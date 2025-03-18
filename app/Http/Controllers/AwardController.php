<?php

namespace App\Http\Controllers;

use App\DataTables\AwardDataTable;
use App\Helper\Reply;
use App\Http\Requests\Appreciation\AppreciationType\StoreRequest;
use App\Http\Requests\Appreciation\AppreciationType\UpdateRequest;
use App\Models\Award;
use App\Models\AwardIcon;
use Illuminate\Http\Request;

class AwardController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.award';
    }

    public function index(AwardDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_appreciation');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('awards.index', $this->data);

    }

    public function create()
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        abort_403(!($this->manageAppreciationPermission == 'all'));

        $this->icons = AwardIcon::all();

        $this->pageTitle = __('modules.appreciations.addAppreciationType');


        $this->view = 'awards.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('awards.create', $this->data);
    }

    public function quickCreate()
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        abort_403(!($this->manageAppreciationPermission == 'all'));

        $this->icons = AwardIcon::all();

        $this->pageTitle = __('modules.appreciations.addAppreciationType');

        return view('appreciations.ajax.create_appreciation_type', $this->data);
    }

    public function quickStore(StoreRequest $request)
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        abort_403(!($this->manageAppreciationPermission == 'all'));

        $award = new Award();
        $award->title = $request->title;
        $award->award_icon_id = $request->icon;
        $award->color_code = $request->color_code;
        $award->summary = $request->summery;
        $award->save();

        $awards = Award::with('awardIcon')->where('status', 'active')->get();

        $options = $this->options($awards, $award);

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }

    public static function options($items, $group = null): string
    {
        $options = '<option value="">--</option>';

        foreach ($items as $item) {

            $name = $item->title;

            $selected = (!is_null($group) && ($item->id == $group->id)) ? 'selected' : '';
            $icon = "<i class='bi bi-" . $item->awardIcon->icon . "' style='color:" . $item->color_code . "'></i>     ";

            $options .= '<option ' . $selected . '  data-content="' . $icon . ' ' . $name . '" value="' . $item->id . '">
                                                ' . $name . '
                                            </option>';
        }

        return $options;
    }

    public function store(StoreRequest $request)
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        abort_403(!($this->manageAppreciationPermission == 'all'));

        $appreciation = new Award();
        $appreciation->title = $request->title;
        $appreciation->award_icon_id = $request->icon;
        $appreciation->color_code = $request->color_code;
        $appreciation->summary = $request->summery;
        $appreciation->save();

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('awards.index')]);

    }

    public function show($id)
    {
        $this->appreciationType = Award::findOrFail($id);

        $this->manageAppreciationPermission = user()->permission('view_appreciation');
        abort_403(!($this->manageAppreciationPermission != 'none'));

        $this->pageTitle = $this->appreciationType->title;


        $this->view = 'awards.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('awards.create', $this->data);

    }

    public function edit($id)
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        abort_403(!($this->manageAppreciationPermission == 'all'));

        $this->appreciationType = Award::findOrFail($id);

        $this->icons = AwardIcon::all();
        $this->pageTitle = __('modules.awards.appreciationType');

        $this->view = 'awards.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('awards.create', $this->data);

    }

    public function update(UpdateRequest $request, $id)
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        abort_403(!($this->manageAppreciationPermission == 'all'));

        $appreciation = Award::findOrFail($id);
        $appreciation->title = $request->title;
        $appreciation->award_icon_id = $request->icon;
        $appreciation->summary = $request->summery;
        $appreciation->color_code = $request->color_code;
        $appreciation->status = $request->status;

        $appreciation->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('awards.index')]);
    }

    public function destroy($id)
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        abort_403(!($this->manageAppreciationPermission == 'all'));
        Award::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('awards.index')]);

    }

    public function changeStatus(Request $request)
    {
        abort_403(user()->permission('manage_award') != 'all');

        $appreciationId = $request->appreciationId;
        $status = $request->status;
        $award = Award::findOrFail($appreciationId);
        $award->status = $status;
        $award->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'change-leave-status':
            $this->changeBulkStatus($request);

            return Reply::success(__('messages.updateSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('manage_award') != 'all');
        $item = explode(',', $request->row_ids);

        if (($key = array_search('on', $item)) !== false) {
            unset($item[$key]);
        }

        Award::whereIn('id', $item)->delete();
    }

    protected function changeBulkStatus($request)
    {
        abort_403(user()->permission('manage_award') != 'all');
        $item = explode(',', $request->row_ids);

        if (($key = array_search('on', $item)) !== false) {
            unset($item[$key]);
        }

        Award::whereIn('id', $item)->update(['status' => $request->status]);
    }

}
