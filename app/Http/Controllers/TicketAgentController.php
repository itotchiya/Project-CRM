<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\TicketAgentGroups\StoreAgentGroup;
use App\Http\Requests\TicketAgentGroups\UpdateAgentGroup;
use App\Models\TicketAgentGroups;
use App\Models\TicketGroup;
use App\Models\User;
use Illuminate\Http\Request;

class TicketAgentController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.ticketAgents';
        $this->activeSettingMenu = 'ticket_settings';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->employees = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'users.image', 'users.salutation')
            ->where('roles.name', 'employee')
            ->get();
        $this->groups = TicketGroup::all();
        return view('ticket-settings.create-agent-modal', $this->data);

    }

    public function store(StoreAgentGroup $request)
    {
        $groups = $request->group_id;

        foreach ($groups as $group) {
            $agent = new TicketAgentGroups();
            $agent->agent_id = $request->user_id;
            $agent->group_id = $group;
            $agent->added_by = user()->id;
            $agent->save();
        }

        if (request()->ajax()) {
            $groups = TicketGroup::with('enabledAgents', 'enabledAgents.user')->get();
            $agentList = '';

            foreach ($groups as $group) {
                if (count($group->enabledAgents) > 0) {

                    $agentList .= '<optgroup label="' . $group->group_name . '">';

                    foreach ($group->enabledAgents as $agent) {
                        $agentList .= '<option value="' . $agent->user->id . '">' . $agent->user->name . ' [' . $agent->user->email . ']' . '</option>';
                    }

                    $agentList .= '</optgroup>';
                }
            }

            return Reply::successWithData(__('messages.recordSaved'), ['teamData' => $agentList]);
        }

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        TicketAgentGroups::where('agent_id', $id)->update(['status' => $request->status]);
        return Reply::success(__('messages.updateSuccess'));
    }

    public function updateGroup(UpdateAgentGroup $request, $id)
    {
        TicketAgentGroups::where('agent_id', $id)->delete();

        foreach($request->groupId as $groupId) {
            TicketAgentGroups::firstOrCreate([
                'agent_id' => $id,
                'group_id' => $groupId
            ]);
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        TicketAgentGroups::where('agent_id', $id)->delete();

        return Reply::success(__('messages.agentRemoveSuccess'));
    }

    public function agentGroups()
    {

        $ticketAgentGroup = TicketAgentGroups::where('agent_id', request()->agent_id)->pluck('group_id')->toArray();

        if(!empty($ticketAgentGroup))
        {

            $ticketGroup = TicketGroup::whereNotIn('id', $ticketAgentGroup)->get();

            return Reply::dataOnly(['data' => $ticketGroup]);

        }
        else
        {
            $ticketGroup = TicketGroup::all();

            return Reply::dataOnly(['data' => $ticketGroup]);
        }
    }

}
