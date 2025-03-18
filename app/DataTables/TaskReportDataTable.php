<?php

namespace App\DataTables;

use App\Helper\Common;
use App\Models\Task;
use App\Models\TaskboardColumn;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;

class TaskReportDataTable extends BaseDataTable
{

    private $viewUnassignedTasksPermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewUnassignedTasksPermission = user()->permission('view_unassigned_tasks');
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
            ->editColumn('due_date', function ($row) {
                return Common::dateColor($row->due_date);
            })
            ->editColumn('users', function ($row) {
                if (count($row->users) == 0) {
                    return '--';
                }

                $members = '';

                foreach ($row->users as $member) {
                    $img = '<img data-toggle="tooltip" data-original-title="' . $member->name . '" src="' . $member->image_url . '">';

                    $members .= '<div class="taskEmployeeImg rounded-circle"><a href="' . route('employees.show', $member->id) . '">' . $img . '</a></div> ';
                }

                return $members;
            })
            ->addColumn('name', function ($row) {
                $members = [];

                foreach ($row->users as $member) {
                    $members[] = $member->name;
                }

                return implode(',', $members);
            })
            ->editColumn('clientName', function ($row) {
                return ($row->clientName) ? $row->clientName : '-';
            })
            ->addColumn('task', function ($row) {
                return $row->heading;
            })
            ->editColumn('heading', function ($row) {
                $private = $pin = $timer = '';

                if ($row->is_private) {
                    $private = '<span class="badge badge-secondary"><i class="fa fa-lock"></i> ' . __('app.private') . '</span>';
                }

                if (($row->pinned_task)) {
                    $pin = '<span class="badge badge-secondary"><i class="fa fa-thumbtack"></i> ' . __('app.pinned') . '</span>';
                }

                if (count($row->activeTimerAll) > 0) {
                    $timer .= '<span class="badge badge-secondary"><i class="fa fa-clock"></i> ' . $row->activeTimer->timer . '</span>';
                }

                return '<div class="media align-items-center">
                        <div class="media-body">
                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('tasks.show', [$row->id]) . '" class="openRightModal">' . $row->heading . '</a></h5>
                    <p class="mb-0">' . $private . ' ' . $pin . ' ' . $timer . '</p>
                    </div>
                  </div>';
            })
            ->editColumn('board_column', function ($row) {
                return '<i class="fa fa-circle mr-2" style="color: ' . $row->label_color . '"></i>' . $row->board_column;
            })
            ->addColumn('status', function ($row) {
                return $row->board_column;
            })
            ->editColumn('project_name', function ($row) {
                if (is_null($row->project_id)) {
                    return '-';
                }

                return '<a href="' . route('projects.show', $row->project_id) . '" class="text-darkest-grey">' . $row->project_name . '</a>';
            })
            ->editColumn('short_code', function ($row) {

                if (is_null($row->task_short_code)) {
                    return ' -- ';
                }

                return '<a href="' . route('tasks.show', [$row->id]) . '" class="text-darkest-grey openRightModal">' . $row->task_short_code . '</a>';
            })
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['board_column', 'project_name', 'clientName', 'due_date', 'users', 'heading', 'short_code'])
            ->removeColumn('project_id')
            ->removeColumn('image')
            ->removeColumn('created_image')
            ->removeColumn('label_color');
    }

    /**
     * @param Task $model
     * @return mixed
     */
    public function query(Task $model)
    {
        $request = $this->request();
        $startDate = null;
        $endDate = null;

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
        }

        $projectId = $request->projectId;
        $taskBoardColumn = TaskboardColumn::completeColumn();

        $model = $model->leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
            ->leftJoin('users as client', 'client.id', '=', 'projects.client_id')
            ->join('taskboard_columns', 'taskboard_columns.id', '=', 'tasks.board_column_id');

        if ($this->viewUnassignedTasksPermission == 'all' && !in_array('client', user_roles()) && ($request->assignedTo == 'unassigned' || $request->assignedTo == 'all')) {
            $model->leftJoin('task_users', 'task_users.task_id', '=', 'tasks.id')
                ->leftJoin('users as member', 'task_users.user_id', '=', 'member.id');

        }
        else {
            $model->join('task_users', 'task_users.task_id', '=', 'tasks.id')
                ->join('users as member', 'task_users.user_id', '=', 'member.id');
        }

        $model->leftJoin('users as creator_user', 'creator_user.id', '=', 'tasks.created_by')
            ->leftJoin('task_labels', 'task_labels.task_id', '=', 'tasks.id')
            ->selectRaw('tasks.id, tasks.added_by, projects.project_name, projects.client_id, tasks.heading, client.name as clientName, creator_user.name as created_by, creator_user.image as created_image, tasks.board_column_id,tasks.task_short_code,
             tasks.due_date, taskboard_columns.column_name as board_column, taskboard_columns.label_color,
              tasks.project_id, tasks.is_private ,( select count("id") from pinned where pinned.task_id = tasks.id and pinned.user_id = ' . user()->id . ') as pinned_task')
            ->addSelect('tasks.company_id') // Company_id is fetched so the we have fetch company relation with it)
            ->whereNull('projects.deleted_at')
            ->with('users', 'activeTimerAll', 'activeTimer')
            ->groupBy('tasks.id');

        if ($startDate !== null && $endDate !== null) {
            $model->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween(DB::raw('DATE(tasks.`due_date`)'), [$startDate, $endDate]);

                $q->orWhereBetween(DB::raw('DATE(tasks.`start_date`)'), [$startDate, $endDate]);
            });
        }

        if ($projectId != 0 && $projectId != null && $projectId != 'all') {
            $model->where('tasks.project_id', '=', $projectId);
        }

        if ($request->clientID != '' && $request->clientID != null && $request->clientID != 'all') {
            $model->where('projects.client_id', '=', $request->clientID);
        }

        if ($request->assignedTo != '' && $request->assignedTo != null) {
            $model->where(function ($q) use ($request) {
                if ($request->assignedTo != 'all' && $request->assignedTo != 'unassigned') {
                    $q->where('task_users.user_id', '=', $request->assignedTo);
                }

                if ($request->assignedTo == 'unassigned' || $request->assignedTo == 'all') {
                    $q->whereDoesntHave('users');
                    $q->orWhereHas('users');
                }
            });
        }

        if ($request->assignedBY != '' && $request->assignedBY != null && $request->assignedBY != 'all') {
            $model->where('creator_user.id', '=', $request->assignedBY);
        }

        if ($request->status != '' && $request->status != null && $request->status != 'all') {
            if ($request->status == 'not finished') {
                $model->where('tasks.board_column_id', '<>', $taskBoardColumn->id);
            }
            else {
                $model->where('tasks.board_column_id', '=', $request->status);
            }
        }

        if ($request->label != '' && $request->label != null && $request->label != 'all') {
            $model->where('task_labels.label_id', '=', $request->label);
        }

        if ($request->category_id != '' && $request->category_id != null && $request->category_id != 'all') {
            $model->where('tasks.task_category_id', '=', $request->category_id);
        }

        if ($request->billable != '' && $request->billable != null && $request->billable != 'all') {
            $model->where('tasks.billable', '=', $request->billable);
        }

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('tasks.heading', 'like', '%' . request('searchText') . '%')
                    ->orWhere('member.name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('projects.project_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('tasks.task_short_code', 'like', '%' . request('searchText') . '%');
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
        $dataTable = $this->setBuilder('allTasks-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["allTasks-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("#allTasks-table .select-picker").selectpicker();
                }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        }

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
            __('modules.taskCode') => ['data' => 'short_code', 'name' => 'task_short_code', 'title' => __('modules.taskCode')],
            __('app.task') => ['data' => 'heading', 'name' => 'heading', 'exportable' => false, 'title' => __('app.task')],
            __('app.menu.tasks') => ['data' => 'task', 'name' => 'heading', 'visible' => false, 'title' => __('app.menu.tasks')],
            __('app.project') => ['data' => 'project_name', 'name' => 'projects.project_name', 'title' => __('app.project')],
            __('app.client') => ['data' => 'clientName', 'name' => 'client.name', 'title' => __('app.client')],
            __('modules.tasks.assigned') => ['data' => 'name', 'name' => 'name', 'visible' => false, 'title' => __('modules.tasks.assigned')],
            __('app.dueDate') => ['data' => 'due_date', 'name' => 'due_date', 'title' => __('app.dueDate')],
            __('modules.tasks.assignTo') => ['data' => 'users', 'name' => 'member.name', 'exportable' => false, 'title' => __('modules.tasks.assignTo')],
            __('app.task') . ' ' . __('app.status') => ['data' => 'status', 'name' => 'board_column', 'visible' => false, 'title' => __('app.task')],
            __('app.columnStatus') => ['data' => 'board_column', 'name' => 'board_column', 'exportable' => false, 'searchable' => false, 'title' => __('app.columnStatus')]
        ];
    }

}
