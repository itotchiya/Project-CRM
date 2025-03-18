<?php

namespace App\Http\Controllers;

use App\DataTables\NoticeBoardDataTable;
use App\Helper\Reply;
use App\Http\Requests\Notice\StoreNotice;
use App\Models\AutomateShift;
use App\Models\Notice;
use App\Models\NoticeBoardUser;
use App\Models\NoticeFile;
use App\Models\NoticeUser;
use App\Models\Team;
use App\Models\User;
use App\Scopes\ActiveScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoticeController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.noticeBoard';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(NoticeBoardDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_notice');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('notices.index', $this->data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_notice');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->teams = Team::all();
        $this->pageTitle = __('modules.notices.addNotice');
        $this->view = 'notices.ajax.create';
        $this->employees = User::allEmployees(null, true);
        $this->clients = User::allClients(null, true);

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('notices.create', $this->data);
    }

    /**
     * @param StoreNotice $request
     * @return array|void
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreNotice $request)
    {
        $this->addPermission = user()->permission('add_notice');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        DB::beginTransaction();

        $notice = new Notice();
        $notice->heading = $request->heading;
        $notice->description = trim_editor($request->description);
        $notice->to = $request->to;
        $notice->department_id = $request->team_id == 0 ? null : $request->team_id;
        $notice->save();

        if (($request->to == 'employee' && isset($request->employees)) || ($request->to == 'client' && isset($request->clients))) {
            $noticeUsers = [];
            $type = $request->to;
            $users = ($type == 'employee') ? $request->employees : $request->clients;

            foreach ($users as $user) {
                $noticeUsers[] = [
                    'notice_id' => $notice->id,
                    'type' => $type,
                    'user_id' => $user
                ];
            }

            if (!empty($noticeUsers)) {
                NoticeBoardUser::insert($noticeUsers);
            }
        }

        DB::commit();

        return Reply::successWithData(__('messages.recordSaved'), ['noticeID' => $notice->id, 'redirectUrl' => route('notices.index')]);

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->notice = Notice::with('member', 'member.user')->findOrFail($id);
        $this->viewPermission = user()->permission('view_notice');
        abort_403(!(
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->notice->added_by == user()->id)
            || ($this->viewPermission == 'owned' && in_array($this->notice->to, user_roles()))
            || ($this->viewPermission == 'both' && (in_array($this->notice->to, user_roles()) || $this->notice->added_by == user()->id))
        ));

        $this->deletePermission = user()->permission('delete_notice');

        $readUser = $this->notice->member->filter(function ($value, $key) {
            return $value->user_id == $this->user->id && $value->notice_id == $this->notice->id;
        })->first();

        if ($readUser) {
            $readUser->read = 1;
            $readUser->save();
        }

        $noticeEmployees = NoticeBoardUser::where('notice_id', $this->notice->id)->where('type', 'employee')->pluck('user_id')->toArray();
        $this->noticeEmployees = User::whereIn('id', $noticeEmployees)->get();

        $noticeClients = NoticeBoardUser::where('notice_id', $this->notice->id)->where('type', 'client')->pluck('user_id')->toArray();
        $this->noticeClients = User::whereIn('id', $noticeClients)->get();

        if(in_array('client', user_roles())){
            $this->noticeClients = User::where('id', auth()->id())->get();
        }

        $this->readMembers = $this->notice->member->filter(function ($value, $key) {
            return $value->read == 1;
        });


        $this->unReadMembers = $this->notice->member->filter(function ($value, $key) {
            return $value->read == 0;
        });

        $this->pageTitle = __('app.menu.noticeBoard');

        $this->view = 'notices.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('notices.create', $this->data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->notice = Notice::findOrFail($id);
        $this->editPermission = user()->permission('edit_notice');

        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->notice->added_by == user()->id)
            || ($this->editPermission == 'owned' && in_array($this->notice->to, user_roles()))
            || ($this->editPermission == 'both' && (in_array($this->notice->to, user_roles()) || $this->notice->added_by == user()->id))
        ));

        $this->teams = Team::all();
        $this->pageTitle = __('modules.notices.updateNotice');

        $this->employees = $this->notice->department_id
            ? User::departmentUsers($this->notice->department_id)
            : User::allEmployees(null, true);

        $this->clients = User::allClients(null, true);

        $this->employeeArray = NoticeBoardUser::where('notice_id', $id)->where('type', 'employee')->pluck('user_id')->toArray();
        $this->clientArray = NoticeBoardUser::where('notice_id', $id)->where('type', 'client')->pluck('user_id')->toArray();

        $this->view = 'notices.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('notices.create', $this->data);

    }

    /**
     * @param StoreNotice $request
     * @param int $id
     * @return array|void
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreNotice $request, $id)
    {
        $notice = Notice::findOrFail($id);
        $this->editPermission = user()->permission('edit_notice');

        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && $notice->added_by == user()->id)
            || ($this->editPermission == 'owned' && in_array($notice->to, user_roles()))
            || ($this->editPermission == 'both' && (in_array($notice->to, user_roles()) || $notice->added_by == user()->id))
        ));

        DB::beginTransaction();

        $notice->heading = $request->heading;
        $notice->description = trim_editor($request->description);
        $notice->to = $request->to;
        $notice->department_id = $request->team_id == 0 ? null : $request->team_id;
        $notice->save();

        $type = $request->to;
        $users = ($type == 'employee') ? $request->employees : $request->clients;

        if (!empty($users)) {
            $noticeUsers = [];

            foreach ($users as $user) {
                $exists = NoticeBoardUser::where('notice_id', $notice->id)
                    ->where('type', $type)
                    ->where('user_id', $user)
                    ->exists();

                if (!$exists) {
                    $noticeUsers[] = [
                        'notice_id' => $notice->id,
                        'type' => $type,
                        'user_id' => $user
                    ];
                }
            }

            if (!empty($noticeUsers)) {
                NoticeBoardUser::insert($noticeUsers);
            }
        }

        DB::commit();

        return Reply::successWithData(__('messages.updateSuccess'), ['noticeID' => $notice->id, 'redirectUrl' => route('notices.index')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notice = Notice::findOrFail($id);
        $this->deletePermission = user()->permission('delete_notice');
        abort_403(!(
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $notice->added_by == user()->id)
            || ($this->deletePermission == 'owned' && in_array($notice->to, user_roles()))
            || ($this->deletePermission == 'both' && (in_array($notice->to, user_roles()) || $notice->added_by == user()->id))
        ));

        $file = NoticeFile::where('notice_id', $id)->first();

        if ($file) {
            $file->delete();
        }

        Notice::destroy($id);
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('notices.index')]);

    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);
                return Reply::success(__('messages.deleteSuccess'));
        default:
                return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_notice') != 'all');

        Notice::whereIn('id', explode(',', $request->row_ids))->forceDelete();
    }

}
