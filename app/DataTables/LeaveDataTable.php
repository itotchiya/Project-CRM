<?php

namespace App\DataTables;

use App\Models\EmployeeDetails;
use App\Models\Leave;
use App\Models\LeaveSetting;
use App\Models\User;
use Carbon\Carbon;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class LeaveDataTable extends BaseDataTable
{

    private $editLeavePermission;
    private $deleteLeavePermission;
    private $deleteApproveLeavePermission;
    private $viewLeavePermission;
    private $approveRejectPermission;
    private $reportingPermission;
    private $reportingTo;

    public function __construct()
    {
        parent::__construct();
        $this->editLeavePermission = user()->permission('edit_leave');
        $this->deleteLeavePermission = user()->permission('delete_leave');
        $this->deleteApproveLeavePermission = user()->permission('delete_approve_leaves');
        $this->viewLeavePermission = user()->permission('view_leave');
        $this->approveRejectPermission = user()->permission('approve_or_reject_leaves');
        $this->reportingPermission = LeaveSetting::value('manager_permission');
        $this->reportingTo = EmployeeDetails::where('reporting_to', user()->id)->get();
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
            ->addIndexColumn()

            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="select-table-row" data-unique-id="'.$row->unique_id.'" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('employee_name', function ($row) {
                return $row->user->name;
            })
            ->editColumn('employee', function ($row) {
                return view('components.employee', [
                    'user' => $row->user
                ]);
            })
            ->addColumn('leave_paid', function ($row) {
                if ($row->duration == 'single' || $row->duration == 'half day') {
                    if ($row->paid == 1) {
                        $leavePaidStatus = '<span class="badge badge-success">' . __('app.paid') . '</span>';

                    } else {
                        $leavePaidStatus = '<span class="badge badge-danger">' . __('app.unpaid') . '</span>';

                    }

                    if ($row->over_utilized == 1) {
                        $leavePaidStatus .= '<br>(' .__('modules.leaves.overUtilized').')';
                    }
                } else if($row->duration == 'multiple' && !is_null($row->unique_id)){

                    $leavePaidStatus = '<h5 class="mb-0 f-13 ">
                            <a class="view-related-leave text-darkest-grey ml-2" data-leave-id=' . $row->id . '
                                    data-unique-id="' . $row->unique_id . '" data-leave-type-id="' . $row->leave_type_id . '" href="javascript:;">
                                    ' . __('app.view') . ' '.__('app.status').'
                                    </a></h5>';

                }

                return $leavePaidStatus;
            })
            ->addColumn('leave_date', function ($row) {
                return Carbon::parse($row->leave_date)->translatedFormat($this->company->date_format) .' ('.Carbon::parse($row->leave_date)->translatedFormat('l').')';
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 'approved') {
                    $class = 'text-light-green';
                    $status = __('app.approved');
                }
                else if ($row->status == 'pending') {
                    $class = 'text-yellow';
                    $status = __('app.pending');
                }
                else {
                    $class = 'text-red';
                    $status = __('app.rejected');
                }

                $leaveStatus = ' <div class="media-bod1y">';

                if($row->duration == 'multiple' && !is_null($row->unique_id)){

                    $leaveStatus = '<h5 class="mb-0 f-13 ">
                            <a class="view-related-leave text-darkest-grey ml-2" data-leave-id=' . $row->id . '
                                    data-unique-id="' . $row->unique_id . '" data-leave-type-id="' . $row->leave_type_id . '" href="javascript:;">
                                    ' . __('app.view') . ' '.__('app.status').'
                                    </a></h5>';

                }
                else{
                    $leaveStatus = '<i class="fa fa-circle mr-1 ' . $class . ' f-10"></i> ' . $status;

                    if($row->manager_status_permission === 'pre-approve'){
                        $leaveStatus .= '<div><span class="badge badge-success">'.__('modules.leaves.preApproved').'</span></div>';
                    }

                }

                $leaveStatus .= ' </div>';
                return $leaveStatus;
            })
            ->addColumn('duration', function ($row) {
                $leave = ' <div class="media-body">
                    <span class="mb-0 f-13 "> '.(($row->duration == 'half day') ? __('modules.leaves.halfDay') : (($row->duration == 'multiple') ? __('modules.leaves.multiple') : __('app.'.$row->duration))) .' </span></br>';

                if($row->count_multiple_leaves != 0){
                    $leave .= '<span class="badge badge-secondary">' . $row->count_multiple_leaves .' '.__('app.leave').'</span>';
                }

                $leave .= ' </div>';

                return $leave;
            })
            ->addColumn('leave_type', function ($row) {
                $type = '<span class="badge badge-success" style="background-color:' . $row->color . '">' . $row->type_name . '</span>';

                if ($row->duration == 'half day') {
                    if (!is_null($row->half_day_type)) {
                        $type .= ' <div class="badge-inverse badge">' . (($row->half_day_type == 'first_half') ? __('modules.leaves.firstHalf') : __('modules.leaves.secondHalf')) . '</div>';

                    }
                    else {
                        $type .= ' <div class="badge-inverse badge">' . __('modules.leaves.halfDay') . '</div>';
                    }
                }

                return $type;
            })
            ->addColumn('action', function ($row) {

                $actions = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-41" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-41" tabindex="0" x-placement="bottom-end" style="position: absolute; transform: translate3d(-137px, 26px, 0px); top: 0px; left: 0px; will-change: transform;">';

                if ($row->duration == 'multiple' && !is_null($row->unique_id)) {
                    $actions .= '<a href="' . route('leaves.show', [$row->unique_id]) .'" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';
                }
                else {
                    $actions .= '<a href="' . route('leaves.show', [$row->id]).'?type=single" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';
                }

                $rejectActionAdded = false;
                $approvedActionAdded = false;
                // First Condition (Approve/Reject for all with certain permission)
                if ($row->status == 'pending' && ($row->duration != 'multiple' || is_null($row->unique_id)) && $this->approveRejectPermission == 'all') {
                    // Add reject action if not already added
                    if (!$approvedActionAdded) {
                        $actions .= '<a class="dropdown-item leave-action-approved" data-leave-id=' . $row->id . '
                             data-leave-action="approved" data-user-id="' . $row->user_id . '" data-leave-type-id="' . $row->leave_type_id . '" href="javascript:;">
                                <i class="fa fa-check mr-2"></i>' . __('app.approve') . '</a>';
                        $approvedActionAdded = true;  // Set flag to true
                    }

                    // Add reject action if not already added
                    if (!$rejectActionAdded) {
                        $actions .= '<a data-leave-id=' . $row->id . '
                              data-leave-action="rejected" data-user-id="' . $row->user_id . '" data-leave-type-id="' . $row->leave_type_id . '" class="dropdown-item leave-action-reject" href="javascript:;">
                                <i class="fa fa-times mr-2"></i>' . __('app.reject') . '</a>';
                        $rejectActionAdded = true;  // Set flag to true
                    }
                }

                if (($row->duration == 'multiple' && !is_null($row->unique_id)) && $this->approveRejectPermission == 'all') {
                    $actions .= '<a class="dropdown-item view-related-leave" data-leave-id=' . $row->id . '
                             data-unique-id="' . $row->unique_id . '" data-leave-type-id="' . $row->leave_type_id . '" href="javascript:;">
                                <i class="fa fa-eye mr-2"></i>
                                ' . __('app.view') . ' '.__('modules.leaves.relatedLeave').'
                        </a>';
                }

                // Second Condition (Manager specific, check for reporting permission)
                if ($row->status == 'pending' && $this->reportingTo && $row->user_id != user()->id && !in_array('admin', user_roles())) {

                    // Only add reject action if not already added
                    if (!$rejectActionAdded && $row->manager_status_permission == '' && !($this->reportingPermission == 'cannot-approve')) {
                        $actions .= '<a data-leave-id=' . $row->id . '
                                 data-leave-action="rejected" data-user-id="' . $row->user_id . '" data-leave-type-id="' . $row->leave_type_id . '" class="dropdown-item leave-action-reject" href="javascript:;">
                                    <i class="fa fa-times mr-2"></i>
                                    ' . __('app.reject') . '
                            </a>';
                        $rejectActionAdded = true;  // Set flag to true
                    }

                    if (!$approvedActionAdded && $this->reportingPermission == 'approved' && $row->manager_status_permission == '')
                    {
                        $approveAll = $row->duration == 'multiple' ? 'approveAll' : 'single';
                        $leaveID = $row->duration == 'multiple' ? $row->unique_id : $row->id;
                        $actions .= '<a class="dropdown-item leave-action-approved" data-leave-id=' . $leaveID . '
                                 data-leave-action="approved" data-type="'. $approveAll .'" data-user-id="' . $row->user_id . '" data-leave-type-id="' . $row->leave_type_id . '" href="javascript:;">
                                    <i class="fa fa-check mr-2"></i>
                                    ' . __('app.approve') . '
                            </a>';
                        $approvedActionAdded = true;  // Set flag to true
                    }
                    elseif ($this->reportingPermission == 'pre-approve' && !$row->manager_status_permission) {
                        $actions .= '<a data-leave-id=' . $row->id . '
                             data-leave-uid=' . $row->unique_id .'
                             data-leave-action="pre-approve" data-user-id="' . $row->user_id . '" data-leave-type-id="' . $row->leave_type_id . '" class="dropdown-item leave-action-preapprove" href="javascript:;">
                               <i class="fa fa-check mr-2"></i>
                                ' . __('app.preApprove') . '
                        </a>';
                    }
                }

                if ($row->status == 'pending' && ($row->duration != 'multiple' || is_null($row->unique_id))) {
                    if ($this->editLeavePermission == 'all'
                        || ($this->editLeavePermission == 'added' && user()->id == $row->added_by)
                        || ($this->editLeavePermission == 'owned' && user()->id == $row->user_id)
                        || ($this->editLeavePermission == 'both' && (user()->id == $row->user_id || user()->id == $row->added_by))
                    ) {
                        $actions .= '<a class="dropdown-item openRightModal" href="' . route('leaves.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . __('app.edit') . '
                        </a>';
                    }
                }

                if ($this->deleteLeavePermission == 'all'
                    || ($this->deleteLeavePermission == 'added' && user()->id == $row->added_by)
                    || ($this->deleteLeavePermission == 'owned' && user()->id == $row->user_id)
                    || ($this->deleteLeavePermission == 'both' && (user()->id == $row->user_id || user()->id == $row->added_by)))
                    {
                    if($row->status != 'approved'){
                        $actions .= '<a data-leave-id=' . $row->id . ' data-unique-id="'.$row->unique_id.'"
                                data-duration="'.$row->duration.'" class="dropdown-item delete-table-row" href="javascript:;">
                                   <i class="fa fa-trash mr-2"></i>
                                    ' . __('app.delete') . '
                            </a>';
                    }
                    else
                    {
                        ($this->deleteApproveLeavePermission == 'all') ? $actions .= '<a data-leave-id=' . $row->id . '
                                    data-unique-id="'.$row->unique_id.'" data-duration="'.$row->duration.'" class="dropdown-item delete-table-row" href="javascript:;">
                                   <i class="fa fa-trash mr-2"></i>
                                    ' . __('app.delete') . '
                            </a>' : '';
                    }
                }

                $actions .= '</div> </div> </div>';

                return $actions;
            })
            ->orderColumn('leave_paid', function ($query, $row) {
                $query->orderBy('paid', $row->paid);
            })
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['status', 'leave_type', 'action', 'check', 'employee', 'duration', 'leave_paid']);
    }

    /**
     * @param Leave $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Leave $model)
    {

        // Will check count leave from the start of the year or nor
        $setting = company();

        $leavesList = $model->with('user', 'user.employeeDetail', 'user.employeeDetail.designation', 'user.session', 'type')
            ->join('leave_types', 'leave_types.id', 'leaves.leave_type_id')
            ->join('users', 'leaves.user_id', 'users.id')
            ->join('employee_details', 'employee_details.user_id', 'users.id')
            ->selectRaw('leaves.*, leave_types.color, leave_types.type_name, ( select count("lvs.id") from leaves as lvs where lvs.unique_id = leaves.unique_id and lvs.duration = \'multiple\') as count_multiple_leaves',
            )
            ->groupByRaw('ifnull(leaves.unique_id, leaves.id)');

        if (!is_null(request()->startDate)) {
            $startDate = Carbon::createFromFormat($this->company->date_format, request()->startDate)->toDateString();

            $leavesList->whereRaw('Date(leaves.leave_date) >= ?', [$startDate]);
        }

        if (!is_null(request()->endDate)) {
            $endDate = Carbon::createFromFormat($this->company->date_format, request()->endDate)->toDateString();

            $leavesList->whereRaw('Date(leaves.leave_date) <= ?', [$endDate]);
        }

        if (request()->employeeId != 'all' && request()->employeeId != '') {
            $leavesList->where('users.id', request()->employeeId);
        }

        if (request()->leave_year != '') {

            $leavesList->whereYear('leaves.leave_date', request()->leave_year);
        }

        if (request()->leaveTypeId != 'all' && request()->leaveTypeId != '') {
            $leavesList->where('leave_types.id', request()->leaveTypeId);
        }

        if (request()->status != 'all' && request()->status != '') {
            $leavesList->where('leaves.status', request()->status);
        }

        if (request()->searchText != '') {
            $leavesList->where('users.name', 'like', '%' . request()->searchText . '%');
        }

        if ($this->viewLeavePermission == 'owned') {
            $leavesList->where(function ($q) {
                $q->orWhere('leaves.user_id', '=', user()->id);

                ($this->reportingPermission != 'cannot-approve') ? $q->orWhere('employee_details.reporting_to', user()->id) : '';
            });
        }

        if ($this->viewLeavePermission == 'added') {
            $leavesList->where(function ($q) {
                $q->orWhere('leaves.added_by', '=', user()->id);

                ($this->reportingPermission != 'cannot-approve') ? $q->orWhere('employee_details.reporting_to', user()->id) : '';
            });
        }

        if ($this->viewLeavePermission == 'both') {

            $leavesList->where(function ($q) {
                $q->orwhere('leaves.user_id', '=', user()->id);

                $q->orWhere('leaves.added_by', '=', user()->id);

                ($this->reportingPermission != 'cannot-approve') ? $q->orWhere('employee_details.reporting_to', user()->id) : '';
            });
        }

        return $leavesList;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('leaves-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["leaves-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                    $(".statusChange").selectpicker();
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
                'visible' => ($this->viewLeavePermission == 'all')
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => false],
            __('app.employee') => ['data' => 'employee', 'name' => 'user.name', 'exportable' => false, 'title' => __('app.employee')],
            __('app.employee') . ' ' => ['data' => 'employee_name', 'name' => 'user.name', 'visible' => false, 'title' => __('app.employee')],
            __('app.leaveDate') => ['data' => 'leave_date', 'name' => 'leaves.leave_date', 'title' => __('app.leaveDate')],
            __('app.duration') => ['data' => 'duration', 'name' => 'duration', 'title' => __('app.duration')],
            __('app.leaveStatus') => ['data' => 'status', 'name' => 'leaves.status', 'title' => __('app.leaveStatus')],
            __('app.leaveType') => ['data' => 'leave_type', 'name' => 'leave_types.type_name', 'title' => __('app.leaveType')],
            __('app.paid') => ['data' => 'leave_paid', 'name' => 'leaves.paid', 'title' => __('app.paid')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
