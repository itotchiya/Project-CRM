<?php

namespace App\Http\Controllers;

use App\DataTables\DealNotesDataTable;
use App\DataTables\LeadFollowupDataTable;
use App\DataTables\LeadGDPRDataTable;
use App\DataTables\DealsDataTable;
use App\DataTables\ProposalDataTable;
use App\Enums\Salutation;
use App\Events\AutoFollowUpReminderEvent;
use ReflectionClass;
use Illuminate\Support\Facades\DB;
use App\Helper\Reply;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Http\Requests\CommonRequest;
use App\Http\Requests\FollowUp\StoreRequest as FollowUpStoreRequest;
use App\Http\Requests\Deal\StoreRequest;
use App\Http\Requests\Deal\UpdateRequest;
use App\Http\Requests\Deal\StageChangeRequest;
use App\Imports\DealImport;
use App\Jobs\ImportDealJob;
use App\Models\GdprSetting;
use App\Models\Deal;
use App\Models\LeadAgent;
use App\Models\LeadCategory;
use App\Models\LeadCustomForm;
use App\Models\DealFollowUp;
use App\Models\DealHistory;
use App\Models\DealNote;
use App\Models\Lead;
use App\Models\LeadPipeline;
use App\Models\LeadProduct;
use App\Models\LeadSource;
use App\Models\PipelineStage;
use App\Models\LeadStatus;
use App\Models\Product;
use App\Models\Proposal;
use App\Models\PurposeConsent;
use App\Models\PurposeConsentLead;
use App\Models\User;
use App\Traits\ImportExcel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DealController extends AccountBaseController
{

    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.deal';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leads', $this->user->modules));

            $this->viewLeadPermission = user()->permission('view_deals');
            $this->viewEmployeePermission = user()->permission('view_employees');
            $this->viewDealLeadPermission = user()->permission('view_lead');
            $this->viewLeadAgentPermission = user()->permission('view_lead_agents');
            $this->viewLeadCategoryPermission = user()->permission('view_lead_category');

            return $next($request);
        });
    }

    public function index(DealsDataTable $dataTable)
    {
        $this->destroySession();
        abort_403(!in_array($this->viewLeadPermission, ['all', 'added', 'both', 'owned']));

        if (!request()->ajax()) {
            $this->loadDataForView();
            $this->products = Product::all();

        }
        return $dataTable->render('leads.index', $this->data);
    }

    protected function loadDataForView()
    {
        $this->loadPipelineData();
        $this->loadDealData();
        $this->loadLeadAgents();
        $this->loadDealWatcher();
        $this->loadDealLeads();
    }

    protected function loadPipelineData()
    {
        $this->pipelines = LeadPipeline::all();
        $defaultPipeline = LeadPipeline::where('default', 1)->first();
        $this->stages = PipelineStage::where('lead_pipeline_id', optional($defaultPipeline)->id)->get();
        $this->categories = LeadCategory::all();
        $this->sources = LeadSource::all();
    }

    protected function loadDealData()
    {
        $this->totalDeals = Deal::all();
        $this->totalClientConverted = $this->totalDeals->whereNotNull('client_id')->count();
        $this->totalLeads = $this->totalDeals->count();
        $this->pendingLeadFollowUps = DealFollowUp::whereDate('next_follow_up_date', '<=', now()->format('Y-m-d'))
            ->join('deals', 'deals.id', 'lead_follow_up.deal_id')
            ->where('deals.next_follow_up', 'yes')
            ->groupBy('lead_follow_up.deal_id')
            ->count();
        $this->dealAgents = LeadAgent::with('user')
            ->whereHas('user', function ($q) {
                $q->where('status', 'active');
            })->where('status', 'enabled')->groupBy('user_id')->get();
    }

    protected function loadLeadAgents()
    {
        $this->leadAgents = LeadAgent::with('user')
            ->whereHas('user', function ($q) {
                $q->where('status', 'active');
            })->groupBy('user_id')->get();
    }

    protected function loadDealWatcher()
    {
        $this->dealWatcher = User::allEmployees(null);

        if (in_array($this->viewEmployeePermission, ['added', 'owned', 'both'])) {
            $this->dealWatcher = $this->dealWatcher->where(function ($query) {
                $query->when($this->viewEmployeePermission == 'added', function ($q) {
                    $q->where('employee_details.added_by', user()->id);
                })->when($this->viewEmployeePermission == 'owned', function ($q) {
                    $q->where('employee_details.user_id', user()->id);
                })->when($this->viewEmployeePermission == 'both', function ($q) {
                    $q->where('employee_details.user_id', user()->id)
                        ->orWhere('employee_details.added_by', user()->id);
                });
            });
        }
    }

    protected function loadDealLeads()
    {
        $this->dealLeads = Lead::select(['id', 'client_name'])->get();
    }

    public function show($id)
    {
        $this->deal = Deal::with('leadAgent.user:id,name,image')->findOrFail($id)->withCustomFields();

        $this->leadAgentId = ($this->deal->leadAgent != null) ? $this->deal->leadAgent->user->id : 0;

        $this->leadAgentName = ($this->deal->leadAgent != null) ? $this->deal->leadAgent->user->name : '';

        $this->viewPermission = user()->permission('view_deals');

        abort_403(!(
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->deal->added_by == user()->id)
            || ($this->viewPermission == 'owned' && (($this->leadAgentId == user()->id) || (!is_null($this->deal->deal_watcher) && user()->id == $this->deal->deal_watcher)))
            || ($this->viewPermission == 'both' && ($this->deal->added_by == user()->id || $this->leadAgentId == user()->id || (!is_null($this->deal->deal_watcher) && user()->id == $this->deal->deal_watcher)))
        ));

        $this->pageTitle = $this->deal->name;

        $this->productNames = $this->deal->products->pluck('name')->toArray();

        $this->leadFormFields = LeadCustomForm::with('customField')->where('status', 'active')->where('custom_fields_id', '!=', 'null')->get();

        $this->leadId = $id;

        $getCustomFieldGroupsWithFields = $this->deal->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->deleteLeadPermission = user()->permission('delete_deals');
        $this->view = 'leads.ajax.profile';

        $tab = request('tab');

        switch ($tab) {
        case 'files':
            $this->tab = 'leads.ajax.files';
            break;
        case 'follow-up':
            $this->dealFollowUps = DealFollowUp::where('deal_id', $id)->get();

            if (user()->permission('view_lead_follow_up') == 'added') {
                $this->dealFollowUps = $this->dealFollowUps->where('added_by', user()->id);
            }

            $this->tab = 'leads.ajax.follow-up';
            break;
        case 'proposals':
            abort_403(!in_array(user()->permission('view_lead_proposals'), ['all', 'added']));

            $this->proposals = Proposal::where('deal_id', $id)->get();

            if (user()->permission('view_lead_proposals') == 'added') {
                $this->proposals = $this->proposals->where('added_by', user()->id);
            }

            $this->tab = 'leads.ajax.proposal';
            break;
        case 'notes':
            $this->notes = DealNote::where('deal_id', $id)->orderBy('created_at', 'desc')->get();
            $viewNotesPermission = user()->permission('view_deal_note');
            abort_403(!($viewNotesPermission == 'all' || $viewNotesPermission == 'added' || $viewNotesPermission == 'both' || $viewNotesPermission == 'owned'));

            if (user()->permission('view_deal_note') == 'added') {
                $this->notes->where('added_by', user()->id);
            }
            elseif (user()->permission('view_deal_note') == 'owned') {
                $this->notes->where('added_by', '!=', user()->id);
            }

            $this->tab = 'leads.ajax.notes';
            break;
        case 'gdpr':

            $this->consents = PurposeConsent::with(['lead' => function ($query) use ($id) {
                $query->where('lead_id', $id)
                    ->orderByDesc('created_at');
            }])->get();

            $this->gdpr = GdprSetting::first();

            return $this->gdpr();
        case 'history':
            $this->histories = DealHistory::where('deal_id', $id)->orderBy('created_at', 'desc')->get();
            $this->tab = 'leads.ajax.history';
            break;
        default:
            $this->tab = 'leads.ajax.files';
            break;
        }

        if (request()->ajax()) {
            $view = request('json') ? $this->tab : 'leads.ajax.show';

            return $this->returnAjax($view);
        }

        $this->view = 'leads.ajax.show';

        return view('leads.create', $this->data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_deals');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->employees = User::allEmployees(null, true);

        $defaultStatus = LeadStatus::where('default', '1')->first();
        $this->columnId = ((request('column_id') != '') ? request('column_id') : $defaultStatus->id);
        $this->leadAgents = LeadAgent::with('user')->whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->get();

        $this->stage = (request()->has('column_id') && !is_null(request()->column_id)) ? PipelineStage::find(request()->column_id) : null;
        $this->contactID = (request()->has('contact_id') && !is_null(request()->contact_id)) ? request()->contact_id : null;

        $this->leadAgentArray = $this->leadAgents->pluck('user_id')->toArray();

        if ((in_array(user()->id, $this->leadAgentArray))) {
            $this->myAgentId = $this->leadAgents->filter(function ($value, $key) {
                return $value->user_id == user()->id;
            })->first()->id;
        }

        $deal = new Deal();
        $getCustomFieldGroupsWithFields = $deal->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->leadContacts = Lead::allLeads();
        $this->products = Product::all();
        $this->sources = LeadSource::all();
        $this->stages = PipelineStage::all();
        $this->categories = LeadCategory::query();

        if ($this->viewLeadCategoryPermission == 'added') {
            $this->categories->where('added_by', user()->id);
        } elseif ($this->viewLeadCategoryPermission == 'both') {
            $this->categories->where(function ($query) {
                $query->where('added_by', user()->id);
            });
        }

        $this->categories = $this->categories->get();

        $this->leadPipelines = LeadPipeline::orderBy('default', 'DESC')->get();
        $this->leadStages = PipelineStage::all();
        $this->countries = countries();

        $this->pageTitle = __('modules.deal.createTitle');
        $this->salutations = Salutation::cases();

        $this->view = 'leads.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('leads.create', $this->data);

    }

    /**
     * @param StoreRequest $request
     * @return array|void
     * @throws RelatedResourceNotFoundException
     */
    public function store(StoreRequest $request)
    {
        $this->addPermission = user()->permission('add_deals');
        abort_403(!in_array($this->addPermission, ['all', 'added']));
        $agentId = null;
        if (!is_null($request->agent_id)) {
            $leadAgent = LeadAgent::where('user_id', $request->agent_id)->where('lead_category_id', $request->category_id)->first();
            $agentId = isset($leadAgent) ? $leadAgent->id : null;
        }
        $deal = new Deal();
        $deal->name = $request->name;
        $deal->lead_id = $request->lead_contact;
        $deal->next_follow_up = 'yes';
        $deal->category_id = $request->category_id;
        $deal->deal_watcher = $request->deal_watcher;
        $deal->lead_pipeline_id = $request->pipeline;
        $deal->pipeline_stage_id = $request->stage_id;
        $deal->agent_id = $agentId;
        $deal->close_date = companyToYmd($request->close_date);
        $deal->value = ($request->value) ?: 0;
        $deal->currency_id = $this->company->currency_id;
        $deal->save();

        if (!is_null($request->product_id)) {

            $products = $request->product_id;

            foreach ($products as $product) {
                $leadProduct = new LeadProduct();
                $leadProduct->deal_id = $deal->id;
                $leadProduct->product_id = $product;
                $leadProduct->save();
            }
        }

        // To add custom fields data
        if ($request->custom_fields_data) {
            $deal->updateCustomFieldData($request->custom_fields_data);
        }

        // Log search
        $this->logSearchEntry($deal->id, $deal->name, 'deals.show', 'deal');

        $redirectUrl = urldecode($request->redirect_url);

        if ($request->add_more == 'true') {
            $html = $this->create();

            return Reply::successWithData(__('messages.recordSaved'), ['html' => $html, 'add_more' => true]);
        }

        if ($redirectUrl == '') {
            $redirectUrl = route('deals.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->deal = Deal::with('currency', 'leadAgent', 'leadAgent.user', 'products', 'leadStage')->findOrFail($id)->withCustomFields();

        $this->productIds = $this->deal->products->pluck('id')->toArray();

        $this->editPermission = user()->permission('edit_deals');

        $this->employees = User::allEmployees(null, false);

        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->deal->added_by == user()->id)
            || ($this->editPermission == 'owned' && ((!is_null($this->deal->agent_id) && !is_null($this->deal->leadAgent) && user()->id == $this->deal->leadAgent->user->id) || (!is_null($this->deal->deal_watcher) && user()->id == $this->deal->deal_watcher)))
            || ($this->editPermission == 'both' && (((!is_null($this->deal->agent_id) && !is_null($this->deal->leadAgent) && user()->id == $this->deal->leadAgent->user->id) || (!is_null($this->deal->deal_watcher) && user()->id == $this->deal->deal_watcher)) || user()->id == $this->deal->added_by))
        ));

        $this->tab = (!is_null(request('tab'))) ? request('tab') : null;
         // Filter out active employees
        $activeEmployees = $this->employees->filter(function ($employee) {
            return $employee->status !== 'deactive';
        });

        // Get the selected employee who is a deal watcher
        $selectedEmployee = $this->employees->firstWhere('id', $this->deal->deal_watcher);

        // Include the selected deactivated employee in the list if they are deactivated
        if ($selectedEmployee && $selectedEmployee->status === 'deactive') {
            $this->employees = $activeEmployees->push($selectedEmployee);
        } else {
            $this->employees = $activeEmployees;
        }

        $this->leadAgents = LeadAgent::with('user')->whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->get();

        $getCustomFieldGroupsWithFields = $this->deal->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->categories = LeadCategory::query();

        if ($this->viewLeadCategoryPermission == 'added') {
            $this->categories->where('added_by', user()->id);
        } elseif ($this->viewLeadCategoryPermission == 'both') {
            $this->categories->where(function ($query) {
                $query->where('added_by', user()->id);
            });
        }

        $this->categories = $this->categories->get();
        $this->leadContacts = Lead::all();
        $this->products = Product::all();
        $this->leadPipelines = LeadPipeline::all();

        $this->stages = PipelineStage::where('lead_pipeline_id', $this->deal->lead_pipeline_id)->get();

        $this->pageTitle = __('modules.deal.updateDeal');
        $this->salutations = Salutation::cases();


        $this->view = 'leads.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('leads.create', $this->data);

    }

    /**
     * @param UpdateRequest $request
     * @param int $id
     * @return array|void
     * @throws RelatedResourceNotFoundException
     */
    public function update(UpdateRequest $request, $id)
    {
        $deal = Deal::with('leadAgent', 'leadAgent.user')->findOrFail($id);
        $this->editPermission = user()->permission('edit_deals');

        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $deal->added_by == user()->id)
            || ($this->editPermission == 'owned' && ((!is_null($deal->agent_id) && !is_null($deal->leadAgent) && user()->id == $deal->leadAgent->user->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)))
            || ($this->editPermission == 'both' && (((!is_null($deal->agent_id) && !is_null($deal->leadAgent) && user()->id == $deal->leadAgent->user->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)) || user()->id == $deal->added_by))
        ));

        if (!is_null($request->agent_id)) {
            $leadAgent = LeadAgent::where('user_id', $request->agent_id)->where('lead_category_id', $request->category_id)->first();
            $deal->agent_id = $leadAgent->id;
        }else{
            $deal->agent_id = $request->agent_id;
        }

        $deal->name = $request->name;
        $deal->deal_watcher = $request->deal_watcher;
        $deal->next_follow_up = $request->next_follow_up;
        $deal->lead_pipeline_id = $request->pipeline;
        $deal->pipeline_stage_id = $request->stage_id;
        $deal->close_date = companyToYmd($request->close_date);
        $deal->value = ($request->value) ?: 0;
        $deal->currency_id = $this->company->currency_id;
        $deal->category_id = $request->category_id;
        $deal->save();

        $deal->products()->sync($request->product_id);

        // To add custom fields data
        if ($request->custom_fields_data) {
            $deal->updateCustomFieldData($request->custom_fields_data);
        }

        $redirectTo = (!is_null(request('tab')) && request('tab') == 'overview') ? route('deals.show', [$deal->id]) : route('deals.index');

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectTo]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deal = Deal::with('leadAgent', 'leadAgent.user')->findOrFail($id);
        $this->deletePermission = user()->permission('delete_deals');

        abort_403(!($this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $deal->added_by == user()->id)
            || ($this->deletePermission == 'owned' && ((!is_null($deal->agent_id) && !is_null($deal->leadAgent) && user()->id == $deal->leadAgent->user->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)))
            || ($this->deletePermission == 'both' && (((!is_null($deal->agent_id) && !is_null($deal->leadAgent) && user()->id == $deal->leadAgent->user->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)) || user()->id == $deal->added_by))
        ));

        $model = new ReflectionClass('App\Models\Deal');

        DB::table('custom_fields_data')
            ->where('model', $model->getName())
            ->where('model_id', $id)
            ->delete();

        Deal::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));

    }

    /**
     * @param CommonRequest $request
     * @return array
     */
    public function changeStatus(CommonRequest $request)
    {
        $deal = Deal::findOrFail($request->leadID);
        $this->editPermission = user()->permission('edit_deals');
        $this->changeLeadStatusPermission = user()->permission('change_deal_stages');

        abort_403(!(($this->editPermission == 'all' || ($this->editPermission == 'added' && $deal->added_by == user()->id)) || $this->changeLeadStatusPermission == 'all'));

        $deal->status_id = $request->statusID;
        $deal->save();

        return Reply::success(__('messages.recordSaved'));
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'change-status':
            $this->changeBulkStatus($request);

            return Reply::success(__('messages.updateSuccess'));

        case 'change-deal-agents':
            $this->changeAgentStatus($request);

            return Reply::success(__('messages.updateSuccess'));

        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_deals') != 'all');

        $model = new ReflectionClass('App\Models\Deal');

        DB::table('custom_fields_data')
            ->where('model', $model->getName())
            ->whereIn('model_id', explode(',', $request->row_ids))
            ->delete();

        Deal::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    protected function changeBulkStatus($request)
    {
        $canEditDeals = user()->permission('edit_deals') == 'all';
        $canChangeStages = user()->permission('change_deal_stages') == 'all';

        abort_403(!($canEditDeals || $canChangeStages));

        $rowIds = explode(',', $request->row_ids);
        $newStatus = $request->status;

        $stage = PipelineStage::find($newStatus);

        if($stage->slug === 'win' || $stage->slug === 'lost'){
           Deal::whereIn('id', $rowIds)->whereNull('close_date')->update(['close_date' => now()->format('Y-m-d')]);
        }

        Deal::whereIn('id', $rowIds)->update(['pipeline_stage_id' => $newStatus]);
    }

    protected function changeAgentStatus($request)
    {
        abort_403(user()->permission('edit_deals') != 'all');
        $agent = LeadAgent::find($request->agent);
        $agentsWithSameUser = LeadAgent::where('user_id', $agent->user_id)->get();
        $rowIds = explode(',', $request->row_ids);

        $leads = Deal::with('leadAgent', 'category')->whereIn('id', $rowIds)->get();

        foreach ($leads as $deal) {
              // Find an agent from the list with matching category
        $matchingAgent = $agentsWithSameUser->firstWhere('lead_category_id', $deal->category_id);

            if ($matchingAgent) {
                // Assign the matching agent to the deal
                $deal->agent_id = $matchingAgent->id;
                $deal->save();
            }
        }

    }

    /**
     *
     * @param int $leadID
     * @return void
     */
    public function followUpCreate($dealID)
    {
        $this->addPermission = user()->permission('add_lead_follow_up');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->dealID = $dealID;
        $this->deal = Deal::findOrFail($dealID);

        return view('leads.followup.create', $this->data);

    }

    public function leadFollowup()
    {
        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';
        $this->view = 'leads.ajax.follow-up';
        $dataTable = new LeadFollowupDataTable();

        return $dataTable->render('leads.show', $this->data);
    }

    /**
     * @param FollowUpStoreRequest $request
     * @return array|void
     * @throws RelatedResourceNotFoundException
     */
    public function followUpStore(FollowUpStoreRequest $request)
    {
        $this->deal = Deal::findOrFail($request->deal_id);

        $this->addPermission = user()->permission('add_lead_follow_up');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        if ($this->deal->next_follow_up != 'yes') {
            return Reply::error(__('messages.leadFollowUpRestricted'));
        }


        $next_follow_up_date = Carbon::createFromFormat(
            $this->company->date_format . ' ' . $this->company->time_format,
            $request->next_follow_up_date . ' ' . $request->start_time
        );


        $followUp = new DealFollowUp();
        $followUp->deal_id = $request->deal_id;
        $followUp->next_follow_up_date = $next_follow_up_date->format('Y-m-d H:i:s');
        $followUp->remark = $request->remark;
        $followUp->send_reminder = $request->send_reminder;
        $followUp->remind_time = $request->remind_time;
        $followUp->remind_type = $request->remind_type;
        $followUp->status = 'pending';

        $followUp->save();

        event(new AutoFollowUpReminderEvent($followUp,true));

        return Reply::success(__('messages.recordSaved'));

    }

    public function editFollow($id)
    {
        $this->follow = DealFollowUp::findOrFail($id);
        $this->editPermission = user()->permission('edit_lead_follow_up');
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->follow->added_by == user()->id)));

        return view('leads.followup.edit', $this->data);
    }

    public function updateFollow(FollowUpStoreRequest $request)
    {
        $this->deal = Deal::findOrFail($request->deal_id);

        $followUp = DealFollowUp::findOrFail($request->id);
        $this->editPermission = user()->permission('edit_lead_follow_up');

        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $followUp->added_by == user()->id)));

        if ($this->deal->next_follow_up != 'yes') {
            return Reply::error(__('messages.leadFollowUpRestricted'));
        }

        $followUp->deal_id = $request->deal_id;

        $followUp->next_follow_up_date = Carbon::createFromFormat($this->company->date_format . ' ' . $this->company->time_format, $request->next_follow_up_date . ' ' . $request->start_time)->format('Y-m-d H:i:s');

        $followUp->remark = $request->remark;
        $followUp->send_reminder = $request->send_reminder;
        $followUp->status = $request->status;
        $followUp->remind_time = $request->remind_time;
        $followUp->remind_type = $request->remind_type;

        $followUp->save();

        return Reply::success(__('messages.updateSuccess'));

    }

    public function deleteFollow($id)
    {
        $followUp = DealFollowUp::findOrFail($id);
        $this->deletePermission = user()->permission('delete_lead_follow_up');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $followUp->added_by == user()->id)));

        DealFollowUp::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function proposals()
    {
        $viewPermission = user()->permission('view_lead_proposals');

        abort_403(!in_array($viewPermission, ['all', 'added']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';
        $this->view = 'leads.ajax.proposal';
        $dataTable = new ProposalDataTable(true);

        return $dataTable->render('leads.show', $this->data);
    }

    public function gdpr()
    {
        $dataTable = new LeadGDPRDataTable();
        $tab = request('tab');
        $this->activeTab = $tab ?: 'gdpr';
        $this->view = 'leads.ajax.gdpr';

        return $dataTable->render('leads.show', $this->data);
    }

    public function consent(Request $request)
    {
        $leadId = $request->leadId;
        $this->consentId = $request->consentId;
        $this->leadId = $leadId;

        $this->consent = PurposeConsent::with(['lead' => function ($query) use ($leadId) {
            $query->where('lead_id', $leadId)->orderByDesc('created_at');
        }])
            ->where('id', $request->consentId)
            ->first();


        return view('leads.gdpr.consent-form', $this->data);
    }

    public function saveLeadConsent(Request $request, $id)
    {
        $deal = Deal::findOrFail($id);
        $consent = PurposeConsent::findOrFail($request->consent_id);

        if ($request->consent_description && $request->consent_description != '') {
            $consent->description = trim_editor($request->consent_description);
            $consent->save();
        }

        // Saving Consent Data
        $newConsentLead = new PurposeConsentLead();
        $newConsentLead->deal_id = $deal->id;
        $newConsentLead->purpose_consent_id = $consent->id;
        $newConsentLead->status = trim($request->status);
        $newConsentLead->ip = $request->ip();
        $newConsentLead->updated_by_id = $this->user->id;
        $newConsentLead->additional_description = $request->additional_description;
        $newConsentLead->save();

        return $request->status == 'agree' ? Reply::success(__('messages.consentOptIn')) : Reply::success(__('messages.consentOptOut'));
    }

    public function importLead()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.menu.deal');

        $this->addPermission = user()->permission('add_deals');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'deals.ajax.import';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('leads.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $rvalue = $this->importFileProcess($request, DealImport::class);

        if($rvalue == 'abort'){
            return Reply::error(__('messages.abortAction'));
        }
        $view = view('deals.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        $batch = $this->importJobProcess($request, DealImport::class, ImportDealJob::class);

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }

    public function destroySession(){

        if (session()->has('is_imported')) {
            session()->forget('is_imported');
        }

        if (session()->has('leads')) {
            session()->forget('leads');
        }

        if (session()->has('leads_count')) {
            session()->forget('leads_count');
        }

        if(session()->has('total_leads')) {
            session()->forget('total_leads');
        }

        if(session()->has('is_deal')) {
            session()->forget('is_deal');
        }
    }

    public function notes()
    {
        $dataTable = new DealNotesDataTable();
        $viewPermission = user()->permission('view_deal_note');

        abort_403(!($viewPermission == 'all' || $viewPermission == 'added' || $viewPermission == 'both' || $viewPermission == 'owned'));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';

        $this->view = 'leads.ajax.notes';

        return $dataTable->render('leads.show', $this->data);
    }

    public function changeFollowUpStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $leadFollowUp = DealFollowUp::find($id);

        if (!is_null($leadFollowUp)) {
            $leadFollowUp->status = $status;
            $leadFollowUp->save();
        }

        return Reply::success(__('messages.leadStatusChangeSuccess'));

    }

    // Get Satges
    public function getStages($id)
    {
        $stages = PipelineStage::where('lead_pipeline_id', $id)->orderBy('priority')->get();

        return Reply::dataOnly(['status' => 'success', 'data' => $stages]);
    }

    // Get Deals
    public function getDeals($id)
    {
        $deals = Deal::allLeads($id);

        return Reply::dataOnly(['status' => 'success', 'data' => $deals]);
    }

    /**
     * @param CommonRequest $request
     * @return array
     */
    public function changeStage(CommonRequest $request)
    {
        $deal = Deal::findOrFail($request->leadID);
        $currentStageSlug = PipelineStage::findOrFail($request->statusID);

        // if the current stage is 'win' or 'lost', do not update
        if (in_array($currentStageSlug->slug, ['win', 'lost'])) {
            return Reply::dataOnly(['status' => 'success']);
        }

        $this->editPermission = user()->permission('edit_deals');
        $this->changeLeadStatusPermission = user()->permission('change_deal_stages');

        abort_403(!(($this->editPermission == 'all' || ($this->editPermission == 'added' && $deal->added_by == user()->id)) || $this->changeLeadStatusPermission == 'all'));

        $deal->pipeline_stage_id = $request->statusID;
        $deal->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['status' => 'success']);
    }

    public function getAgents($id)
    {
        $currentUser = user()->id;
        $leadCategory = LeadCategory::with(['enabledAgents' => function ($query) use ($currentUser) {

            if ($this->viewLeadAgentPermission == 'added') {
                $query->where('added_by', $currentUser);
            } elseif ($this->viewLeadAgentPermission == 'owned') {
                $query->where('user_id', $currentUser);
            } elseif ($this->viewLeadAgentPermission == 'both') {
                $query->where(function ($query) use ($currentUser) {
                    $query->where('added_by', $currentUser)
                        ->orWhere('user_id', $currentUser);
                });
            }

        }])->where('id', $id)->first();

        $deal = Deal::where('id', request()->dealId)->first();
        $groupData = [];
        $userData = [];

        if (isset($leadCategory) && count($leadCategory->enabledAgents) > 0) {

            $activeAgents = $leadCategory->enabledAgents->filter(function ($agent) {
                return $agent->user->status !== 'deactive';
            });

            $selectedAgent = null;
            $data = [];

            if (!is_null($deal)) {
                $selectedAgent = $leadCategory->enabledAgents->firstWhere('id', $deal->agent_id);

                if ($selectedAgent && $selectedAgent->user->status === 'deactive') {
                    $activeAgents->push($selectedAgent);
                }
            }

            foreach ($activeAgents as $agent) {
                $selected = !is_null($deal) && $agent->id == $deal->agent_id;

                $data[] = view('components.user-option', [
                    'user' => $agent->user,
                    'agent' => false,
                    'pill' => false,
                    'selected' => $selected,
                ])->render();
            }

            $groupData = $userData;
        }
        else {
            $data = '<option value="">--</option>';
        }

        return Reply::dataOnly(['data' => $data, 'groupData' => $groupData]);


    }

    public function stageChange(Request $request)
    {
        $deal = Deal::findOrFail($request->leadID);
        $pipelineStageId = $request->statusID;

        $pipelineStage = PipelineStage::findOrFail($request->statusID);

        $pipelineStageName = $pipelineStage->name;

        return view('leads.stage-change', ['deal' => $deal, 'pipelineStageId' => $pipelineStageId, 'pipelineStageName' => $pipelineStageName]);
    }

    public function saveStageChange(StageChangeRequest $request)
    {
        $deal = Deal::findOrFail($request->dealId);

        $deal->pipeline_stage_id = $request->pipelineStageId;
        $deal->close_date = companyToYmd($request->close_date);
        $deal->update();

        if (!empty($request->description)) {
            $dealNote = new DealNote();
            $dealNote->title = $request->title;
            $dealNote->deal_id = $request->dealId;
            $dealNote->details = $request->description;
            $dealNote->save();
        };

        return Reply::success(__('messages.updateSuccess'));
    }

}
