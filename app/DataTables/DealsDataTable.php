<?php

namespace App\DataTables;

use Carbon\Carbon;
use App\Models\Deal;
use App\Enums\Salutation;
use App\Models\LeadAgent;
use App\Models\CustomField;
use App\Models\PipelineStage;
use App\Models\CustomFieldGroup;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use App\Scopes\ActiveScope;

class DealsDataTable extends BaseDataTable
{

    private $editLeadPermission;
    private $viewLeadFollowUpPermission;
    private $deleteLeadPermission;
    private $addFollowUpPermission;
    private $changeLeadStatusPermission;
    private $viewLeadPermission;
    private $myAgentId;

    /**
     * @var \App\Models\LeadStatus[]|\Illuminate\Database\Eloquent\Collection
     */
    private $status;

    public function __construct()
    {
        parent::__construct();
        $this->editLeadPermission = user()->permission('edit_deals');
        $this->deleteLeadPermission = user()->permission('delete_deals');
        $this->viewLeadPermission = user()->permission('view_deals');
        $this->addFollowUpPermission = user()->permission('add_lead_follow_up');
        $this->changeLeadStatusPermission = user()->permission('change_deal_stages');
        $this->viewLeadFollowUpPermission = user()->permission('view_lead_follow_up');
        $this->myAgentId = LeadAgent::where('user_id', user()->id)->pluck('id')->toArray();
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $currentDate = now(company()->timezone)->translatedFormat('Y-m-d');

        $stagesData = PipelineStage::all();

        $stages = $stagesData->filter(function ($value, $key) {
            return $value->lead_pipeline_id == $this->request()->pipeline;
        });

        $datatables = datatables()->eloquent($query);
        $datatables->addIndexColumn();
        $datatables->addColumn('check', fn($row) => $this->checkBox($row));
        $datatables->addColumn('export_deal_watcher', fn($row) => $row->dealWatcher->name ?? '--');
        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a href="' . route('deals.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

            if (
                $this->editLeadPermission == 'all'
                || ($this->editLeadPermission == 'added' && user()->id == $row->added_by)
                || ($this->editLeadPermission == 'owned' && ((!is_null($row->agent_id) && !is_null($row->leadAgent) && user()->id == $row->leadAgent->user->id) || (!is_null($row->deal_watcher) && user()->id == $row->deal_watcher)))
                || ($this->editLeadPermission == 'both' && (((!is_null($row->agent_id) && !is_null($row->leadAgent) && user()->id == $row->leadAgent->user->id) || (!is_null($row->deal_watcher) && user()->id == $row->deal_watcher)) || user()->id == $row->added_by))
            ) {
                $action .= '<a class="dropdown-item openRightModal" href="' . route('deals.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
            }

            if (
                $this->deleteLeadPermission == 'all'
                || ($this->deleteLeadPermission == 'added' && user()->id == $row->added_by)
                || ($this->deleteLeadPermission == 'owned' && ((!is_null($row->agent_id) && !is_null($row->leadAgent) && user()->id == $row->leadAgent->user->id) || (!is_null($row->deal_watcher) && user()->id == $row->deal_watcher)))
                || ($this->deleteLeadPermission == 'both' && (((!is_null($row->agent_id) && !is_null($row->leadAgent) && user()->id == $row->leadAgent->user->id) || (!is_null($row->deal_watcher) && user()->id == $row->deal_watcher)) || user()->id == $row->added_by))
            ) {
                $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-id="' . $row->id . '">
                        <i class="fa fa-trash mr-2"></i>
                        ' . trans('app.delete') . '
                    </a>';
            }

            if (($this->addFollowUpPermission == 'all' || ($this->addFollowUpPermission == 'added' && user()->id == $row->added_by)) && $row->next_follow_up == 'yes' && !in_array($row->leadStage?->slug, ['win', 'lost'])) {
                $action .= '<a onclick="followUp(' . $row->id . ')" class="dropdown-item" href="javascript:;">
                                <i class="fa fa-thumbs-up mr-2"></i>
                                ' . trans('modules.lead.addFollowUp') . '
                            </a>';
            }

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });
        $datatables->addColumn('employee_name', fn($row) => $row->leadAgent->user->name ?? '--');
        $datatables->addColumn('mobile', fn($row) => !empty($row->mobile) ? '<a href="tel:' . $row->mobile . '" class="text-darkest-grey"><u>' . $row->mobile . '</u></a>' : '--');
        $datatables->addColumn('lead_email', fn($row) => !empty($row->client_email) ? '<a href="mailto:' . $row->client_email . '" class="text-darkest-grey"><u>' . str($row->client_email)->limit(25) . '</u></a>'
            .'<p>'.$row->mobile.'</p>' : '--');
        $datatables->addColumn('export_mobile', fn($row) => $row->contact->mobile ?? '--');
        $datatables->addColumn('export_email', fn($row) => $row->client_email);
        $datatables->addColumn('lead_value', fn($row) => currency_format($row->value, $row->currency_id));
        $datatables->addColumn('lead', 'client_name');
        $datatables->addColumn('category_name', fn($row) => $row->contact->category->category_name ?? null);
        $datatables->addColumn('leadStage', fn($row) => $row->leadStage->name ?? '--');

        $datatables->addColumn('stage', function ($row) use ($stages, $stagesData) {
            $action = '--';

            if (count($stages) == 0) {

                $stages = $stagesData->filter(function ($value) use ($row) {
                    return $value->lead_pipeline_id == $row->lead_pipeline_id;
                });
            }

            $stages = $stages->sortBy('priority');

            if ($this->changeLeadStatusPermission == 'all') {

                $action = '<select class="form-control select-picker statusChange" data-size="4" name="statusChange" onchange="changeStage(' . $row->id . ', this)">';

                foreach ($stages as $item) {
                    $action .= '<option ';

                    if ($item->id == $row->pipeline_stage_id) {
                        $action .= 'selected';
                    }

                    $action .= '  data-content="<i class=\'fa fa-circle mr-2\' style=\'color: ' . $item->label_color . '\'></i> ' . $item->name . '" data-id="' . $item->id . '" data-slug="' . $item->slug . '" value="' . $item->id . '">' . $item->name . '</option>';
                }

                $action .= '</select>';
            }
            else {
                foreach ($stages as $st) {
                    if ($row->pipeline_stage_id == $st->id) {
                        $action = "<div class='media align-items-center mw-120'><i class='fa fa-circle mr-1' style='color: ". $st->label_color ." '></i> " . str($st->name)->limit(10) . '</div>';
                    }
                }
            }

            return $action;
        });

        $datatables->addColumn('client_name', function ($row) {
            $label = '';

            if ($row->client_id != null && $row->client_id != '') {
                $label = '<label class="badge badge-secondary">' . __('app.client') . '</label>';
            }

            $client_name = $row->client_name;

            if ($row->salutation instanceof Salutation || is_string($row->salutation)) {
                $salutationLabel = ($row->salutation instanceof Salutation) ? $row->salutation->label() : Salutation::from($row->salutation)->label();
                $client_name = $salutationLabel . ' ' . $client_name;
            }

            return '
                        <div class="media-body">
                    <h5 class="mb-0 f-13 text-truncate"><a href="' . route('lead-contact.show', [$row->contact_id]) . '">' . str($client_name)->limit(18) . '</a></h5>
                    <p class="mb-0">' . $label . '</p>
                    <p class="mb-0 f-12 text-dark-grey text-truncate">
                    ' . str($row->company_name)->limit(18) . '
                </p>
                    </div>
                  ';
        });

        $datatables->editColumn('name', function ($row) {
            return '
                        <div class="media-body">
                            <h5 class="mb-0 f-13 "><a href="' . route('deals.show', [$row->id]) . '">' . $row->name . '</a></h5>
                        </div>
                  ';
        });
        $datatables->editColumn('next_follow_up_date', function ($row) use ($currentDate) {
            $date = '';
            if ($row->next_follow_up_date != null && $row->next_follow_up_date != '') {
                $date = $row->next_follow_up_date->translatedFormat($this->company->date_format) . '<br> ' . $row->next_follow_up_date->translatedFormat($this->company->time_format);
            }

            if ($row->next_follow_up_date < $currentDate && $row->next_follow_up_status == 'incomplete' && $date != '--') {
                return $date . '<br><label class="badge badge-danger">' . __('app.pending') . '</label>';
            }

            return $date;
        });
        $datatables->editColumn('close_date', fn($row) => $row->close_date ? $row->close_date->translatedFormat($this->company->date_format) : '--');
        $datatables->editColumn('lead_pipeline_id', fn($row) => $row->lead_pipeline_id ? $row->pipeline->name : '--');
        $datatables->editColumn('agent_name', fn($row) => $row->agent_id ? view('components.employee', ['user' => $row->leadAgent->user]) : '--');
        $datatables->addColumn('deal_watcher_user', fn($row) => $row->dealWatcher ? view('components.employee', ['user' => $row->dealWatcher]) : '--');
        $datatables->smart(false);
        $datatables->setRowId(fn($row) => 'row-' . $row->id);
        $datatables->removeColumn('status_id');
        $datatables->removeColumn('client_id');
        $datatables->removeColumn('source');
        $datatables->removeColumn('next_follow_up');
        $datatables->removeColumn('statusName');
        $datatables->removeColumn('statusName');

        $customFieldColumns = CustomField::customFieldData($datatables, Deal::CUSTOM_FIELD_MODEL);

        $datatables->rawColumns(array_merge(['status', 'action', 'name', 'client_name', 'next_follow_up_date', 'agent_name', 'check', 'mobile', 'stage', 'lead_email'], $customFieldColumns));

        return $datatables;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Deal $model)
    {
        $lead = $model->with(['leadAgent','products',
            'dealWatcher' => function ($query) {
                    $query->withoutGlobalScope(ActiveScope::class);
                }
            , 'leadAgent.user', 'category', 'contact', 'pipeline', 'leadStage', 'leadAgent.user.employeeDetail.designation:id,name',
            'leadAgent.user.employeeDetail.department:id,team_name'])
            ->select(
                'deals.id',
                'deals.name',
                'deals.deal_watcher',
                'deals.lead_id',
                'deals.lead_pipeline_id',
                'deals.agent_id',
                'deals.added_by',
                'leads.client_id',
                'deals.next_follow_up',
                'deals.value',
                'pipeline_stages.name as stageName',
                'pipeline_stage_id',
                'deals.created_at',
                'deals.close_date',
                'deals.updated_at',
                'users.name as agent_name',
                'users.image',
                'leads.company_name',
                'leads.mobile',
                'leads.salutation as salutation',
                'leads.id as contact_id',
                'leads.client_name as client_name',
                'leads.client_email as client_email',
                DB::raw("(select next_follow_up_date from lead_follow_up where deal_id = deals.id and deals.next_follow_up  = 'yes' and lead_follow_up.status  = 'pending' ORDER BY next_follow_up_date asc limit 1) as next_follow_up_date"),
                DB::raw("(select lead_follow_status.status from lead_follow_up as lead_follow_status where deal_id = deals.id and deals.next_follow_up  = 'yes'  ORDER BY next_follow_up_date asc limit 1) as next_follow_up_status")
            )
            ->leftJoin('pipeline_stages', 'pipeline_stages.id', 'deals.pipeline_stage_id')
            ->leftJoin('lead_agents', 'lead_agents.id', 'deals.agent_id')
            ->leftJoin('users', 'users.id', 'lead_agents.user_id')
            ->leftJoin('leads', 'leads.id', 'deals.lead_id')
            ->leftJoin('lead_products', 'lead_products.deal_id', 'deals.id')
            ->leftJoin('products', 'products.id', 'lead_products.product_id');


        if ($this->request()->followUp != 'all' && $this->request()->followUp != '') {
            $lead = $lead->leftJoin('lead_follow_up', 'lead_follow_up.deal_id', 'deals.id');

            if ($this->request()->followUp == 'yes') {
                $lead = $lead->where('deals.next_follow_up', 'yes');
            }
            else {
                $lead = $lead->where('deals.next_follow_up', 'no');
            }

        }

        if (!is_null($this->request()->min) || !is_null($this->request()->max)) {
            $min = $this->request()->min;
            $lead = $lead->where('value', '>=', $min);
        }

        if (!is_null($this->request()->max)) {
            $max = $this->request()->max;
            $lead = $lead->where('value', '<=', $max);
        }

        if ($this->request()->type != 'all' && $this->request()->type != '') {

            if ($this->request()->type == 'lead') {
                $lead = $lead->whereNull('leads.client_id');
            }
            else {
                $lead = $lead->whereNotNull('leads.client_id');
            }
        }

        if ($this->request()->startDate !== null && $this->request()->startDate != 'null' && $this->request()->startDate != '' && request()->date_filter_on == 'created_at') {
            $startDate = Carbon::createFromFormat($this->company->date_format, $this->request()->startDate)->toDateString();

            $lead = $lead->having(DB::raw('DATE(deals.`created_at`)'), '>=', $startDate);
        }

        if ($this->request()->pipeline !== null && $this->request()->pipeline != 'null' && $this->request()->pipeline != '' && request()->pipeline != 'all') {
            $lead = $lead->where('deals.lead_pipeline_id', $this->request()->pipeline);
        }

        if ($this->request()->category !== null && $this->request()->category != 'null' && $this->request()->category != '' && $this->request()->category != 'all') {
            $lead = $lead->where('deals.category_id', $this->request()->category);
        }

        if ($this->request()->product !== null && $this->request()->product != 'null' && $this->request()->pipeline != '' && request()->product != 'all') {
            $lead = $lead->where('products.id', $this->request()->product);
        }

        if ($this->request()->startDate !== null && $this->request()->startDate != 'null' && $this->request()->startDate != '' && request()->date_filter_on == 'next_follow_up_date') {
            $startDate = Carbon::createFromFormat($this->company->date_format, $this->request()->startDate)->toDateString();

            $lead = $lead->having(DB::raw('DATE(`next_follow_up_date`)'), '>=', $startDate);
        }

        if ($this->request()->endDate !== null && $this->request()->endDate != 'null' && $this->request()->endDate != '' && request()->date_filter_on == 'created_at') {
            $endDate = Carbon::createFromFormat($this->company->date_format, $this->request()->endDate)->toDateString();
            $lead = $lead->having(DB::raw('DATE(deals.`created_at`)'), '<=', $endDate);
        }

        if ($this->request()->endDate !== null && $this->request()->endDate != 'null' && $this->request()->endDate != '' && request()->date_filter_on == 'next_follow_up_date') {
            $endDate = Carbon::createFromFormat($this->company->date_format, $this->request()->endDate)->toDateString();
            $lead = $lead->having(DB::raw('DATE(`next_follow_up_date`)'), '<=', $endDate);
        }

        if ($this->request()->startDate !== null && $this->request()->startDate != 'null' && $this->request()->startDate != '' && request()->date_filter_on == 'updated_at') {
            $startDate = Carbon::createFromFormat($this->company->date_format, $this->request()->startDate)->toDateString();

            $lead = $lead->having(DB::raw('DATE(deals.`updated_at`)'), '>=', $startDate);
        }

        if ($this->request()->endDate !== null && $this->request()->endDate != 'null' && $this->request()->endDate != '' && request()->date_filter_on == 'updated_at') {
            $endDate = Carbon::createFromFormat($this->company->date_format, $this->request()->endDate)->toDateString();
            $lead = $lead->having(DB::raw('DATE(deals.`updated_at`)'), '<=', $endDate);
        }

        if (($this->request()->agent != 'all' && $this->request()->agent != '') || $this->viewLeadPermission == 'added') {
            $lead = $lead->where(function ($query) {
                if ($this->request()->agent != 'all' && $this->request()->agent != '') {
                    $AgentIds = LeadAgent::where('user_id', $this->request()->agent)->get()->pluck('id');
                    $query->whereIn('agent_id', $AgentIds);
                }

                if ($this->viewLeadPermission == 'added') {
                    $query->where('deals.added_by', user()->id);
                }
            });
        }

        if ($this->viewLeadPermission == 'owned') {
            $lead = $lead->where(function ($query) {
                if (!empty($this->myAgentId)) {
                    $query->whereIn('agent_id', $this->myAgentId);
                }

                $query->orWhere('deals.deal_watcher', user()->id);
            });
        }

        if ($this->viewLeadPermission == 'both') {
            $lead = $lead->where(function ($query) {
                if (!empty($this->myAgentId)) {
                    $query->whereIn('agent_id', $this->myAgentId);
                }

                $query->orWhere('deals.added_by', user()->id)
                    ->orWhere('deals.deal_watcher', user()->id);
            });
        }

        if ($this->request()->deal_watcher_id !== null && $this->request()->deal_watcher_id != 'all' && $this->request()->deal_watcher_id != '') {
            $lead = $lead->where('deals.deal_watcher', $this->request()->deal_watcher_id);
        }

        if ($this->request()->stage_id != 'all' && $this->request()->stage_id != '') {
            $lead = $lead->where('deals.pipeline_stage_id', $this->request()->stage_id);
        }

        if ($this->request()->leadId !== null && $this->request()->leadId != 'null' && $this->request()->leadId != '' && $this->request()->leadId != 'all') {
            $lead = $lead->where('deals.lead_id', $this->request()->leadId);
        }

        if ($this->request()->lead_agent_id !== null && $this->request()->lead_agent_id != 'null' && $this->request()->lead_agent_id != '' && $this->request()->lead_agent_id != 'all') {
            $lead = $lead->where('deals.lead_id', $this->request()->lead_agent_id);
        }

        if ($this->request()->source_id != 'all' && $this->request()->source_id != '') {
            $lead = $lead->where('leads.source_id', $this->request()->source_id);
        }

        if ($this->request()->searchText != '') {
            $lead = $lead->where(function ($query) {
                $query->where('leads.client_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('leads.client_email', 'like', '%' . request('searchText') . '%')
                    ->orWhere('leads.company_name', 'like', '%' . request('searchText') . '%')
                    ->orwhere('leads.mobile', 'like', '%' . request('searchText') . '%')
                    ->orwhere('deals.name', 'like', '%' . request('searchText') . '%');
            });
        }

        return $lead->groupBy('deals.id');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('leads-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["leads-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $(".select-picker").selectpicker();
                }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        }

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {

        $data = [

            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => showId()],
            __('modules.deal.dealName') => ['data' => 'name', 'name' => 'name', 'title' => __('modules.deal.dealName')],
            __('modules.leadContact.leadName') => ['data' => 'client_name', 'name' => 'leads.client_name', 'title' => __('modules.leadContact.leadName')],
            __('modules.lead.email') => ['data' => 'lead_email', 'name' => 'email', 'title' => __('app.contactDetails'), 'exportable' => false],
            __('app.lead') . ' ' . __('modules.lead.email') => ['data' => 'export_email', 'name' => 'email', 'title' => __('app.lead') . ' ' . __('modules.lead.email'), 'exportable' => true, 'visible' => false],
            __('modules.deal.pipeline') => ['data' => 'lead_pipeline_id', 'name' => 'lead_pipeline_id', 'exportable' => true, 'visible' => false, 'title' => __('modules.deal.pipeline')],
            //            __('modules.lead.mobile') => ['data' => 'mobile', 'name' => 'mobile', 'title' => __('modules.lead.mobile'), 'exportable' => false],
            __('app.lead') . ' ' . __('modules.lead.mobile') => ['data' => 'export_mobile', 'name' => 'mobile', 'title' => __('app.lead') . ' ' . __('modules.lead.mobile'), 'exportable' => true, 'visible' => false],
            __('modules.deal.dealValue') => ['data' => 'lead_value', 'name' => 'value', 'title' => __('app.value'), 'exportable' => true],
            __('modules.deal.closeDate') => ['data' => 'close_date', 'name' => 'close_date', 'title' => __('modules.deal.closeDate')],
            __('modules.lead.nextFollowUp') => ['data' => 'next_follow_up_date', 'name' => 'next_follow_up_date', 'searchable' => false, 'exportable' => ($this->viewLeadFollowUpPermission != 'none'), 'title' => __('modules.lead.nextFollowUp'), 'visible' => ($this->viewLeadFollowUpPermission != 'none')],
            __('modules.deal.dealAgent') => ['data' => 'agent_name', 'name' => 'users.name', 'exportable' => false, 'title' => __('modules.deal.dealAgent')],
            __('app.leadAgent') => ['data' => 'employee_name', 'name' => 'users.name', 'visible' => false, 'title' => __('app.leadAgent')],
            __('app.addedBy') => ['data' => 'export_deal_watcher', 'name' => 'users.name', 'exportable' => true, 'visible' => false, 'title' => __('app.dealWatcher')],
            __('app.dealWatcher') => ['data' => 'deal_watcher_user', 'name' => 'users.name', 'exportable' => false, 'title' => __('app.dealWatcher')],
            __('modules.leadContact.stage') => ['data' => 'stage', 'name' => 'deals.pipeline_stage_id', 'exportable' => false, 'visible' => true, 'title' => __('modules.leadContact.stage')],
            __('modules.leadContact.leadStage') => ['data' => 'leadStage', 'name' => 'leadStage', 'visible' => false, 'orderable' => false, 'searchable' => false, 'title' => __('modules.leadContact.leadStage')]
        ];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];


        return array_merge($data, CustomFieldGroup::customFieldsDataMerge(new Deal()), $action);

    }

}
