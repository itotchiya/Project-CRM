<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Designation;
use App\Models\EmployeeDetails;
use Illuminate\Http\Request;
use App\DataTables\DesignationDataTable;
use App\Http\Requests\Designation\StoreRequest;
use App\Http\Requests\Designation\UpdateRequest;

class DesignationController extends AccountBaseController
{
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.designation');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('employees', $this->user->modules));
            return $next($request);
        });
    }

    public function index(DesignationDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_designation');
        abort_403(!in_array($viewPermission, ['all']));

        // get all designations
        $this->designations = Designation::all();
        return $dataTable->render('designation.index', $this->data);
    }

    public function create()
    {
        $this->designations = Designation::all();
        $this->view = 'designation.ajax.create';

        if (request()->model == true) {
            return view('employees.create_designation', $this->data);
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('designation.create', $this->data);
    }

    /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreRequest $request)
    {
        $group = new Designation();
        $group->name = $request->name;
        $group->parent_id = $request->parent_id ? $request->parent_id : null;
        $group->save();

        $redirectUrl = urldecode($request->redirect_url);
        $this->designations = Designation::all();

        if ($redirectUrl == '') {
            $redirectUrl = route('designations.index');
        }


        return Reply::successWithData(__('messages.recordSaved'), ['designations' => $this->designations, 'redirectUrl' => $redirectUrl]);
    }

    public function show($id)
    {
        $this->designation = Designation::findOrFail($id);
        $this->parent = Designation::where('id', $this->designation->parent_id)->first();

        $this->view = 'designation.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('designation.create', $this->data);
    }

    public function edit($id)
    {
        $this->designation = Designation::findOrFail($id);

        $designations = Designation::where('id', '!=', $this->designation->id)->get();

        $childDesignations = $designations->where('parent_id', $this->designation->id)->pluck('id')->toArray();

        $designations = $designations->where('parent_id', '!=', $this->designation->id);

        // remove child designations
        $this->designations = $designations->filter(function ($value, $key) use ($childDesignations) {
            return !in_array($value->parent_id, $childDesignations);
        });


        $this->view = 'designation.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('designation.create', $this->data);

    }

    /**
     * @param UpdateRequest $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */

    public function update(UpdateRequest $request, $id)
    {
        $editDesignation = user()->permission('edit_designation');
        abort_403($editDesignation != 'all');

        $group = Designation::findOrFail($id);

        if($request->parent_id != null)
        {
            $parent = Designation::findOrFail($request->parent_id);

            if($id == $parent->parent_id)
            {
                $parent->parent_id = $group->parent_id;
                $parent->save();
            }
        }

        $group->name = strip_tags($request->designation_name);
        $group->parent_id = $request->parent_id ? $request->parent_id : null;
        $group->save();

        $redirectUrl = route('designations.index');
        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_designation');
        abort_403($deletePermission != 'all');

        EmployeeDetails::where('designation_id', $id)->update(['designation_id' => null]);
        $designation = Designation::where('parent_id', $id)->get();
        $parent = Designation::findOrFail($id);

        if(count($designation) > 0)
        {
            foreach($designation as $designation)
            {
                $child = Designation::findOrFail($designation->id);
                $child->parent_id = $parent->parent_id;
                $child->save();
            }
        }

        Designation::destroy($id);

        $redirectUrl = route('designations.index');
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    public function applyQuickAction(Request $request)
    {

        if ($request->action_type === 'delete') {
            $this->deleteRecords($request);
            return Reply::success(__('messages.deleteSuccess'));
        }

        return Reply::error(__('messages.selectAction'));

    }

    protected function deleteRecords($request)
    {
        $deletePermission = user()->permission('delete_department');
        abort_403($deletePermission != 'all');

        $rowIds = explode(',', $request->row_ids);

        if (($key = array_search('on', $rowIds)) !== false) {
            unset($rowIds[$key]);
        }

        foreach ($rowIds as $id) {
            EmployeeDetails::where('designation_id', $id)->update(['designation_id' => null]);
            $designation = Designation::where('parent_id', $id)->get();
            $parent = Designation::findOrFail($id);

            if(count($designation) > 0)
            {
                foreach($designation as $designation)
                {
                    $child = Designation::findOrFail($designation->id);
                    $child->parent_id = $parent->parent_id;
                    $child->save();
                }
            }
        }

        Designation::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    public function hierarchyData()
    {
        $viewPermission = user()->permission('view_designation');
        abort_403($viewPermission != 'all');

        $this->pageTitle = 'Designation Hierarchy';
        $this->chartDesignations = Designation::get(['id','name','parent_id']);
        $this->designations = Designation::with('childs')->where('parent_id', null)->get();

        if(request()->ajax())
        {
            return Reply::dataOnly(['status' => 'success', 'designations' => $this->designations]);
        }

        return view('designations-hierarchy.index', $this->data);
    }

    public function changeParent()
    {
        $editPermission = user()->permission('edit_designation');
        abort_403($editPermission != 'all');

        $child_ids = request('values');
        $parent_id = request('newParent') ? request('newParent') : request('parent_id');

        $designation = Designation::findOrFail($parent_id);
        // Root node again
        if(request('newParent') && $designation)
        {
            $designation->parent_id = null;
            $designation->save();
        }
        else if ($designation && $child_ids != '') // update child Node
        {
            foreach ($child_ids as $child_id)
            {
                $child = Designation::findOrFail($child_id);

                if ($child)
                {
                    $child->parent_id = $parent_id;
                    $child->save();
                }

            }
        }

        $this->chartDesignations = Designation::get(['id','name','parent_id']);
        $this->designations = Designation::with('childs')->where('parent_id', null)->get();

        $html = view('designations-hierarchy.chart_tree', $this->data)->render();
        $organizational = view('designations-hierarchy.chart_organization', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html,'organizational' => $organizational]);

    }

    public function searchFilter()
    {
        $text = request('searchText');

        if($text != '' && strlen($text) > 2)
        {
            $searchParent = Designation::with('childs')->where('name', 'like', '%' . $text . '%')->get();

            $id = [];

            foreach($searchParent as $item)
            {
                array_push($id, $item->parent_id);
            }

            $item = $searchParent->whereIn('id', $id)->pluck('id');
            $this->chartDepartments = $searchParent;

            if($text != '' && !is_null($item)){
                foreach($this->chartDepartments as $item){
                    $item['parent_id'] = null;
                }
            }

            $parent = array();

            foreach($this->chartDepartments as $designation)
            {
                array_push($parent, $designation->id);

                if ($designation->childs)
                {
                    $this->child($designation->childs);
                }
            }

            $this->children = Designation::whereIn('id', $this->arr)->get(['id','name','parent_id']);
            $this->parents = Designation::whereIn('id', $parent)->get(['id','name']);
            $this->chartDesignations = $this->parents->merge($this->children);

            $this->designations = Designation::with('childs')
                ->where('name', 'like', '%' . $text . '%')
                ->get();
        }
        else
        {
            $this->chartDesignations = Designation::get(['id','name','parent_id']);
            $this->designations = Designation::with('childs')->where('parent_id', null)->get();
        }

        $html = view('designations-hierarchy.chart_tree', $this->data)->render();
        $organizational = view('designations-hierarchy.chart_organization', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html,'organizational' => $organizational]);

    }

    public function child($child)
    {
        foreach($child as $item)
        {
            array_push($this->arr, $item->id);

            if ($item->childs)
            {
                $this->child($item->childs);
            }
        }


    }

}
