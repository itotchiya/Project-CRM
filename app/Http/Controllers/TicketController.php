<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helper\Reply;
use App\Models\Ticket;
use App\Models\TicketTag;
use App\Models\TicketType;
use App\Models\TicketGroup;
use App\Models\TicketSettingForAgents;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use App\Models\TicketChannel;
use App\Models\TicketTagList;
use App\Models\TicketAgentGroups;
use Illuminate\Support\Facades\DB;
use App\DataTables\TicketDataTable;
use App\Models\TicketReplyTemplate;
use App\Http\Requests\Tickets\StoreTicket;
use App\Http\Requests\Tickets\UpdateTicket;
use App\Models\Project;

class TicketController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.tickets';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('tickets', $this->user->modules));

            return $next($request);
        });
    }

    public function index(TicketDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('view_tickets');
        abort_403(!in_array($this->viewPermission, ['all', 'added', 'owned', 'both']));

        $managePermission = user()->permission('manage_ticket_agent');

        if (!request()->ajax()) {
            $this->channels = TicketChannel::all();
            $this->groups = $managePermission == 'none' ? null : TicketGroup::with(['enabledAgents' => function ($q) use ($managePermission) {

                if ($managePermission == 'added') {
                    $q->where('added_by', user()->id);
                }
                elseif ($managePermission == 'owned') {
                    $q->where('agent_id', user()->id);
                }
                elseif ($managePermission == 'both') {
                    $q->where('agent_id', user()->id)->orWhere('added_by', user()->id);
                }
                else {
                    $q->get();
                }

            }, 'enabledAgents.user'])->get();

            $this->types = TicketType::all();
            $this->tags = TicketTagList::all();
            $this->projects = Project::allProjects();
        }

        return $dataTable->render('tickets.index', $this->data);

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
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_tickets') != 'all');

        Ticket::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    protected function changeBulkStatus($request)
    {
        abort_403(user()->permission('edit_tickets') != 'all');

        Ticket::whereIn('id', explode(',', $request->row_ids))->update(['status' => $request->status]);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_tickets');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->groups = TicketGroup::with('enabledAgents', 'enabledAgents.user')->get();
        $this->types = TicketType::all();
        $this->channels = TicketChannel::all();
        $this->templates = TicketReplyTemplate::all();
        $this->employees = User::allEmployees(null, true, ($this->addPermission == 'all' ? 'all' : null));
        $this->clients = User::allClients();
        $this->countries = countries();
        $this->lastTicket = Ticket::orderBy('id', 'desc')->first();
        $this->pageTitle = __('modules.tickets.addTicket');

        $ticket = new Ticket();

        $getCustomFieldGroupsWithFields = $ticket->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }


        if (request()->default_client) {
            $this->client = User::find(request()->default_client);
        }

        $this->view = 'tickets.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('tickets.create', $this->data);

    }

    public function store(StoreTicket $request)
    {

        $ticket = new Ticket();
        $ticket->subject = $request->subject;
        $ticket->status = 'open';
        $ticket->user_id = ($request->requester_type == 'employee') ? $request->user_id : $request->client_id;

        $ticket->agent_id = $request->agent_id;
        $ticket->type_id = $request->type_id;
        $ticket->priority = $request->priority;
        $ticket->channel_id = $request->channel_id;
        $ticket->group_id = $request->group_id;
        $ticket->project_id = $request->project_id;
        $ticket->save();

        // Save first message
        $reply = new TicketReply();
        $reply->message = trim_editor($request->description);
        $reply->ticket_id = $ticket->id;
        $reply->user_id = $this->user->id; // Current logged in user
        $reply->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $ticket->updateCustomFieldData($request->custom_fields_data);
        }

        // Save tags
        $tags = collect(json_decode($request->tags))->pluck('value');

        foreach ($tags as $tag) {
            $tag = TicketTagList::firstOrCreate([
                'tag_name' => $tag
            ]);
            $ticket->ticketTags()->attach($tag);
        }

        // Log search
        $this->logSearchEntry($ticket->ticket_number, $ticket->subject, 'tickets.show', 'ticket');

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('tickets.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['replyID' => $reply->id, 'redirectUrl' => $redirectUrl]);
    }

    public function show($ticketNumber)
    {
        $managePermission = user()->permission('manage_ticket_agent');
        $this->ticket = Ticket::with('project', 'reply.user.employeeDetail.designation:id,name', 'reply.files')
            ->where('ticket_number', $ticketNumber)
            ->firstOrFail();

        // abort_403(!$this->ticket->canViewTicket());

        $this->ticket = $this->ticket->withCustomFields();
        $this->pageTitle = __('app.menu.ticket') . '#' . $this->ticket->ticket_number;

        $userData = [];
        $groups = TicketGroup::with('enabledAgents', 'enabledAgents.user')->findOrfail($this->ticket->group_id);
        $users = User::findOrFail($this->ticket->user_id);

        foreach ($groups->enabledAgents as $agent) {
            $user = $agent->user;
            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $url = route('employees.show', [$users->id]);
        $userData[] = ['id' => $users->id, 'value' => $users->name, 'image' => $users->image_url, 'link' => $url];
        $this->userData = $userData;

        $this->groups = TicketGroup::with('enabledAgents', 'enabledAgents.user')->get();
        $this->types = TicketType::all();
        $this->channels = TicketChannel::all();
        $this->templates = TicketReplyTemplate::all();
        $this->employees = User::withRole('employee')
            ->join('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id')
            ->select('users.id', 'users.company_id', 'users.name', 'users.email', 'users.created_at', 'users.image', 'designations.name as designation_name', 'users.email_notifications', 'users.mobile', 'users.country_id', 'users.status')->get();

        $this->agents = TicketAgentGroups::query();

            if ($managePermission == 'added') {
                $this->agents->where('added_by', user()->id);
            } elseif ($managePermission == 'owned') {
                $this->agents->where('agent_id', user()->id);
            } elseif ($managePermission == 'both') {
                $this->agents->where(function($q) {
                    $q->where('agent_id', user()->id)
                      ->orWhere('added_by', user()->id);
                });
            } elseif ($managePermission == 'none') {
                $this->agents->where('agent_id', user()->id);
            }

            $this->agents = $this->agents->get();

        $this->ticketAgents = $this->employees->whereIn('id', $this->agents->pluck('agent_id'));
        // for the above
        $this->ticketChart = $this->ticketChartData($this->ticket->user_id);

        $getCustomFieldGroupsWithFields = $this->ticket->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        return view('tickets.edit', $this->data);
    }

    public function ticketChartData($id)
    {
        $labels = ['open', 'pending', 'resolved', 'closed'];
        $data['labels'] = [__('app.open'), __('app.pending'), __('app.resolved'), __('app.closed')];
        $data['colors'] = ['#D30000', '#FCBD01', '#2CB100', '#1d82f5'];
        $data['values'] = [];

        foreach ($labels as $label) {
            $data['values'][] = Ticket::where('user_id', $id)->where('status', $label)->count();
        }

        return $data;
    }

    public function update(UpdateTicket $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->status = $request->status;
        $ticket->save();

        if ($request->type == 'reply') {
            $reply = new TicketReply();
            $reply->message = $request->message;
            $reply->ticket_id = $ticket->id;
            $reply->user_id = $this->user->id; // Current logged in user
            $reply->type = $request->type;
            $reply->save();

            return Reply::successWithData(__('messages.ticketReplySuccess'), ['reply_id' => $reply->id]);
        }

        if ($request->type == 'note') {
            $reply = new TicketReply();
            $reply->message = $request->message2;
            $reply->ticket_id = $ticket->id;
            $reply->user_id = $this->user->id; // Current logged in user
            $reply->type = $request->type;

            $reply->save();

            $reply->users()->sync(request()->user_id);

            return Reply::successWithData(__('messages.noteAddedSuccess'), ['reply_id' => $reply->id]);
        }

        return Reply::dataOnly(['status' => 'success']);
    }

    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);

        abort_403(!$ticket->canDeleteTicket());

        Ticket::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));

    }

    public function updateOtherData(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->group_id = $request->group_id;
        $ticket->type_id = $request->type_id;
        $ticket->priority = $request->priority;
        $ticket->channel_id = $request->channel_id;
        $ticket->status = $request->status;

        $agentGroupData = TicketAgentGroups::where('company_id', company()->id)
            ->where('status', 'enabled')
            ->where('group_id', request()->group_id)
            ->pluck('agent_id')
            ->toArray();

        $ticketData = $ticket->where('company_id', company()->id)
            ->where('group_id', request()->group_id)
            ->whereIn('agent_id', $agentGroupData)
            ->whereIn('status', ['open', 'pending'])
            ->whereNotNull('agent_id')
            ->pluck('agent_id')
            ->toArray();

        $diffAgent = array_diff($agentGroupData, $ticketData);

        if (is_null(request()->agent_id)) {

            if (!empty($diffAgent)) {
                $ticket->agent_id = current($diffAgent);
            }
            else {
                $agentDuplicateCount = array_count_values($ticketData);

                if (!empty($agentDuplicateCount)) {
                    $minVal = min($agentDuplicateCount);
                    $agentId = array_search($minVal, $agentDuplicateCount);
                    $ticket->agent_id = $agentId;
                }

            }
        }
        else {
            $ticket->agent_id = request()->agent_id;
        }

        $ticket->save();

        // Save tags
        $tags = collect(json_decode($request->tags))->pluck('value');
        TicketTag::where('ticket_id', $ticket->id)->delete();

        foreach ($tags as $tag) {
            $tag = TicketTagList::firstOrCreate([
                'tag_name' => $tag
            ]);
            $ticket->ticketTags()->attach($tag);
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    public function refreshCount(Request $request)
    {
        $viewPermission = user()->permission('view_tickets');

        $tickets = Ticket::with('agent','group.enabledAgents');

        if (!is_null($request->startDate) && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            $tickets->where(DB::raw('DATE(`updated_at`)'), '>=', $startDate);
        }

        if (!is_null($request->endDate) && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            $tickets->where(DB::raw('DATE(`updated_at`)'), '<=', $endDate);
        }

        $tagIds = is_array($request->tagId) ? $request->tagId : explode(',', $request->tagId);
        $totalTagLists = TicketTagList::all();
        $totaltags = ($totalTagLists->count() + 1) - count($tagIds);

        if (is_array($request->tagId) && $request->tagId[0] !== 'all') {
            $tickets->join('ticket_tags', 'ticket_tags.ticket_id', 'tickets.id')
              ->whereIn('ticket_tags.tag_id', $tagIds)
              ->groupBy('tickets.id');
        } elseif(is_array($request->tagId) && $request->tagId[0] !== 'all' && $totaltags > 0){
            $tickets->join('ticket_tags', 'ticket_tags.ticket_id', 'tickets.id')
                ->whereIn('ticket_tags.tag_id', $tagIds)
                ->groupBy('tickets.id');
        } elseif(is_array($request->tagId) && $request->tagId[0] == 'all' && $totaltags > 0 && count($tagIds) !== 1){
            $tickets->leftJoin('ticket_tags', 'ticket_tags.ticket_id', '=', 'tickets.id')
                ->where(function ($query) use ($tagIds) {
                    $query->whereIn('ticket_tags.tag_id', $tagIds)
                        ->orWhereNull('ticket_tags.tag_id');
                })->groupBy('tickets.id');
        }elseif(is_array($request->tagId) && $request->tagId[0] == 'all' && count($tagIds) == 1){
            $tickets->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('ticket_tags')
                        ->whereColumn('ticket_tags.ticket_id', 'tickets.id');
                });
        }

        if (!is_null($request->agentId) && $request->agentId != 'all') {
            $tickets->where('agent_id', '=', $request->agentId);
        }

        if (!is_null($request->priority) && $request->priority != 'all') {
            $tickets->where('priority', '=', $request->priority);
        }

        if (!is_null($request->channelId) && $request->channelId != 'all') {
            $tickets->where('channel_id', '=', $request->channelId);
        }

        if (!is_null($request->typeId) && $request->typeId != 'all') {
            $tickets->where('type_id', '=', $request->typeId);
        }

        if (!is_null($request->groupId) && $request->groupId != 'all') {
            $tickets->where('group_id', '=', $request->groupId);
        }

        if (!is_null($request->projectID) && $request->projectID != 'all') {
            $tickets->whereHas('project', function ($q) use ($request) {
                $q->withTrashed()->where('tickets.project_id', $request->projectID);
            });
        }

        $userAssignedInGroup = false;
        if(in_array('employee', user_roles()) && !in_array('admin', user_roles()) && !in_array('client', user_roles())){
            $userAssignedInGroup = TicketGroup::whereHas('enabledAgents', function ($query) {
                $query->where('agent_id', user()->id)->orWhereNull('agent_id');
            })->exists();
        }

        if($userAssignedInGroup == false){

            if ($viewPermission == 'added') {
                $tickets->where('added_by', '=', user()->id);
            }

            if ($viewPermission == 'owned') {
                $tickets->where(function ($query) {
                    $query->where('user_id', '=', user()->id)
                        ->orWhere('agent_id', '=', user()->id);
                });
            }

            if ($viewPermission == 'both') {
                $tickets->where(function ($query) {
                    $query->where('user_id', '=', user()->id)
                        ->orWhere('added_by', '=', user()->id)
                        ->orWhere('agent_id', '=', user()->id);
                });
            }
        }else{

            $ticketSetting = TicketSettingForAgents::first();

            if($ticketSetting?->ticket_scope == 'group_tickets'){

                $userGroupIds = TicketGroup::whereHas('enabledAgents', function ($query) {
                    $query->where('agent_id', user()->id);
                })->pluck('id')->toArray();

                $ticketSettingGroupIds = is_array($ticketSetting?->group_id) ? $ticketSetting?->group_id : explode(',', $ticketSetting?->group_id);

                // Find the common group IDs
                $commonGroupIds = array_intersect($userGroupIds, $ticketSettingGroupIds);

                if($commonGroupIds){
                    $tickets->where(function ($query) use ($commonGroupIds) {
                        $query->where(function ($subQuery) use ($commonGroupIds) {
                            // Conditions related to user and agent
                            $subQuery->where('user_id', '=', user()->id)
                                ->orWhere('added_by', '=', user()->id)
                                ->orWhere('agent_id', '=', user()->id)
                                ->orWhere('agent_id', '!=', user()->id)
                                ->whereIn('group_id', $commonGroupIds);
                        })
                        // Add orWhere for tickets where agent_id is null
                        ->orWhere(function ($subQuery) use ($commonGroupIds) {
                            $subQuery->whereNull('agent_id')
                                ->whereIn('group_id', $commonGroupIds);
                        });
                    });
                }else{
                    $tickets->where(function ($query) use ($userGroupIds) {
                        $query->where(function ($subQuery) use ($userGroupIds) {
                            // Conditions related to user and agent
                            $subQuery->where('user_id', '=', user()->id)
                                ->orWhere('added_by', '=', user()->id)
                                ->orWhere('agent_id', '=', user()->id)
                                ->orWhere('agent_id', '!=', user()->id)
                                ->whereIn('group_id', $userGroupIds);
                        })
                        // Add orWhere for tickets where agent_id is null
                        ->orWhere(function ($subQuery) use ($userGroupIds) {
                            $subQuery->whereNull('agent_id')
                                ->whereIn('group_id', $userGroupIds);
                        });
                    });
                }
            }

            if($ticketSetting?->ticket_scope == 'assigned_tickets'){
                $tickets->where(function ($query) {
                    $query->where('agent_id', '=', user()->id)
                        ->orWhere('user_id', '=', user()->id)
                        ->orWhere('added_by', '=', user()->id);
                });
            }
        }

        $tickets = $tickets->get();

        $openTickets = $tickets->filter(function ($value, $key) {
            return $value->status == 'open';
        })->count();

        $pendingTickets = $tickets->filter(function ($value, $key) {
            return $value->status == 'pending';
        })->count();

        $resolvedTickets = $tickets->filter(function ($value, $key) {
            return $value->status == 'resolved';
        })->count();

        $closedTickets = $tickets->filter(function ($value, $key) {
            return $value->status == 'closed';
        })->count();

        $totalTickets = $tickets->count();

        $ticketData = [
            'totalTickets' => $totalTickets,
            'closedTickets' => $closedTickets,
            'openTickets' => $openTickets,
            'pendingTickets' => $pendingTickets,
            'resolvedTickets' => $resolvedTickets
        ];

        return Reply::dataOnly($ticketData);
    }

    public function changeStatus(Request $request)
    {
        $ticket = Ticket::findOrFail($request->ticketId);

        abort_403(!$ticket->canEditTicket());

        $ticket->update(['status' => $request->status]);

        return Reply::successWithData(__('messages.updateSuccess'), ['status' => 'success']);
    }

    public function agentGroup($id, $exceptThis = null)
    {
        $groups = TicketGroup::with('enabledAgents', 'enabledAgents.user');
        $groups = $groups->where('id', $id)->first();
        $ticketNumber = request()->ticketNumber;
        $ticket = Ticket::where('ticket_number', $ticketNumber)->first();
        $groupData = [];
        $userData = [];

        if (isset($groups) && count($groups->enabledAgents) > 0) {
            $data = [];
            foreach ($groups->enabledAgents as $agent) {

                if($agent->user->id == (int)$exceptThis) {
                    continue;
                }

                $selected = !is_null($ticket) && $agent->user->id == $ticket->agent_id;

                $url = route('employees.show', [$agent->user->id]);
                $userData[] = ['id' => $agent->user->id, 'value' => $agent->user->name, 'image' => $agent->user->image_url, 'link' => $url];

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

        if($exceptThis !== "null"){
            $users = User::findOrFail($exceptThis);
            $url = route('clients.show', [$users->id]);
            $userData[] = ['id' => $users->id, 'value' => $users->name, 'image' => $users->image_url, 'link' => $url];
            $groupData = $userData;
        }

        return Reply::dataOnly(['data' => $data, 'groupData' => $groupData]);


    }

}
