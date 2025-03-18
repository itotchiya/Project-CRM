<?php

namespace App\Http\Controllers;

use App\DataTables\LeadNotesDataTable;
use App\Helper\Reply;
use App\Http\Requests\Lead\StoreLeadNote;
use App\Http\Requests\StoreDealNote;
use App\Models\DealNote;
use App\Models\User;
use Illuminate\Http\Request;

class DealNoteController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.notes';
        $this->middleware(function ($request, $next) {
            return $next($request);
        });
    }

    public function index(LeadNotesDataTable $dataTable)
    {
        abort_403(!(in_array(user()->permission('view_deal_note'), ['all', 'added'])));

        return $dataTable->render('leads.notes.index', $this->data);
    }

    public function create()
    {
        abort_403(!in_array(user()->permission('add_deal_note'), ['all', 'added', 'both']));

        $this->employees = User::allEmployees();

        $this->pageTitle = __('modules.deal.addDealNote');
        $this->leadId = request('lead');

        $this->view = 'leads.notes.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('leads.create', $this->data);
    }

    public function show($id)
    {
        $this->note = DealNote::findOrFail($id);

        /** @phpstan-ignore-next-line */
        $this->noteMembers = $this->note->members->pluck('user_id')->toArray();
        $this->employees = User::whereIn('id', $this->noteMembers)->get();

        $viewClientNotePermission = user()->permission('view_deal_note');
        $memberIds = $this->note->members->pluck('user_id')->toArray(); /** @phpstan-ignore-line */

        abort_403(!($viewClientNotePermission == 'all'
            || ($viewClientNotePermission == 'added' && $this->note->added_by == user()->id)
            || ($viewClientNotePermission == 'owned' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
            || ($viewClientNotePermission == 'both' && (in_array(user()->id, $memberIds) || $this->note->added_by == user()->id))
            )
        );

        $this->pageTitle = __('modules.deal.dealNote');

        if (request()->ajax()) {
            $html = view('leads.notes.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'leads.notes.show';
        return view('leads.create', $this->data);

    }

    public function store(StoreDealNote $request)
    {
        abort_403(!in_array(user()->permission('add_deal_note'), ['all', 'added', 'both']));

        $note = new DealNote();
        $note->title = $request->title;
        $note->deal_id = $request->lead_id;
        $note->details = trim_editor($request->details);
        $note->save();

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('deals.show', $note->deal_id) . '?tab=notes']);
    }

    public function edit($id)
    {
        $this->pageTitle = __('modules.deal.editDealNote');

        $this->note = DealNote::findOrFail($id);
        $editClientNotePermission = user()->permission('view_deal_note');
        $memberIds = $this->note->members->pluck('user_id')->toArray(); /** @phpstan-ignore-line */

        abort_403(!($editClientNotePermission == 'all'
            || ($editClientNotePermission == 'added' && user()->id == $this->note->added_by)
            || ($editClientNotePermission == 'owned' && in_array('employee', user_roles()))
            || ($editClientNotePermission == 'both' && ($this->note->added_by == user()->id ))
        ));

        /** @phpstan-ignore-next-line */
        $this->leadId = $this->note->deal_id;

        if (request()->ajax()) {
            $html = view('leads.notes.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'leads.notes.edit';
        return view('leads.create', $this->data);

    }

    public function update(StoreDealNote $request, $id)
    {
        $note = DealNote::findOrFail($id);
        $note->title = $request->title;
        $note->details = trim_editor($request->details);
        $note->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('deals.show', $note->deal_id) . '?tab=notes']);
    }

    public function destroy($id)
    {
        $this->note = DealNote::findOrFail($id);
        $this->deletePermission = user()->permission('delete_deal_note');

        abort_403(!($this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $this->note->added_by == user()->id))
            || ($this->deletePermission == 'owned' && in_array('employee', user_roles()))
            || ($this->deletePermission == 'both' && ($this->note->added_by == user()->id ))
        );
        $this->note->delete();

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('deals.show', $this->note->deal_id) . '?tab=notes']);
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
        abort_403(!(user()->permission('delete_deal_note') == 'all'));

        DealNote::whereIn('id', explode(',', $request->row_ids))->delete();
        return true;
    }

}
