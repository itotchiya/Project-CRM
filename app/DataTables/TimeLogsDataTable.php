<?php

namespace App\DataTables;

use Carbon\CarbonInterval;
use App\Models\CustomField;
use App\Models\ProjectTimeLog;
use App\Models\CustomFieldGroup;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\ProjectMember;

class TimeLogsDataTable extends BaseDataTable
{

    private $editTimelogPermission;
    private $deleteTimelogPermission;
    private $viewTimelogPermission;
    private $approveTimelogPermission;
    private $viewTimelogEarningsPermission;
    private $ignoreDeletedAtCondition;

    public function __construct($ignoreDeletedAtCondition = false)
    {
        parent::__construct();
        $this->editTimelogPermission = user()->permission('edit_timelogs');
        $this->deleteTimelogPermission = user()->permission('delete_timelogs');
        $this->viewTimelogPermission = user()->permission('view_timelogs');
        $this->approveTimelogPermission = user()->permission('approve_timelogs');
        $this->viewTimelogEarningsPermission = user()->permission('view_timelog_earnings');
        $this->ignoreDeletedAtCondition = $ignoreDeletedAtCondition;
    }

    /**
     * @param mixed $query
     * @return \Yajra\DataTables\DataTableAbstract|\Yajra\DataTables\EloquentDataTable
     */
    public function dataTable($query)
    {

        $datatables = datatables()->eloquent($query);
        $datatables->addIndexColumn();
        $datatables->addColumn('check', fn($row) => $this->checkBox($row));
        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a href="' . route('timelogs.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

            if (!is_null($row->end_time)) {
                if ($this->approveTimelogPermission == 'all') {
                    if (!$row->approved) {
                        $action .= '<a class="dropdown-item approve-timelog" href="javascript:;" data-time-id="' . $row->id . '">
                                <i class="fa fa-check mr-2"></i>
                                ' . trans('app.approve') . '
                            </a>';
                    }
                }

                if (
                    $this->editTimelogPermission == 'all'
                    || ($row->project_admin == user()->id)
                    || ($this->editTimelogPermission == 'added' && $row->added_by == user()->id)
                    || ($this->editTimelogPermission == 'owned'
                        && (($row->project && $row->project->client_id == user()->id) || $row->user_id == user()->id)
                    )
                    || ($this->editTimelogPermission == 'both' && (($row->project && $row->project->client_id == user()->id) || $row->user_id == user()->id || $row->added_by == user()->id))
                ) {
                    if(is_null($row->project_id) || ($row->project && is_null($row->project->deleted_at))) {
                        $action .= '<a class="dropdown-item openRightModal" href="' . route('timelogs.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                    }
                }

                if ($this->deleteTimelogPermission == 'all'
                    || ($this->deleteTimelogPermission == 'added' && user()->id == $row->added_by)
                    || ($row->project_admin == user()->id)
                ) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-time-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }
            }
            else {
                if ($this->editTimelogPermission == 'all'
                    || ($this->editTimelogPermission == 'added' && user()->id == $row->added_by)
                    || ($row->project_admin == user()->id)
                ) {
                    $action .= '<a class="dropdown-item stop-active-timer" href="javascript:;" data-time-id="' . $row->id . '" data-url="">
                                <i class="fa fa-stop-circle mr-2"></i>
                                ' . trans('app.stop') . '
                            </a>';
                }
            }

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });
        $datatables->addColumn('employee_name', fn($row) => $row->user->name);
        $datatables->editColumn('name', fn($row) => view('components.employee', ['user' => $row->user]));
        $datatables->editColumn('start_time', fn($row) => $row->start_time->timezone($this->company->timezone)->translatedFormat($this->company->date_format . ' ' . $this->company->time_format));
        $datatables->editColumn('end_time', function ($row) {
            if (!is_null($row->end_time)) {
                return $row->end_time->timezone($this->company->timezone)->translatedFormat($this->company->date_format . ' ' . $this->company->time_format);
            }

            if (!is_null($row->activeBreak)) {
                return "<span class='badge badge-secondary'><i class='fa fa-pause-circle'></i> " . __('modules.timeLogs.paused') . '</span>';
            }

            return "<span class='badge badge-primary'><i class='fa fa-clock'></i> " . __('app.active') . '</span>';
        });
        $datatables->editColumn('total_hours', function ($row) {
            // Determine total minutes based on end_time
            $totalMinutes = is_null($row->end_time)
                ? (($row->activeBreak) ? $row->activeBreak->start_time->diffInMinutes($row->start_time) : now()->diffInMinutes($row->start_time)) - $row->breaks->sum('total_minutes')
                : $row->total_minutes - $row->breaks->sum('total_minutes');

            // Convert total minutes to hours and minutes
            $hours = intdiv($totalMinutes, 60);
            $minutes = $totalMinutes % 60;

            // Format output based on hours and minutes
            $formattedTime = $hours > 0
                ? $hours . 'h' . ($minutes > 0 ? ' ' . sprintf('%02dm', $minutes) : '')
                : ($minutes > 0 ? sprintf('%dm', $minutes) : '0s');

            // Build timeLog with conditional icons
            $timeLog = '<span data-trigger="hover" data-toggle="popover" data-content="' . $row->memo . '">' . $formattedTime . '</span>';
            if (is_null($row->end_time)) {
                $timeLog .= ' <i data-toggle="tooltip" data-original-title="' . __('app.active') . '" class="fa fa-hourglass-start"></i>';
            } elseif ($row->approved) {
                $timeLog .= ' <i data-toggle="tooltip" data-original-title="' . __('app.approved') . '" class="fa fa-check-circle text-primary"></i>';
            }

            return $timeLog;
        });
        $datatables->editColumn('earnings', function ($row) {

            $memberHoursRate = ProjectMember::where('user_id', $row->user_id)->where('project_id', $row->project_id)->first();

            $totalMinutes = is_null($row->end_time)
                ? (($row->activeBreak) ? $row->activeBreak->start_time->diffInMinutes($row->start_time) : now()->diffInMinutes($row->start_time)) - $row->breaks->sum('total_minutes')
                : $row->total_minutes - $row->breaks->sum('total_minutes');

            $userData = (!empty($memberHoursRate->hourly_rate) && $memberHoursRate->hourly_rate !== 0) ? $memberHoursRate->hourly_rate : $row->user_hour_rate;
            $amount = ($userData/60) * $totalMinutes;

            return currency_format($amount, company()->currency_id);
        });

        $datatables->editColumn('project_name', function ($row) {
            $name = '';

            if (!is_null($row->project_id) && !is_null($row->task_id)) {
                $name .= '<h5 class="f-13 text-darkest-grey"><a href="' . route('tasks.show', [$row->task_id]) . '" class="openRightModal">' . $row->task->heading . '</a></h5><div class="text-muted">' . $row->task->project->project_name . '</div>';
            }
            else if (!is_null($row->project_id)) {
                $name .= '<a href="' . route('projects.show', [$row->project_id]) . '" class="text-darkest-grey ">' . $row->project->project_name . '</a>';
            }
            else if (!is_null($row->task_id)) {
                $name .= '<a href="' . route('tasks.show', [$row->task_id]) . '" class="text-darkest-grey openRightModal">' . $row->task->heading . '</a>';
            }

            return $name;
        });
        $datatables->addColumn('task_name', fn($row) => $row->task?->heading ?? '--');
        $datatables->addColumn('task_project_name', fn($row) => $row->project?->project_name ?? '--');
        $datatables->addColumn('short_code', fn($row) => $row->project?->project_short_code ?? '--');
        $datatables->addIndexColumn();
        $datatables->setRowId(fn($row) => 'row-' . $row->id);
        $datatables->orderColumn('project_name', 'tasks.heading $1');
        $datatables->removeColumn('project_id');
        $datatables->removeColumn('total_minutes');
        $datatables->removeColumn('task_id');

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatables, ProjectTimeLog::CUSTOM_FIELD_MODEL);

        $datatables->rawColumns(array_merge(['end_time', 'action', 'project_name', 'name', 'total_hours', 'check'], $customFieldColumns));

        return $datatables;
    }

    /**
     * @param ProjectTimeLog $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ProjectTimeLog $model)
    {
        $request = $this->request();

        $projectId = $request->projectId;
        $employee = $request->employee;
        $taskId = $request->taskId;
        $approved = $request->approved;
        $invoice = $request->invoice;

        $model = $model->with('user', 'user.employeeDetail', 'user.employeeDetail.designation', 'user.session', 'task', 'task.project', 'breaks', 'activeBreak', 'project');

        if (!in_array('client', user_roles()) && $request->has('project_admin') && $request->project_admin == 1) {
            $model->leftJoin('users', 'users.id', '=', 'project_time_logs.user_id')
                ->leftJoin('employee_details', 'users.id', '=', 'employee_details.user_id');
        }
        else {
            $model->join('users', 'users.id', '=', 'project_time_logs.user_id')
                ->join('employee_details', 'users.id', '=', 'employee_details.user_id');
        }

        $model = $model->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id')
            ->leftJoin('tasks', 'tasks.id', '=', 'project_time_logs.task_id')
            ->leftJoin('projects', 'projects.id', '=', 'tasks.project_id');

        $model = $model->select('project_time_logs.id', 'project_time_logs.start_time', 'project_time_logs.end_time', 'project_time_logs.total_hours', 'project_time_logs.total_minutes', 'project_time_logs.memo', 'project_time_logs.user_id', 'tasks.project_id', 'project_time_logs.task_id', 'users.name', 'users.image', 'project_time_logs.hourly_rate', 'project_time_logs.earnings', 'project_time_logs.approved', 'tasks.heading', 'projects.project_name', 'designations.name as designation_name', 'project_time_logs.added_by', 'projects.project_admin', 'employee_details.hourly_rate as user_hour_rate');


        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '' &&
        $request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {

            $startDate = Carbon::createFromFormat($this->company->date_format, $request->startDate)->startOfDay(); // Assuming $startDate is already in a specific timezone
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->endDate)->endOfDay(); // Assuming $endDate is already in a specific timezone

            $model->whereBetween(DB::raw('CONVERT_TZ(project_time_logs.`start_time`, \'+00:00\', @@session.time_zone)'), [$startDate, $endDate]);
        }

        if (!is_null($employee) && $employee !== 'all') {
            $model->where('project_time_logs.user_id', $employee);
        }

        if (!is_null($projectId) && $projectId !== 'all') {
            $model->where('tasks.project_id', '=', $projectId);
        }

        if (!is_null($taskId) && $taskId !== 'all') {
            $model->where('project_time_logs.task_id', '=', $taskId);
        }

        if (!is_null($approved) && $approved !== 'all') {
            if ($approved == 2) {
                $model->whereNull('project_time_logs.end_time');
            }
            else {
                $model->where('project_time_logs.approved', '=', $approved);
            }
        }

        if (!is_null($invoice) && $invoice !== 'all') {
            if ($invoice == 0) {
                $model->whereNull('project_time_logs.invoice_id');
            }
            else if ($invoice == 1) {
                $model->whereNotNull('project_time_logs.invoice_id');
            }
        }

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('tasks.heading', 'like', '%' . request('searchText') . '%')
                    ->orWhere('project_time_logs.memo', 'like', '%' . request('searchText') . '%')
                    ->orWhere('projects.project_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('projects.project_short_code', 'like', '%' . request('searchText') . '%')
                    ->orWhere('tasks.task_short_code', 'like', '%' . request('searchText') . '%');
            });
        };

        if (($request->has('project_admin') && $request->project_admin != 1) || !$request->has('project_admin')) {

            if ($this->viewTimelogPermission == 'added') {
                $model->where('project_time_logs.added_by', user()->id);
            }

            if ($this->viewTimelogPermission == 'owned') {
                $model->where(function ($q) {
                    $q->where('project_time_logs.user_id', '=', user()->id);

                    if (in_array('client', user_roles())) {
                        $q->orWhere('projects.client_id', '=', user()->id);
                    }
                });

                if ($projectId != 0 && $projectId != null && $projectId != 'all' && !in_array('client', user_roles())) {
                    $model->where('projects.project_admin', '<>', user()->id);
                }
            }

            if ($this->viewTimelogPermission == 'both') {
                $model->where(function ($q) {
                    $q->where('project_time_logs.user_id', '=', user()->id);

                    $q->orWhere('project_time_logs.added_by', '=', user()->id);

                    if (in_array('client', user_roles())) {
                        $q->orWhere('projects.client_id', '=', user()->id);
                    }
                });
            }
        }

        if (!$this->ignoreDeletedAtCondition) {
            $model->whereNull('tasks.deleted_at');
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
        $dataTable = $this->setBuilder('timelogs-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["timelogs-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {

                    $(".select-picker").selectpicker();
                    $("body").popover({
                        selector: \'[data-toggle="popover"]\',
                        trigger: "hover",
                        placement: "top",
                    })

                   // $(\'[data-toggle="popover"]\').popover();
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
        $data = [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => !showId()],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => showId()],
            __('modules.taskCode') => ['data' => 'short_code', 'name' => 'project_short_code', 'title' => __('modules.taskCode')],
            __('app.task') => ['data' => 'project_name', 'name' => 'tasks.heading', 'exportable' => false, 'width' => '200', 'title' => __('app.task')],
            __('app.tasks') => ['data' => 'task_name', 'visible' => false, 'name' => 'task_name', 'title' => __('app.tasks')],
            __('app.project') => ['data' => 'task_project_name', 'visible' => false, 'name' => 'task_project_name', 'title' => __('app.project')],
            __('app.employee') => ['data' => 'name', 'name' => 'users.name', 'exportable' => false, 'title' => __('app.employee')],
            __('app.name') => ['data' => 'employee_name', 'name' => 'name', 'visible' => false, 'title' => __('app.name')],
            __('modules.timeLogs.startTime') => ['data' => 'start_time', 'name' => 'start_time', 'title' => __('modules.timeLogs.startTime')],
            __('modules.timeLogs.endTime') => ['data' => 'end_time', 'name' => 'end_time', 'title' => __('modules.timeLogs.endTime')],
            __('modules.timeLogs.totalHours') => ['data' => 'total_hours', 'name' => 'total_hours', 'title' => __('modules.timeLogs.totalHours')],
            __('app.earnings') => ['data' => 'earnings', 'name' => 'earnings', 'title' => __('app.earnings'), 'visible' => ($this->viewTimelogEarningsPermission == 'all'), 'exportable' => ($this->viewTimelogEarningsPermission == 'all')]
        ];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

        return array_merge($data, CustomFieldGroup::customFieldsDataMerge(new ProjectTimeLog()), $action);
    }

}
