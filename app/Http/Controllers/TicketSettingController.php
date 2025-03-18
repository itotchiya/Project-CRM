<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\TicketChannel;
use App\Models\TicketEmailSetting;
use App\Models\TicketGroup;
use App\Models\TicketReplyTemplate;
use App\Models\TicketType;
use App\Models\User;
use App\Models\LeadSetting;
use App\Models\TicketSettingForAgents;
use Illuminate\Http\Request;

class TicketSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.ticketSettings';
        $this->activeSettingMenu = 'ticket_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_ticket_setting') == 'all' && in_array('tickets', user_modules())));
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->agents = User::with('employeeDetail.designation:id,name')->whereHas('agent')->with('agentGroup', 'agent')->get();

        $this->employees = User::doesntHave('agent')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at')
            ->where('roles.name', 'employee')
            ->get();

        $this->groups = TicketGroup::all();
        $this->ticketTypes = TicketType::all();
        $this->templates = TicketReplyTemplate::all();
        $this->channels = TicketChannel::all();
        $this->ticketEmailSetting = TicketEmailSetting::first();
        $this->ticketSettings = LeadSetting::select('ticket_round_robin_status')->first();
        $this->ticketAgentSettings = TicketSettingForAgents::first();

        $this->view = 'ticket-settings.ajax.agent';

        $tab = request('tab');

        switch ($tab) {
        case 'type':
            $this->pageTitle = 'app.menu.ticketTypes';
            $this->view = 'ticket-settings.ajax.type';
            break;
        case 'channel':
            $this->pageTitle = 'app.menu.ticketChannel';
            $this->view = 'ticket-settings.ajax.channel';
            break;
        case 'reply-template':
            $this->pageTitle = 'app.menu.replyTemplates';
            $this->view = 'ticket-settings.ajax.reply-template';
            break;
        case 'round-robin':
            $this->pageTitle = 'modules.deal.dealMethod';
            $this->view = 'ticket-settings.ajax.round-robin';
            break;
        case 'email-sync':
            $this->pageTitle = 'app.menu.emailSync';
            $this->view = 'ticket-settings.ajax.email-sync';
            break;
        case 'group-manage':
            $this->pageTitle = 'app.menu.groupManage';
            $this->view = 'ticket-settings.ajax.group-manage';
            break;
        case 'ticket-agent-setting':
            $this->pageTitle = 'modules.deal.ticketSetting';
            $this->view = 'ticket-settings.ajax.ticket-agent-setting';
            break;
        default:
            $this->pageTitle = 'app.menu.ticketAgents';
            $this->view = 'ticket-settings.ajax.agent';
            break;
        }

        $this->activeTab = $tab ?: 'agent';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('ticket-settings.index', $this->data);

    }

    public function updateTicketSettingForAgent($id, Request $request)
    {
        $ticketSetting = TicketSettingForAgents::first();

        if(!$ticketSetting){
            $ticketSetting = new TicketSettingForAgents;
            $ticketSetting->company_id = $id;
        }

        $ticketSetting->user_id = user()->id;
        $ticketSetting->ticket_scope = $request->ticketScope;
        $ticketSetting->group_id = $request->groupId;
        $ticketSetting->updated_by = user()->id;

        $ticketSetting->save();

        return reply::success(__('messages.updateSuccess'));

    }
}
