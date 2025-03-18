<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use App\Helper\Reply;
use App\Models\LeadAgent;
use App\Models\LeadSource;
use App\Models\LeadCategory;
use App\Models\LeadPipeline;
use Illuminate\Http\Request;
use App\Models\PipelineStage;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Models\UserLeadboardSetting;

class LeadBoardController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.deal';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leads', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->viewLeadPermission = $viewPermission = user()->permission('view_deals');
        $this->viewLeadAgentPermission = user()->permission('view_lead_agents');
        $this->viewEmployeePermission = user()->permission('view_employees');
        $this->viewDealLeadPermission = user()->permission('view_lead');
        $this->products = Product::all();
        abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

        $this->categories = LeadCategory::get();
        $this->sources = LeadSource::get();
        $this->pipelines = LeadPipeline::has('stages')->get();

        $this->dealWatcher = User::allEmployees(null, 'active');
        $this->dealWatcher->where(function ($query) {
            if ($this->viewEmployeePermission == 'added') {
                $query->where('employee_details.added_by', user()->id);
            } elseif ($this->viewEmployeePermission == 'owned') {
                $query->where('employee_details.user_id', user()->id);
            } elseif ($this->viewEmployeePermission == 'both') {
                $query->where(function ($q) {
                    $q->where('employee_details.user_id', user()->id)
                        ->orWhere('employee_details.added_by', user()->id);
                });
            }
        });

        $this->dealLeads = Lead::select('id', 'client_name')->get();

        $this->defaultPipeline = $this->pipelines->filter(function ($value, $key) {
            return $value->default == 1;
        })->first();

        $this->stages = PipelineStage::where('lead_pipeline_id', $this->defaultPipeline->id)->get();
        $this->startDate = now()->subDays(15)->format($this->company->date_format);
        $this->endDate = now()->addDays(15)->format($this->company->date_format);
        $this->leadAgents = LeadAgent::with('user')->whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->groupBy('user_id');

        if ($this->viewLeadAgentPermission != 'all') {
            $this->leadAgents = $this->leadAgents->where('user_id', user()->id);
        }

        $this->leadAgents = $this->leadAgents->get();
        $this->myAgentId = LeadAgent::where('user_id', user()->id)->pluck('id')->toArray();

        $this->viewStageFilter = false;

        if (request()->ajax()) {
            $this->pipelineId = ($request->pipeline) ? $request->pipeline : $this->defaultPipeline->id;

            $startDate = ($request->startDate != 'null') ? companyToDateString($request->startDate) : null;
            $endDate = ($request->endDate != 'null') ? companyToDateString($request->endDate) : null;

            $this->boardEdit = (request()->has('boardEdit') && request('boardEdit') == 'false') ? false : true;
            $this->boardDelete = (request()->has('boardDelete') && request('boardDelete') == 'false') ? false : true;

            $boardColumns = PipelineStage::withCount(['deals as deals_count' => function ($q) use ($startDate, $endDate, $request) {

                $this->dateFilter($q, $startDate, $endDate, $request);
                $q->leftJoin('leads as lead1', 'lead1.id', 'deals.lead_id');

                if ($request->product != 'all' && $request->product != '') {
                    $q->leftJoin('lead_products', 'lead_products.deal_id', '=', 'deals.id')
                      ->where('lead_products.product_id', $request->product);
                }

                if ($request->pipeline != 'all' && $request->pipeline != '') {
                    $q->where('deals.lead_pipeline_id', $request->pipeline);
                }

                if ($request->deal_watcher_id !== null && $request->deal_watcher_id != 'all' && $request->deal_watcher_id != '') {
                    $q = $q->where('deals.deal_watcher', $request->deal_watcher_id);
                }

                if ($request->lead_agent_id !== null && $request->lead_agent_id != 'null' && $request->lead_agent_id != '' && $request->lead_agent_id != 'all') {
                    $q = $q->where('deals.lead_id', $request->lead_agent_id);
                }

                if ($request->searchText != '') {
                    $q->leftJoin('leads', 'leads.id', 'deals.lead_id');
                    $q->where(function ($query) {
                        $query->where('leads.client_name', 'like', '%' . request('searchText') . '%')
                            ->orWhere('leads.client_name', 'like', '%' . request('searchText') . '%')
                            ->orWhere('leads.client_email', 'like', '%' . request('searchText') . '%')
                            ->orWhere('leads.company_name', 'like', '%' . request('searchText') . '%')
                            ->orWhere('leads.mobile', 'like', '%' . request('searchText') . '%');
                    });
                }

                if (($request->agent != 'all' && $request->agent != 'undefined' && $request->agent != '') || $this->viewLeadPermission == 'added') {
                    $q->where(function ($query) use ($request) {
                        if ($request->agent != 'all' && $request->agent != '') {

                            $query->whereHas('leadAgent', function ($q) use ($request) {
                                $q->where('user_id', $request->agent);
                            });
                        }

                        if ($this->viewLeadPermission == 'added') {
                            $query->where('deals.added_by', user()->id);
                        }
                    });
                }

                if ($this->viewLeadPermission == 'owned') {
                    $q->where(function ($query) {
                        if (!empty($this->myAgentId)) {
                            $query->whereIn('agent_id', $this->myAgentId);
                        }
                    });

                    $q->orWhere('deals.deal_watcher', user()->id);
                }

                if ($this->viewLeadPermission == 'both') {
                    $q->where(function ($query) {
                        if (!empty($this->myAgentId)) {
                            $query->whereIn('agent_id', $this->myAgentId);
                        }

                        $query->orWhere('deals.added_by', user()->id)->orWhere('deals.deal_watcher', user()->id);
                    });
                }

                $q->select(DB::raw('count(distinct deals.id)'));

            }])
                ->with(['deals' => function ($q) use ($startDate, $endDate, $request) {
                    $q->with(['leadAgent', 'leadAgent.user', 'currency'])
                        ->leftJoin('leads', 'leads.id', 'deals.lead_id')
                        ->groupBy('deals.id');

                    if (($request->agent != 'all' && $request->agent != '' && $request->agent != 'undefined') || $this->viewLeadPermission == 'added') {
                        $q->where(function ($query) use ($request) {
                            if ($request->agent != 'all' && $request->agent != '') {

                                $query->whereHas('leadAgent', function ($q) use ($request) {
                                    $q->where('user_id', $request->agent);
                                });
                            }

                            if ($this->viewLeadPermission == 'added') {
                                $query->where('deals.added_by', user()->id);
                            }
                        });
                    }

                    if ($this->viewLeadPermission == 'owned') {
                        $q->where(function ($query) {
                            if (!empty($this->myAgentId)) {
                                $query->whereIn('agent_id', $this->myAgentId);
                            }
                            $query->orWhere('deals.deal_watcher', user()->id);
                        });
                    }

                    if ($this->viewLeadPermission == 'both') {
                        $q->where(function ($query) {
                            if (!empty($this->myAgentId)) {
                                $query->whereIn('agent_id', $this->myAgentId);
                            }

                            $query->orWhere('deals.added_by', user()->id)
                                ->orWhere('deals.deal_watcher', user()->id);
                        });
                    }

                    $this->dateFilter($q, $startDate, $endDate, $request);

                    if ($request->min == 'undefined' && $request->max == 'undefined' && (!is_null($request->min) || !is_null($request->max))) {
                        $q->whereBetween('deals.value', [$request->min, $request->max]);
                    }

                    if ($request->product != 'all' && $request->product != '') {
                        $q->leftJoin('lead_products', 'lead_products.deal_id', '=', 'deals.id')
                          ->where('lead_products.product_id', $request->product);
                    }

                    if ($this->pipelineId != 'all' && $this->pipelineId != '' && $this->pipelineId != null) {
                        $q->where('deals.lead_pipeline_id', $this->pipelineId);
                    }

                    if ($request->deal_watcher_id !== null && $request->deal_watcher_id != 'all' && $request->deal_watcher_id != '') {
                        $q = $q->where('deals.deal_watcher', $request->deal_watcher_id);
                    }

                    if ($request->lead_agent_id !== null && $request->lead_agent_id != 'null' && $request->lead_agent_id != '' && $request->lead_agent_id != 'all') {
                        $q = $q->where('deals.lead_id', $request->lead_agent_id);
                    }

                    if ($request->searchText != '') {
                        $q->where(function ($query) {
                            $query->where('leads.client_name', 'like', '%' . request('searchText') . '%')
                                ->orWhere('leads.client_name', 'like', '%' . request('searchText') . '%')
                                ->orWhere('leads.client_email', 'like', '%' . request('searchText') . '%')
                                ->orWhere('leads.company_name', 'like', '%' . request('searchText') . '%')
                                ->orWhere('leads.mobile', 'like', '%' . request('searchText') . '%');
                        });
                    }
                }])->where(function ($query) use ($request) {
                    if ($request->status_id != 'all' && $request->status_id != '' && $request->status_id != 'undefined') {
                        $query->where('id', $request->status_id);
                    }
                });

            if ($request->pipeline != 'all' && $request->pipeline != '') {
                $boardColumns->where('lead_pipeline_id', $request->pipeline);
            }

            $boardColumns = $boardColumns->with('userSetting')->orderBy('priority', 'asc')->get();

            $result = array();

            foreach ($boardColumns as $key => $boardColumn) {
                $result['boardColumns'][] = $boardColumn;

                $leads = Deal::select('deals.*', DB::raw("(select next_follow_up_date from lead_follow_up where deal_id = deals.id and deals.next_follow_up  = 'yes' ORDER BY next_follow_up_date desc limit 1) as next_follow_up_date"))
                    ->leftJoin('leads', 'leads.id', 'deals.lead_id')
                    ->with('leadAgent', 'leadAgent.user')
                    ->where('deals.pipeline_stage_id', $boardColumn->id)
                    ->orderBy('deals.column_priority', 'asc')
                    ->groupBy('deals.id');


                $this->dateFilter($leads, $startDate, $endDate, $request);


                if (!is_null($request->min) || !is_null($request->max)) {
                    $min = $request->min;
                    $leads = $leads->where('value', '>=', $min);
                }

                if (!is_null($request->max)) {
                    $max = $request->max;
                    $leads = $leads->where('value', '<=', $max);
                }

                if ($request->followUp != 'all' && $request->followUp != '' && $request->followUp != 'undefined') {
                    $leads = $leads->leftJoin('lead_follow_up', 'lead_follow_up.deal_id', 'deals.id');

                    if ($request->followUp == 'yes') {
                        $leads->where('deals.next_follow_up', 'yes');
                    }
                    else {
                        $leads->where('deals.next_follow_up', 'no');
                    }
                }

                if ($this->pipelineId != 'all' && $this->pipelineId != '' && $this->pipelineId != null) {
                    $leads->where('deals.lead_pipeline_id', $this->pipelineId);
                }

                if ($request->product != 'all' && $request->product != '') {
                    $leads->leftJoin('lead_products', 'lead_products.deal_id', '=', 'deals.id')
                      ->where('lead_products.product_id', $request->product);
                }


                if ($request->deal_watcher_id !== null && $request->deal_watcher_id != 'all' && $request->deal_watcher_id != '') {
                    $leads->where('deals.deal_watcher', $request->deal_watcher_id);
                }

                if ($request->lead_agent_id !== null && $request->lead_agent_id != 'null' && $request->lead_agent_id != '' && $request->lead_agent_id != 'all') {
                    $leads->where('deals.lead_id', $request->lead_agent_id);
                }

                if ($request->searchText != '') {

                    $leads->where(function ($query) {
                        $query->where('leads.client_name', 'like', '%' . request('searchText') . '%')
                            ->orWhere('leads.client_name', 'like', '%' . request('searchText') . '%')
                            ->orWhere('leads.client_email', 'like', '%' . request('searchText') . '%')
                            ->orWhere('leads.company_name', 'like', '%' . request('searchText') . '%')
                            ->orWhere('leads.mobile', 'like', '%' . request('searchText') . '%');
                    });
                }

                if (($request->agent != 'all' && $request->agent != '' && $request->agent != 'undefined') || $this->viewLeadPermission == 'added') {
                    $leads->where(function ($query) use ($request) {
                        if ($request->agent != 'all' && $request->agent != '') {

                            $query->whereHas('leadAgent', function ($q) use ($request) {
                                $q->where('user_id', $request->agent);
                            });
                        }

                        if ($this->viewLeadPermission == 'added') {
                            $query->where('deals.added_by', user()->id);
                        }
                    });
                }

                if ($this->viewLeadPermission == 'owned') {
                    $leads->where(function ($query) {
                        if (!empty($this->myAgentId)) {
                            $query->whereIn('agent_id', $this->myAgentId);
                        }

                        $query->orWhere('deals.deal_watcher', user()->id);
                    });
                }

                if ($this->viewLeadPermission == 'both') {
                    $leads->where(function ($query) {
                        if (!empty($this->myAgentId)) {
                            $query->whereIn('agent_id', $this->myAgentId);
                        }

                        $query->orWhere('deals.added_by', user()->id)
                            ->orWhere('deals.deal_watcher', user()->id);
                    });
                }

                $leads->skip(0)->take($this->taskBoardColumnLength);
                $leads = $leads->get();
                $dealIds = $leads->pluck('id')->toArray();

                $result['boardColumns'][$key]['total_value'] = 0;

                if (!empty($dealIds)) {
                    $statusTotalValue = Deal::whereIn('id', $dealIds)->sum('value');
                    $result['boardColumns'][$key]['total_value'] = $statusTotalValue;
                }

                $result['boardColumns'][$key]['deals'] = $leads;
            }

            $this->result = $result;
            $this->startDate = $startDate;
            $this->endDate = $endDate;

            $view = view('leads.board.board_data', $this->data)->render();

            return Reply::dataOnly(['view' => $view]);
        }

        $this->leads = Deal::get();

        return view('leads.board.index', $this->data);
    }

    public function dateFilter($query, $startDate, $endDate, $request)
    {
        if ($startDate && $endDate) {
            $query->where(function ($task) use ($startDate, $endDate, $request) {
                if ($request->date_filter_on == 'created_at') {
                    $task->whereBetween(DB::raw('DATE(leads.`created_at`)'), [$startDate, $endDate]);
                }
                elseif ($request->date_filter_on == 'updated_at') {
                    $task->whereBetween(DB::raw('DATE(leads.`updated_at`)'), [$startDate, $endDate]);
                }
                elseif ($request->date_filter_on == 'next_follow_up_date') {
                    $task->whereHas('followup', function ($q) use ($startDate, $endDate) {
                        $q->whereBetween(DB::raw('DATE(lead_follow_up.`next_follow_up_date`)'), [$startDate, $endDate]);
                    });
                }
            });
        }
    }

    public function loadMore(Request $request)
    {
        $startDate = ($request->startDate != 'null') ? companyToDateString($request->startDate) : null;
        $endDate = ($request->endDate != 'null') ? companyToDateString($request->endDate) : null;
        $skip = $request->currentTotalTasks;
        $totalTasks = $request->totalTasks;

        $leads = Deal::select('leads.*', 'deals.*', DB::raw("(select next_follow_up_date from lead_follow_up where deal_id = leads.id and deals.next_follow_up  = 'yes' ORDER BY next_follow_up_date desc limit 1) as next_follow_up_date"))
            ->leftJoin('leads', 'leads.id', 'deals.lead_id')
            ->where('deals.pipeline_stage_id', $request->columnId)
            ->orderBy('leads.column_priority', 'asc')
            ->groupBy('deals.id');

        if ($startDate && $endDate) {
            $leads->where(function ($task) use ($startDate, $endDate) {
                $task->whereBetween(DB::raw('DATE(leads.`created_at`)'), [$startDate, $endDate]);

                $task->orWhereBetween(DB::raw('DATE(leads.`created_at`)'), [$startDate, $endDate]);
            });
        }

        if (!is_null($request->min) || !is_null($request->max)) {
            $leads = $leads->whereBetween('value', [$request->min, $request->max]);
        }

        if ($request->followUp != 'all' && $request->followUp != '' && $request->followUp != 'undefined') {
            $leads = $leads->leftJoin('lead_follow_up', 'lead_follow_up.deal_id', 'deals.id');

            if ($request->followUp == 'yes') {
                $leads->where('deals.next_follow_up', 'yes');
            }
            else {
                $leads->where('deals.next_follow_up', 'no');
            }
        }

        if ($request->searchText != '') {
            $leads->leftJoin('leads', 'leads.id', 'deals.lead_id');
            $leads->where(function ($query) {
                $query->where('leads.client_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('leads.client_email', 'like', '%' . request('searchText') . '%')
                    ->orWhere('leads.company_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('leads.mobile', 'like', '%' . request('searchText') . '%');
            });
        }

        $leads->skip($skip)->take($this->taskBoardColumnLength);
        $leads = $leads->get();
        $this->leads = $leads;

        if ($totalTasks <= ($skip + $this->taskBoardColumnLength)) {
            $loadStatus = 'hide';
        }
        else {
            $loadStatus = 'show';
        }

        $view = view('leads.board.load_more', $this->data)->render();

        return Reply::dataOnly(['view' => $view, 'load_more' => $loadStatus]);
    }

    public function updateIndex(Request $request)
    {
        $taskIds = $request->taskIds;
        $boardColumnId = $request->boardColumnId;
        $priorities = $request->prioritys;

        $board = PipelineStage::findOrFail($boardColumnId);

        if (isset($taskIds) && count($taskIds) > 0) {

            $taskIds = (array_filter($taskIds, function ($value) {
                return $value !== null;
            }));

            foreach ($taskIds as $key => $taskId) {
                if (!is_null($taskId)) {
                    $task = Deal::findOrFail($taskId);
                    $task->update(
                        [
                            'pipeline_stage_id' => $boardColumnId,
                            'column_priority' => $priorities[$key]
                        ]
                    );
                }
            }

        }

        return Reply::dataOnly(['status' => 'success']);
    }

    public function collapseColumn(Request $request)
    {
        $setting = UserLeadboardSetting::firstOrNew([
            'user_id' => user()->id,
            'pipeline_stage_id' => $request->boardColumnId,
        ]);
        $setting->collapsed = (($request->type == 'minimize') ? 1 : 0);
        $setting->save();

        return Reply::dataOnly(['status' => 'success']);
    }

    public function getStageSlug(Request $request)
    {
        $stage = PipelineStage::find($request->statusID);
        return response()->json(['slug' => $stage->slug]);
    }

}
