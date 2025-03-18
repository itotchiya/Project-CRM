<?php

namespace App\Exports;

use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ShiftScheduleExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{

    public static $sum;
    public $viewAttendancePermission;
    public $viewAtteyearndancePermission;
    public $month;
    public $userId;
    public $department;
    public $startdate;
    public $enddate;
    public $year;
    public $viewType;
    public $holidays;
    public $daysInMonth;
    public $weekStartDate;
    public $weekEndDate;

    public function __construct($year, $month, $id, $department, $startdate, $enddate, $viewType)
    {
        $this->viewAttendancePermission = user()->permission('view_shift_roster');
        $this->year = $year;
        $this->month = $month;
        $this->userId = $id;
        $this->department = $department;
        $this->startdate = $startdate;
        $this->enddate = $enddate;
        $this->viewType = $viewType;
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

        $employees = User::with(
            ['shifts' => function ($query) use ($startDate, $endDate) {
                $query->wherebetween('employee_shift_schedules.date', [$startDate->toDateString(), $endDate->toDateString()]);
            },
                'leaves' => function ($query) use ($startDate, $endDate) {
                    $query->wherebetween('leaves.leave_date', [$startDate->toDateString(), $endDate->toDateString()])
                        ->where('status', 'approved');
                }, 'shifts.shift', 'leaves.type']
        )->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'employee_details.department_id', 'users.image')
            ->onlyEmployee()->groupBy('users.id');

        if ($this->department != 'all') {
            $employees = $employees->where('employee_details.department_id', $this->department);
        }

        if ($this->userId != 'all') {
            $employees = $employees->where('users.id', $this->userId);
        }

        $employees = $employees->get();

        $this->holidays = Holiday::whereRaw('MONTH(holidays.date) = ?', [$this->month])->whereRaw('YEAR(holidays.date) = ?', [$this->year])->get();

        $final = [];
        $holidayOccasions = [];
        $shiftColorCode = [];

        if ($startDate->month == $endDate->month) {
            $this->daysInMonth = Carbon::parse('01-' . $endDate->month . '-' . $this->year)->daysInMonth;

        }
        else {
            $this->daysInMonth = Carbon::parse('01-' . $startDate->month . '-' . $this->year)->daysInMonth;
        }

        $now = now()->timezone(company()->timezone);
        $requestedDate = Carbon::parse(Carbon::parse('01-' . $this->month . '-' . $this->year))->endOfMonth();

        $employeedata = array();
        $emp_attendance = 1;
        $employee_index = 0;

        foreach ($employees as $employee) {
            $employeedata[$employee_index]['employee_name'] = $employee->name;

            if ($this->viewType != 'week') {
                $dataTillToday = array_fill(1, $now->copy()->format('d'), '--');
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), ((int)$this->daysInMonth - (int)$now->copy()->format('d')), '--');
                $employeedata[$employee_index]['dates'] = array_replace($dataTillToday, $dataFromTomorrow);
            }
            else {
                $period = CarbonPeriod::create($this->startdate, $this->enddate); // Get All Dates from start to end date
                $employeedata[$employee_index]['dates'] = [];

                foreach ($period->toArray() as $date) {
                    $employeedata[$employee_index]['dates'][$date->day] = '--';
                }

            }

            foreach ($employee->shifts as $shift) {
                $employeedata[$employee_index]['dates'][Carbon::parse($shift->date)->timezone(company()->timezone)->day] = $shift->shift->shift_name;
            }

            $employeeName = $employee->name;

            $final[$employee->id . '#' . $employee->name][] = $employeeName;

            if ($employee->employeeDetail->joining_date->greaterThan(Carbon::parse(Carbon::parse('01-' . $this->month . '-' . $this->year)))) {
                if ($this->month == $employee->employeeDetail->joining_date->format('m') && $this->year == $employee->employeeDetail->joining_date->format('Y')) {
                    if ($employee->employeeDetail->joining_date->format('d') == '01') {
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->format('d'), '-');
                        $shiftColorCode = array_fill(1, $employee->employeeDetail->joining_date->format('d'), '');
                    }
                    else {
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->subDay()->format('d'), '-');
                    }
                }
            }

            foreach ($employee->leaves as $leave) {

                if ($leave->duration != 'half day') {
                    $employeedata[$employee_index]['dates'][$leave->leave_date->day] = __('app.leave') . ': ' . $leave->type->type_name;
                    $shiftColorCode[$leave->leave_date->day] = '';
                }
            }

            foreach ($this->holidays as $holiday) {
                if (in_array($holiday->date->day, array_keys($employeedata[$employee_index]['dates']))) {
                    if ($employeedata[$employee_index]['dates'][$holiday->date->day] == 'Absent' || $employeedata[$employee_index]['dates'][$holiday->date->day] == '--') {
                        $employeedata[$employee_index]['dates'][$holiday->date->day] = 'Holiday';
                        $holidayOccasions[$holiday->date->day] = $holiday->occassion;
                        $shiftColorCode[$holiday->date->day] = '';
                    }
                }
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

        $now = Carbon::parse($this->startdate, company()->timezone);
        $this->weekStartDate = $now->copy()->startOfWeek(attendance_setting()->week_start_from);
        $startDate = Carbon::parse($this->weekStartDate)->day;
        $this->weekEndDate = Carbon::parse($this->weekStartDate->copy()->addDays(6))->day;

        if ($this->viewType != 'week') {
            $num = isset($employeedata['dates']) ? count($employeedata['dates']) : 0;

            for ($index = 1; $index <= $num; $index++) {
                $data[] = $employeedata['dates'][$index];
            }
        }
        else {
            $num = isset($employeedata['dates']) ? count($employeedata['dates']) : 0;

            $period = CarbonPeriod::create($this->startdate, $this->enddate); // Get All Dates from start to end date

            foreach ($period->toArray() as $date) {
                $data[] = $employeedata['dates'][$date->day];
            }
        }

        return $data;
    }

}
