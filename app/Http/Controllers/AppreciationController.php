<?php

namespace App\Http\Controllers;

use App\DataTables\AppreciationsDataTable;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Appreciation\StoreRequest;
use App\Http\Requests\Appreciation\UpdateRequest;
use App\Models\Award;
use App\Models\User;
use App\Models\Appreciation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppreciationController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.appreciation';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AppreciationsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_appreciation');
        $this->appreciations = Award::with('awardIcon')->get();
        $this->employees = User::allEmployees(null, true, 'all');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('appreciations.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_appreciation');
        $this->viewPermission = user()->permission('view_appreciation');
        abort_403($this->addPermission != 'all');

        $this->employees = User::allEmployees(null, true, 'all');
        $this->appreciationTypes = Award::with('awardIcon')->where('status', 'active')->get();
        $this->pageTitle = __('modules.appreciations.appreciation');
        $this->empID = request()->empid;
        $this->view = 'appreciations.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('appreciations.create', $this->data);
    }

    public function store(StoreRequest $request)
    {
        $this->addPermission = user()->permission('add_appreciation');
        abort_403($this->addPermission != 'all');

        $appreciation = new Appreciation();
        $appreciation->award_id = $request->award;
        $appreciation->summary = trim_editor($request->summery);
        $appreciation->award_date = Carbon::createFromFormat($this->company->date_format, $request->award_date);
        $appreciation->award_to = $request->given_to;
        $appreciation->added_by = user()->id;

        if ($request->hasFile('photo')) {
            Files::deleteFile($appreciation->image, 'appreciation');
            $appreciation->image = Files::uploadLocalOrS3($request->photo, 'appreciation');
        }

        $appreciation->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('appreciations.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->appreciation = Appreciation::with('award')->findOrFail($id);
        $this->viewPermission = user()->permission('view_appreciation');
        $this->pageTitle = __('app.menu.appreciation');

        abort_403(!(
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->appreciation->added_by == user()->id)
            || ($this->viewPermission == 'owned' && $this->appreciation->award_to == user()->id)
            || ($this->viewPermission == 'both' && ($this->appreciation->added_by == user()->id || $this->appreciation->award_to == user()->id))
        ));

        $this->view = 'appreciations.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('appreciations.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_appreciation');
        $this->appreciation = Appreciation::findOrFail($id);

        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->appreciation->added_by == user()->id)
            || ($this->editPermission == 'owned' && $this->appreciation->award_to == user()->id)
            || ($this->editPermission == 'both' && ($this->appreciation->added_by == user()->id || $this->appreciation->award_to == user()->id))
        ));

        $this->pageTitle = __('app.menu.appreciation');
        $this->employees = User::allEmployees(null, false, 'all');

        $activeEmployees = $this->employees->filter(function ($employee) {
            return $employee->status !== 'deactive';
        });

        $selectedEmployee = $this->employees->firstWhere('id', $this->appreciation->award_to);

        if ($selectedEmployee && $selectedEmployee->status === 'deactive') {
            $this->employees = $activeEmployees->push($selectedEmployee);
        } else {
            $this->employees = $activeEmployees;
        }

        $this->appreciationTypes = Award::with('awardIcon')->where('status', 'active')->get();


        $this->view = 'appreciations.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('appreciations.create', $this->data);

    }

    public function update(UpdateRequest $request, $id)
    {
        $appreciation = Appreciation::findOrFail($id);
        $appreciation->award_id = $request->award;
        $appreciation->summary = trim_editor($request->summery);
        $appreciation->award_date = Carbon::createFromFormat($this->company->date_format, $request->award_date);
        $appreciation->award_to = $request->given_to;

        if ($request->photo_delete == 'yes') {
            Files::deleteFile($appreciation->image, 'appreciation');
            $appreciation->image = null;
        }

        if ($request->hasFile('photo')) {
            Files::deleteFile($appreciation->image, 'appreciation');
            $appreciation->image = Files::uploadLocalOrS3($request->photo, 'appreciation', 300);
        }

        $appreciation->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('appreciations.index');
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->appreciation = Appreciation::findOrFail($id);
        $this->deletePermission = user()->permission('delete_appreciation');
        abort_403(!(
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $this->appreciation->added_by == user()->id)
            || ($this->deletePermission == 'owned' && $this->appreciation->award_to == user()->id)
            || ($this->deletePermission == 'both' && ($this->appreciation->added_by == user()->id || $this->appreciation->award_to == user()->id))
        ));

        Appreciation::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('appreciations.index')]);

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
        $deletePermission = user()->permission('delete_appreciation');
        abort_403($deletePermission != 'all');
        $item = explode(',', $request->row_ids);

        if (($key = array_search('on', $item)) !== false) {
            unset($item[$key]);
        }

        Appreciation::whereIn('id', $item)->delete();
    }

}
