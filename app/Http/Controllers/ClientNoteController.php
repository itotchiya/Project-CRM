<?php

namespace App\Http\Controllers;

use App\DataTables\ClientNotesDataTable;
use App\Helper\Reply;
use App\Http\Requests\Admin\Client\StoreClientNote;
use App\Models\ClientNote;
use App\Models\ClientUserNote;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientNoteController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.notes';
        $this->middleware(function ($request, $next) {
            return $next($request);
        });
    }

    public function index(ClientNotesDataTable $dataTable)
    {
        abort_403(in_array(user()->permission('view_client_note'), ['none']));

        $this->addClientNotePermission = user()->permission('add_client_note');

        return $dataTable->render('clients.notes.index', $this->data);
    }

    public function create()
    {
        abort_403(!in_array(user()->permission('add_client_note'), ['all', 'added', 'both']));
        $this->pageTitle = __('app.addClientNote');
        $this->clientId = request('client');
        $projectMember = [];

        if (in_array('client', user_roles())) {
            $this->employees = [];

            $clientProject = Project::where('client_id', user()->id)->pluck('id')->toArray();

            if (!empty($clientProject)) {

                $member = ProjectMember::with('user')->whereIn('project_id', $clientProject)->get();

                foreach ($member as $members) {
                    $projectMember[] = $members->user;
                }

                $this->employees = $projectMember;
            }
        }
        else {

            $this->employees = User::allEmployees();

        }

        $this->view = 'clients.notes.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('clients.create', $this->data);
    }

    public function show($id)
    {
        $this->note = ClientNote::findOrFail($id);

        $callingFunction = debug_backtrace()[1]['function'];  // Check which function called this show function

        if ($this->note->ask_password == 1 && $callingFunction != 'showVerified') { // Password protected note should be called from showVerified function
            abort(403, __('messages.permissionDenied'));
        }

        /** @phpstan-ignore-next-line */
        $this->noteMembers = $this->note->members->pluck('user_id')->toArray();
        $this->employees = User::whereIn('id', $this->noteMembers)->get();

        $viewClientNotePermission = user()->permission('view_client_note');

        abort_403(!($viewClientNotePermission == 'all'
            || ($viewClientNotePermission == 'added' && $this->note->added_by == user()->id)

            || ($viewClientNotePermission == 'owned' && in_array(user()->id, $this->noteMembers) && in_array('employee', user_roles()))

            || ($viewClientNotePermission == 'both' && ($this->note->added_by == user()->id || in_array(user()->id, $this->noteMembers))) /* @phpstan-ignore-line */

            || (in_array('client', user_roles()) && $this->note->is_client_show == 1)
            || ($this->note->type == 0 && $viewClientNotePermission != 'none')
        )
        );

        $this->pageTitle = __('app.client') . ' ' . __('app.note');

        $this->view = 'clients.notes.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('clients.create', $this->data);

    }

    public function store(StoreClientNote $request)
    {
        abort_403(!in_array(user()->permission('add_client_note'), ['all', 'added', 'both']));

        $this->employees = User::allEmployees();

        $note = new ClientNote();
        $note->title = $request->title;
        $note->client_id = $request->client_id;
        $note->details = $request->details;
        $note->type = $request->type;

        if (in_array('client', user_roles())) {
            $note->is_client_show = 1;
        }
        else {
            $note->is_client_show = $request->is_client_show ? $request->is_client_show : '';
        }

        $note->ask_password = $request->ask_password ? $request->ask_password : '';

        $note->save();

        /* if note type is private */
        if ($request->type == 1) {
            $users = $request->user_id;

            if (!is_null($users)) {
                foreach ($users as $user) {
                    ClientUserNote::firstOrCreate([
                        'user_id' => $user,
                        'client_note_id' => $note->id
                    ]);
                }
            }
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => in_array('client', user_roles()) ? route('client-notes.index') : route('clients.show', $note->client_id) . '?tab=notes']);
    }

    public function edit($id)
    {

        $this->pageTitle = __('app.editClientNote');

        $this->note = ClientNote::findOrFail($id);
        $editClientNotePermission = user()->permission('view_client_note');

        abort_403(!($editClientNotePermission == 'all'
            || ($editClientNotePermission == 'added' && user()->id == $this->note->added_by)
            || ($editClientNotePermission == 'both' && user()->id == $this->note->added_by)));

        $projectMember = [];

        if (in_array('client', user_roles())) {
            $this->employees = [];

            $clientProject = Project::where('client_id', user()->id)->pluck('id')->toArray();

            if (!empty($clientProject)) {

                $member = ProjectMember::with('user')->whereIn('project_id', $clientProject)->get();

                foreach ($member as $members) {
                    $projectMember[] = $members->user;
                }

                $this->employees = $projectMember;
            }
        }
        else {

            $this->employees = User::allEmployees();

        }

        /** @phpstan-ignore-next-line */
        $this->noteMembers = $this->note->members->pluck('user_id')->toArray();
        $this->clientId = $this->note->client_id;


        $this->view = 'clients.notes.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('clients.create', $this->data);

    }

    public function update(StoreClientNote $request, $id)
    {

        $note = ClientNote::findOrFail($id);
        $note->title = $request->title;
        $note->details = $request->details;
        $note->type = $request->type;

        if (in_array('client', user_roles())) {
            $note->is_client_show = 1;
        }
        else {
            $note->is_client_show = $request->is_client_show ? $request->is_client_show : '';
        }

        $note->ask_password = $request->ask_password ?: '';
        $note->save();

        /* if note type is private */
        if ($request->type == 1) {
            // delete all data of this client_note_id from client_user_notes
            ClientUserNote::where('client_note_id', $note->id)->delete();

            $users = $request->user_id;

            if (!is_null($users)) {
                foreach ($users as $user) {
                    ClientUserNote::firstOrCreate([
                        'user_id' => $user,
                        'client_note_id' => $note->id
                    ]);
                }
            }
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => in_array('client', user_roles()) ? route('client-notes.index') : route('clients.show', $note->client_id) . '?tab=notes']);

    }

    public function destroy($id)
    {
        $this->contact = ClientNote::findOrFail($id);
        $this->deletePermission = user()->permission('delete_client_note');

        abort_403(!($this->deletePermission == 'all'
                || ($this->deletePermission == 'added' && $this->contact->added_by == user()->id))
            || ($this->deletePermission == 'both' && $this->contact->added_by == user()->id)
        );
        $this->contact->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function applyQuickAction(Request $request)
    {
        if ($request->action_type == 'delete') {
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        }

        return Reply::error(__('messages.selectAction'));
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_client_note') !== 'all');

        ClientNote::whereIn('id', explode(',', $request->row_ids))->delete();

        return true;
    }

    public function askForPassword($id)
    {
        $this->note = ClientNote::findOrFail($id);

        return view('clients.notes.verify-password', $this->data);
    }

    public function checkPassword(Request $request)
    {
        $this->client = User::findOrFail($this->user->id);

        if (Hash::check($request->password, $this->client->password)) {
            return Reply::success(__('messages.passwordMatched'));
        }

        return Reply::error(__('messages.incorrectPassword'));
    }

    public function showVerified($id)
    {
        return $this->show($id);
    }

}
