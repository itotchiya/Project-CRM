<?php

namespace App\Exports;

use App\Models\Attendance;
use Carbon\CarbonInterval;
use App\Models\AttendanceSetting;
use App\Models\EmployeeDetails;
use App\Models\EmployeeShiftSchedule;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\CompanyAddress;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{

    /**
     * @return Collection
     */
    public static $sum;
    public $year;
    public $month;
    public $userId;
    public $viewAttendancePermission;
    public $department;
    public $designation;
    public $startdate;
    public $enddate;

    public function __construct($year, $month, $id, $department, $designation, $startdate, $enddate)
    {
        $this->viewAttendancePermission = user()->permission('view_attendance');
        $this->year = $year;
        $this->month = $month;
        $this->userId = $id;
        $this->department = $department;
        $this->designation = $designation;
        $this->startdate = $startdate;
        $this->enddate = $enddate;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => [self::class, 'afterSheet'],
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        $emp_status = self::$sum;
        $total = count($emp_status);
        $arr = array('B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        $j = 2;

        for ($index = 0; $index < $total; $index++) {
            $total_day = isset($emp_status[$index]['dates']) ? count($emp_status[$index]['dates']) : 0;

            for ($i = 1; $i <= $total_day; $i++) {
                if ($emp_status[$index]['dates'][$i]['total_hours'] > 0) {
                    $event->sheet->getDelegate()->getComment($arr[$i - 1] . $j)->getText()->createTextRun(
                        ['Status : ' . $emp_status[$index]['dates'][$i]['comments']['status'],
                            $emp_status[$index]['dates'][$i]['comments']['clock_in'],
                        ]
                    );
                }
            }

            $j++;
        }

        $event->sheet->getDelegate()->getStyle('b:ag')
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    public function headings(): array
    {
        $arr = array();
        $period = CarbonPeriod::create($this->startdate, $this->enddate); // Get All Dates from start to end date
        $arr[] = __('app.empdate');

        foreach ($period->toArray() as $date) {
            $arr[] = $date->format('d-m-Y');
        }

        return [
            $arr,
        ];
    }

    public function collection()
    {
        $startDate = $this->startdate;
        $endDate = $this->enddate;
        $id = $this->userId;

        $employees = EmployeeDetails::join('users', 'employee_details.user_id', '=', 'users.id');

        if ($id != 'all') {
            if ($this->viewAttendancePermission == 'owned') {
                $employees->where('users.id', user()->id);
            }
            else {
                $employees->where('users.id', $id);
            }
        }
        else if ($this->viewAttendancePermission == 'owned') {
            $employees->where('users.id', user()->id);
        }

        if ($this->viewAttendancePermission == 'owned') {
            $employees->where('users.id', user()->id);
        }

        if ($this->department != 'all') {
            $employees->where('employee_details.department_id', $this->department);
        }

        if ($this->designation != 'all') {
            $employees->where('employee_details.designation_id', $this->designation);
        }

        $employees = $employees->select('users.name', 'users.id')->get();
        $employeedata = array();
        $emp_attendance = 1;
        $employee_index = 0;

        foreach ($employees as $employee) {
            $userId = $employee->id;
            $employeedata[$employee_index]['employee_name'] = $employee->name;

            $attendances = Attendance::where('attendances.user_id', '=', $userId);

            $attendances = $attendances->orderBy('attendances.clock_in_time', 'asc')
                ->where(DB::raw('DATE(attendances.clock_in_time)'), '>=', $startDate->format('Y-m-d'))
                ->where(DB::raw('DATE(attendances.clock_in_time)'), '<=', $endDate->format('Y-m-d'))
                ->select('attendances.clock_in_time as date', 'attendances.clock_in_time', 'attendances.working_from', 'attendances.location_id', 'attendances.clock_out_time', 'attendances.late', 'attendances.half_day', 'attendances.auto_clock_out', 'attendances.half_day_type')->get();

            $leavesDates = Leave::where('user_id', $userId)
                ->where('leave_date', '>=', $startDate)
                ->where('leave_date', '<=', $endDate)
                ->where('status', 'approved')
                ->select('leave_date', 'reason', 'duration')->get();

            $employeeShifts = EmployeeShiftSchedule::with('shift')
                ->where('user_id', $userId)
                ->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate)
                ->get();

            $period = CarbonPeriod::create($startDate, $endDate); // Get All Dates from start to end date
            $holidays = Holiday::getHolidayByDates($startDate, $endDate, $userId); // Getting Holiday Data

            $attendances = collect($attendances)->each(function ($item) {
                $item->status = '';
                $item->occassion = '';
            });

            // Add New Collection if date does not match with attendance collection...
            foreach ($period->toArray() as $date) {
                $att = new Attendance();
                $att->date = $date->format('Y-m-d');
                $att->clock_in_time = null;
                $att->clock_out_time = null;
                $att->late = null;
                $att->half_day = null;
                $att->half_day_type = null;
                $att->working_from = null;
                $att->location_id = null;

                if ($date->lessThan(now()) && !$attendances->whereBetween('clock_in_time', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])->count()) {

                    $att->status = 'Absent';
                    // If date is not in attendance..
                    foreach ($leavesDates as $leave) { // check leaves
                        if ($date->equalTo($leave->leave_date)) {
                            $att->status = 'Leave';
                        }
                    }

                    foreach ($holidays as $holiday) { // Check holidays
                        if (\Carbon\Carbon::createFromFormat('Y-m-d', $holiday->holiday_date)->startOfDay()->equalTo($date)) {
                            $att->status = 'Holiday';
                            $att->occassion = $holiday->occassion;
                        }
                    }

                    foreach ($employeeShifts as $shift) { // Check shifts
                        if ($date->equalTo($shift->date) && $shift->shift->shift_name == 'Day Off') {
                            $att->status = $shift->shift->shift_name;
                        }
                    }

                    $attendances->push($att);

                }
                else if ($date->lessThan(now())) {
                    // else date present in attendance then check for holiday and leave
                    foreach ($leavesDates as $leave) { // check employee leaves

                        if ($date->equalTo($leave->leave_date)) {
                            $att->status = 'Leave';
                            $attendances->push($att);
                        }
                    }

                    foreach ($holidays as $holiday) { // Check holidays

                        if ($date->format('Y-m-d') == $holiday->holiday_date && !$attendances->whereBetween('clock_in_time', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])->count()) {
                            $att->status = 'Holiday';
                            $att->occassion = $holiday->occassion;
                            $attendances->push($att);
                        }
                        else if ($date->format('Y-m-d') == $holiday->holiday_date && $attendances->whereBetween('clock_in_time', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])->count()) {
                            // here modify the collection property not creating new
                            $this->checkHolidays($attendances, $date);
                        }

                    }

                    foreach ($employeeShifts as $shift) { // Check shifts
                        if ($date->equalTo($shift->date) && $shift->shift->shift_name == 'Day Off') {
                            $att->status = $shift->shift->shift_name;
                            $attendances->push($att);
                        }
                    }

                }
            }

            $employee_temp = array();
            $status = __('app.present');

            foreach ($attendances->sortBy('date') as $attendance) {

                $date = Carbon::createFromFormat('Y-m-d H:i:s', $attendance->date)->timezone(company()->timezone)->format(company()->date_format);

                $to = $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time) : null;
                $from = $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time) : null;

                if ($from && !$to) {
                    $to = $this->getDefaultClockOutTime($from, $employeeShifts->where('date', $attendance->date)->first());
                }

                $clock_in = $attendance->clock_in_time ? Carbon::createFromFormat('Y-m-d H:i:s', $attendance->clock_in_time)->timezone(company()->timezone)->format(company()->time_format) : 0;
                $clock_out = $attendance->clock_out_time ? Carbon::createFromFormat('Y-m-d H:i:s', $attendance->clock_out_time)->timezone(company()->timezone)->format(company()->time_format) : 0;

                if($clock_out != 0 && $attendance->auto_clock_out == 1) {
                    $clock_out .= ' ' . __('Modules.attendance.autoClockOut');
                }

                $diff_in_hours = ($to && $from) ? $to->diffInMinutes($from) : 0;

                if ($attendance->status != null) {

                    if ($attendance->status == 'Absent') {
                        $status = __('app.absent');
                    }
                    else if ($attendance->status == 'Leave') {
                        $status = __('app.onLeave');
                    }
                    else if ($attendance->status == 'Day Off') {
                        $status = __('modules.attendance.dayOff');
                    }
                    else if ($attendance->status == 'Holiday') {
                        $status = __('app.holiday', ['name' => $attendance->occassion]);
                    }
                }
                else if ($attendance->late == 'yes' && $attendance->half_day == 'yes') {
                    $halfDayType = '';

                    if ($attendance->half_day_type == 'first_half') {
                        $halfDayType = '('. __('modules.leaves.1stHalf') .')';
                    } elseif ($attendance->half_day_type == 'second_half') {
                        $halfDayType = '('. __('modules.leaves.2ndHalf') .')';
                    }

                    $status =  __('app.halfday') . $halfDayType . __('app.lateHalfday');
                }
                else if ($attendance->late == 'yes') {
                    $status = __('app.presentlate');
                }
                else if ($attendance->half_day == 'yes') {
                    $halfDayType = '';

                    if ($attendance->half_day_type == 'first_half') {
                        $halfDayType = '('. __('modules.leaves.1stHalf') .')';
                    } elseif ($attendance->half_day_type == 'second_half') {
                        $halfDayType = '('. __('modules.leaves.2ndHalf') .')';
                    }

                    $status = __('app.halfday') . $halfDayType;
                }
                else {
                    $status = '--';
                }

                $workFrom = $attendance->working_from ?? '--';
                $companyAddress = CompanyAddress::where('id', $attendance->location_id)->first();
                $location = '-';
                if($companyAddress){
                    $location = $companyAddress->location;
                }

                if ($employee_temp && $employee_temp[1] == $date) {
                    $employeedata[$employee_index]['dates'][$emp_attendance - 1]['comments']['clock_in'] .= ' Clock In : ' . $clock_in . ' Clock Out : ' . $clock_out . ' Work From : ' . $workFrom . ' location : ' . $location;
                    $employeedata[$employee_index]['dates'][$emp_attendance - 1]['total_hours'] = $employeedata[$employee_index]['dates'][$emp_attendance - 1]['total_hours'] + $diff_in_hours;
                }
                else {
                    $employeedata[$employee_index]['dates'][$emp_attendance] = [
                        'total_hours' => $diff_in_hours,
                        'date' => $attendance->date,
                        'comments' => [
                            'status' => $status,
                            'clock_in' => 'Clock In : ' . $clock_in . ' Clock Out : ' . $clock_out . ' Work From : ' . $workFrom . ' location : ' . $location,
                        ],
                    ];
                    $emp_attendance++;
                }

                $employee_temp = [$emp_attendance, $date];
            }

            $employee_index++;
            $emp_attendance = 1;
        }

        $employeedata = collect($employeedata);
        self::$sum = $employeedata;

        return $employeedata;
    }

    public function map($employeedata): array
    {
        $data = array();
        $data[] = $employeedata['employee_name'];
        $num = isset($employeedata['dates']) ? count($employeedata['dates']) : 0;

        for ($index = 1; $index <= $num; $index++) {

            $emp_status = $employeedata['dates'][$index]['comments']['status'];

            if (str_contains($emp_status, 'Holiday') || $employeedata['dates'][$index]['total_hours'] < 1) {
                $data[] = $employeedata['dates'][$index]['comments']['status'];
            }
            else {
                $data[] = CarbonInterval::formatHuman($employeedata['dates'][$index]['total_hours']);
            }
        }

        return $data;
    }

    public function checkHolidays($attendances, $date)
    {
        foreach ($attendances as $attendance) {
            if ($date->format('Y-m-d') == \Carbon\Carbon::parse($attendance->clock_in_time)->format('Y-m-d')) {
                $attendance->status = '';
            }
        }
    }

    private function getDefaultClockOutTime($date, $attendanceSettings)
    {

        if ($attendanceSettings) {
            $attendanceSettings = $attendanceSettings->shift;

        }
        else {
            $attendanceSettings = AttendanceSetting::first()->shift; // Do not get this from session here
        }

        $defaultClockOutTime = Carbon::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d') . ' ' . $attendanceSettings->office_end_time, $attendanceSettings->company->timezone);

        if ($defaultClockOutTime->lessThan($date)) {
            $defaultClockOutTime = $date;
        }

        return $defaultClockOutTime;
    }

}
