<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\KnowledgeBase;
use App\Models\KnowledgeBaseCategory;
use App\Http\Requests\KnowledgeBase\KnowledgeBaseStore;

class KnowledgeBaseController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.knowledgebase';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('knowledgebase', $this->user->modules));

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
        $viewPermission = user()->permission('view_knowledgebase');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $this->categories = KnowledgeBaseCategory::with('knowledgebase')->get();
        $this->knowledgebases = KnowledgeBase::with('knowledgebasecategory');

        if (!in_array('admin', user_roles())) {
            $this->knowledgebases = $this->knowledgebases->where('to', in_array('client', user_roles()) ? 'client' : 'employee');

            // Show only those records which has  knowledgebase means do not show categories that
            // Does not belong to them
            $this->categories = KnowledgeBaseCategory::with('knowledgebase')->whereHas('knowledgebase')->get();
        }

        if (request()->id != '') {
            $category = KnowledgeBaseCategory::findOrFail(request('id'));
            $this->activeMenu = str_replace(' ', '_', $category->name);
            $this->knowledgebases = $this->knowledgebases->where('category_id', request('id'));

        }
        else {
            $this->activeMenu = 'all_category';
        }

        $this->knowledgebases = $this->knowledgebases->get();

        return view('knowledge-base.index', $this->data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id = null)
    {
        $this->addPermission = user()->permission('add_knowledgebase');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->pageTitle = __('modules.knowledgeBase.addknowledgebase');
        $knowledgeBase = new KnowledgeBase();
        $this->knowledgeBase = $knowledgeBase->appends;
        $this->categories = KnowledgeBaseCategory::all();
        $this->selected_category_id = $id;

        if (request('category') != '') {
            $this->selected_category_id = request('category');
        }

        if (request()->ajax()) {
            $html = view('knowledge-base.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'categories' => $this->categories, 'selected_category_id' => $id, 'title' => $this->pageTitle]);
        }

        $this->view = 'knowledge-base.ajax.create';

        return view('knowledge-base.create', $this->data);
    }

    public function store(KnowledgeBaseStore $request)
    {
        $this->addPermission = user()->permission('add_knowledgebase');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $knowledgeBase = new KnowledgeBase();

        $knowledgeBase->to = $request->to;
        $knowledgeBase->heading = $request->heading;
        $knowledgeBase->category_id = $request->category;
        $knowledgeBase->description = trim_editor($request->description);
        $knowledgeBase->added_by = user()->id;
        $knowledgeBase->save();

        $redirectUrl = route('knowledgebase.index') . '?id=' . $knowledgeBase->category_id;

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl, 'knowledgeBaseId' => $knowledgeBase->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->viewPermission = user()->permission('view_knowledgebase');
        abort_403(!in_array($this->viewPermission, ['all', 'added']));

        $this->knowledge = KnowledgeBase::findOrFail($id);

        $this->pageTitle = __('modules.knowledgeBase.knowledgebase');

        $this->view = 'knowledge-base.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('knowledge-base.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_knowledgebase');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->knowledge = KnowledgeBase::findOrFail($id);
        $this->categories = KnowledgeBaseCategory::all();

        $this->pageTitle = __('modules.knowledgeBase.updateknowledge');

        $this->view = 'knowledge-base.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('knowledge-base.create', $this->data);
    }

    public function update(KnowledgeBaseStore $request, $id)
    {
        $this->editPermission = user()->permission('edit_knowledgebase');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $knowledge = KnowledgeBase::findOrFail($id);
        $knowledge->heading = $request->heading;
        $knowledge->description = trim_editor($request->description);
        $knowledge->to = $request->to;
        $knowledge->category_id = $request->category;
        $knowledge->added_by = user()->id;
        $knowledge->save();

        $redirectUrl = route('knowledgebase.index') . '?id=' . $knowledge->category_id;

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
        $this->deletePermission = user()->permission('delete_knowledgebase');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        KnowledgeBase::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('knowledgebase.index')]);

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
        $this->deletePermission = user()->permission('delete_knowledgebase');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        KnowledgeBase::whereIn('id', explode(',', $request->row_ids))->forceDelete();
    }

    public function searchQuery($srch_query = '')
    {
        $model = KnowledgeBase::query();

        if ($srch_query != '') {
            $model->where('heading', 'LIKE', '%' . $srch_query . '%');
        }

        if (in_array('employee', user_roles()) && !in_array('admin', user_roles())) {
            $model->where('to', 'employee');
        }

        if (in_array('client', user_roles()) && !in_array('admin', user_roles())) {
            $model->where('to', 'client');
        }

        if (user()->permission('view_knowledgebase') == 'added' && !in_array('admin', user_roles())) {
            $model->where('added_by', user()->id);
        }

        if (request('categoryId') != '') {
            $model->where('category_id', request('categoryId'));
        }

        $this->knowledgebases = $model->with('knowledgebasecategory')->get();
        $this->editKnowledgebasePermission = user()->permission('edit_knowledgebase');
        $this->deleteKnowledgebasePermission = user()->permission('delete_knowledgebase');

        $html = view('knowledge-base.ajax.knowledgedata', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html]);

    }

}
