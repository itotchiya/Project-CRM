<?php

namespace App\DataTables;

use App\Models\ProjectTemplateTask;
use App\Models\TaskLabelList;
use Yajra\DataTables\Html\Column;

class ProjectTemplateTasksDataTable extends BaseDataTable
{

    private $deleteTaskPermission;
    private $editTaskPermission;
    private $viewTaskPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editTaskPermission = user()->permission('edit_tasks');
        $this->deleteTaskPermission = user()->permission('delete_tasks');
        $this->viewTaskPermission = user()->permission('view_tasks');
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

                $action .= ' <a href="' . route('project-template-task.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editTaskPermission == 'all' || ($this->editTaskPermission == 'added' && user()->id == $row->added_by) || ($this->editTaskPermission == 'both' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('project-template-task.edit', [$row->id]) . '?project_id='. $row->project_template_id . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($this->deleteTaskPermission == 'all' || ($this->deleteTaskPermission == 'added' && user()->id == $row->added_by) || ($this->deleteTaskPermission == 'both' && user()->id == $row->added_by)) {
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
            ->editColumn('heading', function ($row) {

                $taskLabelList = TaskLabelList::whereNull('project_id')->get();
                $taskLabelIds = explode(',', $row->task_labels);
                $labels = '';

                // Use foreach instead of forelse
                foreach ($taskLabelList->filter(function ($label) use ($taskLabelIds) {
                    return in_array($label->id, $taskLabelIds);
                }) as $key => $label) {
                    $labels .= '<span class="badge badge-secondary mr-1" style="background-color: ' . $label->label_color. '">'
                            . $label->label_name . '</span>';
                }

                return '<div class="media align-items-center">
                        <div class="media-body">
                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('project-template-task.show', [$row->id]) . '" class="openRightModal">' . $row->heading . '<br/>' . '</a></h5>
                    <p class="mb-0">' . $labels . '</p>
                    </div>
                  </div>';
            })
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'heading', 'check']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ProjectTemplateTask $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ProjectTemplateTask $model)
    {
        $request = $this->request();
        $projectId = $request->projectId;

        $model = $model->where('project_template_id', $projectId);

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('heading', 'like', '%' . request('searchText') . '%');
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
        $dataTable = $this->setBuilder('allTasks-table', 0)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["allTasks-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("#allTasks-table .select-picker").selectpicker();
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
            '#' => ['data' => 'id', 'name' => 'id', 'visible' => false],
            __('app.task') => ['data' => 'heading', 'name' => 'heading', 'title' => __('app.task')],
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
