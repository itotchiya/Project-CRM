<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Leave;
use App\Models\Holiday;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class AttendanceByMemberExport implements FromCollection, WithHeadings, WithMapping
{

    use Exportable;

    public static $sum;
    public $year;
    public $month;
    public $id;
    public $employeeName;
    public $startDate;
    public $userId;
    public $startdate;
    public $empname;
    public $endaDate;
    public $date;
    public $enddate;

    public function __construct($year, $month, $id, $employeeName, $startDate, $endaDate)
    {
        $this->year = $year;
        $this->month = $month;
        $this->userId = $id;
        $this->empname = $employeeName;
        $this->startdate = $startDate;
        $this->enddate = $endaDate;
        $this->date = $this->enddate->lessThan(now()) ? $this->enddate : now();
    }

    public function headings(): array
    {
        return [
            [__('app.attendanceof') . $this->empname . '_' . __('app.from') . '_' . $this->startdate->format('d-m-Y') . '_' . __('app.to') . '_' . $this->date->format('d-m-Y'), __('modules.attendance.clock_in'), __('modules.attendance.clock_out'), __('app.status'), __('app.location'), __('app.total')]
        ];
    }

    public function collection()
    {

        $startDate = $this->startdate;
        $endDate = $this->enddate;
        $userId = $this->userId;

        $attendances = Attendance::
        leftJoin('company_addresses', 'company_addresses.id', '=', 'attendances.location_id')
            ->where('attendances.user_id', '=', $userId)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '>=', $startDate->format('Y-m-d'))
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '<=', $endDate->format('Y-m-d'))
            ->orderBy('attendances.clock_in_time', 'asc')
            ->select('attendances.clock_in_time as date', 'attendances.clock_in_time', 'attendances.clock_out_time', 'attendances.late', 'attendances.half_day', 'company_addresses.location', 'attendances.auto_clock_out', 'attendances.half_day_type')->get();

        $leavesDates = Leave::where('user_id', $userId)
            ->where('leave_date', '>=', $startDate)
            ->where('leave_date', '<=', $endDate)
            ->where('status', 'approved')
            ->select('leave_date', 'reason', 'duration')->get();

        $period = CarbonPeriod::create($startDate, $endDate); // Get All Dates from start to end date
        $holidays = Holiday::getHolidayByDates($startDate, $endDate, $userId); // Getting Holiday Data

        $attendances = collect($attendances)->each(function ($item) {
            $item->status = '';
            $item->occassion = '';
        });

        // Add New Collection if period date does not match with attendance collection...
        $employeedata = array();
        $emp_attendance = 0;
        $status = __('app.present');

        foreach ($period->toArray() as $date) {

            $att = new Attendance();
            $att->date = $date;
            $att->clock_in_time = null;
            $att->clock_out_time = null;
            $att->late = null;
            $att->half_day = null;
            $att->half_day_type = null;

            if ($date->lessThan(now()) && !$attendances->whereBetween('date', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])->count()) {
                // If date is not in attendance..
                $att->status = 'Absent';
                // If date is not in attendance..
                // Check employee leaves
                foreach ($leavesDates as $leave) {

                    if ($date->equalTo($leave->leave_date)) {
                        $att->status = 'Leave';
                    }
                }

                // Check holidays
                foreach ($holidays as $holiday) {

                    if (Carbon::createFromFormat('Y-m-d', $holiday->holiday_date)->startOfDay()->equalTo($date)) {
                        $att->status = 'Holiday';
                        $att->occassion = $holiday->occassion;
                    }
                }

                $attendances->push($att);
            }
            else if ($date->lessThan(now())) {
                // Else date present in attendance then check for holiday and leave
                // Check employee leaves
                foreach ($leavesDates as $leave) {

                    if ($date->equalTo($leave->leave_date)) {
                        $att->status = 'Leave';
                        $attendances->push($att);
                    }

                }

                // Check holidays
                foreach ($holidays as $holiday) {

                    if ($date->format('Y-m-d') == $holiday->holiday_date && !$attendances->whereBetween('date', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])->count()) {
                        $att->status = 'Holiday';
                        $att->occassion = $holiday->occassion;
                        $attendances->push($att);
                    }
                    else if ($date->format('Y-m-d') == $holiday->holiday_date && $attendances->whereBetween('date', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])->count()) {
                        // Here just modify the collection property not creating new
                        $this->checkHolidays($attendances, $date, $holiday->occassion);
                    }

                }

            }
        }

        $employee_temp = array();

        foreach ($attendances->sortBy('date') as $attendance) {

            $date = Carbon::createFromFormat('Y-m-d H:i:s', $attendance->date)->timezone(company()->timezone)->format(company()->date_format);
            $to = $attendance->clock_out_time ? Carbon::parse($attendance->clock_out_time) : null;
            $from = $attendance->clock_in_time ? Carbon::parse($attendance->clock_in_time) : null;
            $clock_in = $attendance->clock_in_time ? Carbon::createFromFormat('Y-m-d H:i:s', $attendance->clock_in_time)->timezone(company()->timezone)->format(company()->time_format) : 0;
            $clock_out = $attendance->clock_out_time ? Carbon::createFromFormat('Y-m-d H:i:s', $attendance->clock_out_time)->timezone(company()->timezone)->format(company()->time_format) : 0;
            $diff_time = ($to && $from) ? $to->diffInMinutes($from) : 0;
            $location = $attendance->location;

            if($clock_out != 0 && $attendance->auto_clock_out == 1) {
                $clock_out .= ' ' . __('Modules.attendance.autoClockOut');
            }

            $status = __('app.present');

            if ($attendance->status != null) {

                if ($attendance->status == 'Absent') {
                    $status = __('app.absent');
                }
                else if ($attendance->status == 'Leave') {
                    $status = __('app.onLeave');
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

            if ($diff_time > 0 || $clock_out != 0) {
                if ($employee_temp && $employee_temp[1] == $date) {
                    $employeedata[$employee_temp[0] - 1]['comments']['clock_in'] .= 'Clock In : ' . $clock_in . ' Clock Out : ' . $clock_out;
                    $employeedata[$employee_temp[0] - 1]['total_hours'] = $employeedata[$employee_temp[0] - 1]['total_hours'] + $diff_time;
                }
                else {
                    $employeedata[$emp_attendance] = [
                        'date' => $date,
                        'location' => $location,
                        'total_hours' => $diff_time,
                        'comments' => [
                            'status' => $status,
                            'clockIn' => $clock_in,
                            'clockOut' => $clock_out,
                        ],
                    ];

                    $emp_attendance++;
                }
            }
            else {
                $employeedata[$emp_attendance] = [
                    'date' => $date,
                    'total_hours' => $diff_time,
                    'location' => $location,
                    'comments' => [
                        'status' => $status,
                        'clockIn' => $clock_in,
                        'clockOut' => $clock_out,
                    ],
                ];
                $emp_attendance++;
            }
        }


        $employeedata = collect($employeedata);

        self::$sum = $employeedata;

        return $employeedata;

    }

    public function map($employeedata): array
    {
        $diff = $employeedata['total_hours'];

        if (is_int($diff)) {
            $diff = CarbonInterval::formatHuman($employeedata['total_hours']);
            /** @phpstan-ignore-line */
        }

        $view_status = ($diff > 0) ? $diff : $employeedata['comments']['status'];

        return [
            $employeedata['date'],
            $employeedata['comments']['clockIn'],
            $employeedata['comments']['clockOut'],
            $employeedata['comments']['status'],
            $employeedata['location'],
            $view_status,
        ];
    }

    public function checkHolidays($attendances, $date, $occassion)
    {
        foreach ($attendances as $attendance) {
            if ($date->format('Y-m-d') == Carbon::parse($attendance->clock_in_time)->format('Y-m-d')) {
                $attendance->status = 'Holiday';
                $attendance->occassion = $occassion;
            }
        }
    }

}
