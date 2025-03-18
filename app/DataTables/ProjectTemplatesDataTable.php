<?php

namespace App\DataTables;

use App\Models\ProjectTemplate;
use Yajra\DataTables\Html\Column;

class ProjectTemplatesDataTable extends BaseDataTable
{

    private $editProjectsPermission;
    private $deleteProjectPermission;
    private $viewProjectPermission;
    private $addProjectPermission;
    private $manageProjectTemplatePermission;
    private $viewProjectTemplatePermission;

    public function __construct()
    {
        parent::__construct();
        $this->editProjectsPermission = user()->permission('edit_projects');
        $this->deleteProjectPermission = user()->permission('delete_projects');
        $this->viewProjectPermission = user()->permission('view_projects');
        $this->addProjectPermission = user()->permission('add_projects');
        $this->manageProjectTemplatePermission = user()->permission('manage_project_template');
        $this->viewProjectTemplatePermission = user()->permission('view_project_template');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->addColumn('action', function ($row) {

                    $action = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                    $action .= ' <a href="' . route('project-template.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->addProjectPermission == 'all' || $this->addProjectPermission == 'added') {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('projects.create') . '?template=' . $row->id . '">
                        <i class="fa fa-plus mr-2"></i>
                        ' . trans('app.create') . ' ' . trans('app.project') . '
                    </a>';
                }

                if ($this->manageProjectTemplatePermission == 'all' || ($this->manageProjectTemplatePermission == 'added')) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('project-template.edit', [$row->id]) . '">
                            <i class="fa fa-edit mr-2"></i>
                            ' . trans('app.edit') . '
                        </a>';
                }

                if ($this->manageProjectTemplatePermission == 'all' || ($this->manageProjectTemplatePermission == 'added')) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-user-id="' . $row->id . '">
                            <i class="fa fa-trash mr-2"></i>
                            ' . trans('app.delete') . '
                        </a>';
                }

                $action .= '</div>
                </div>
            </div>';

                return $action;
            })
            ->addColumn('members', function ($row) {
                $members = '';

                if (count($row->members) > 0) {
                    foreach ($row->members as $member) {
                        $img = '<img data-toggle="tooltip" data-original-title="' . $member->user->name . '" src="' . $member->user->image_url . '">';

                        $members .= '<div class="taskEmployeeImg rounded-circle"><a href="' . route('employees.show', $member->user->id) . '">' . $img . '</a></div> ';
                    }
                }
                else {
                    $members .= __('messages.noMemberAddedToProject');
                }

                return $members;
            })
            ->editColumn('project_name', function ($row) {
                return '<div class="media align-items-center">
                            <div class="media-body">
                                <h5 class="mb-0 f-13 text-darkest-grey">
                                    <a href="' . route('project-template.show', [$row->id]) . '">' . $row->project_name . '</a>
                                </h5>
                            </div>
                        </div>';
            })
            ->editColumn('category_id', function ($row) {
                return ($row->category) ? $row->category->category_name : '-';
            })
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['project_name', 'action', 'members', 'category_id', 'check']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ProjectTemplate $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ProjectTemplate $model)
    {
        $request = $this->request();

        $model = $model::with(['category', 'members'])
            ->select('id', 'project_name', 'category_id');

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('project_templates.project_name', 'like', '%' . request('searchText') . '%');
            });
        }

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('projects-template-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["projects-template-table"].buttons().container()
                     .appendTo( "#table-actions")
                 }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
            ]);

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
                'visible' => !in_array('client', user_roles())
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('modules.projects.projectName') => ['data' => 'project_name', 'name' => 'project_name', 'title' => __('modules.projects.projectName')],
            __('modules.projects.members') => ['data' => 'members', 'name' => 'members', 'exportable' => false, 'width' => '25%', 'title' => __('modules.projects.members')],
            __('modules.projects.projectCategory') => ['data' => 'category_id', 'name' => 'category_id', 'title' => __('modules.projects.projectCategory')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
                ->addClass('text-right pr-20')
        ];
    }

}
