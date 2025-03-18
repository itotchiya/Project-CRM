<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Company;
use App\Models\Holiday;
use App\Models\AutomateShift;
use App\Models\EmployeeShift;
use Illuminate\Console\Command;
use App\Models\RotationAutomateLog;
use App\Models\ShiftRotationSequence;
use App\Models\EmployeeShiftSchedule;
use App\Events\ShiftRotationEvent;


class AssignShiftRotation extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign-shift-rotation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign shift rotation to employees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentDate = Carbon::now();

        $dayOfWeekMap = [
            1 => Carbon::MONDAY,
            2 => Carbon::TUESDAY,
            3 => Carbon::WEDNESDAY,
            4 => Carbon::THURSDAY,
            5 => Carbon::FRIDAY,
            6 => Carbon::SATURDAY,
            0 => Carbon::SUNDAY,
        ];

        $dayNameWeekMap = [
            'sunday' => Carbon::SUNDAY,
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
        ];

        Company::active()->select('id')->chunk(50, function ($companies) use ($currentDate, $dayOfWeekMap, $dayNameWeekMap) {
            foreach ($companies as $company) {

                $dayOff = EmployeeShift::where('company_id', $company->id)->where('shift_name', 'Day Off')->first();
                $automateShifts = AutomateShift::whereHas('rotation', function ($query) use ($company) {
                    $query->where('company_id', $company->id);
                })->with(['sequences', 'rotation', 'user'])->get();

                $shiftSequences = ShiftRotationSequence::whereIn('employee_shift_rotation_id', $automateShifts->pluck('employee_shift_rotation_id'))->orderBy('sequence')->get()->groupBy('employee_shift_rotation_id');

                foreach ($automateShifts as $automateShift) {
                    if ($automateShift->rotation && $automateShift->rotation->status == 'active') {

                        $log = null;

                        if ($automateShift->rotation && $automateShift->user) {
                            $logs = RotationAutomateLog::where('company_id', $company->id)->where('employee_shift_rotation_id', $automateShift->rotation->id)->where('user_id', $automateShift->user->id)->get()->keyBy('employee_shift_rotation_id');

                            $log = $logs->get($automateShift->rotation->id);
                        }

                        $sequences = $shiftSequences->get($automateShift->employee_shift_rotation_id, collect());

                        $currentSequence = $log->sequence ?? null;

                        if ($currentSequence && ($log && ($log->cron_run_date != $currentDate->format('Y-m-d')))) {
                            $sequence = $sequences->where('sequence', '>', $currentSequence)->first();

                            $nextShiftSequence = $sequence ? $sequence : $sequences->first();
                        }
                        else {
                            $nextShiftSequence = $sequences->first();
                        }

                        if ($nextShiftSequence && $sequences->isNotEmpty()) {
                            $employeeShift = EmployeeShift::find($nextShiftSequence->employee_shift_id);
                            $officeOpenDays = json_decode($employeeShift->office_open_days);

                            $employeeData = $automateShift->user ? User::with('employeeDetail')->find($automateShift->user->id) : null;

                            $dayString = str_replace('every-', '', $automateShift->rotation->schedule_on);
                            $dayString = strtolower($dayString);

                            $dayOfWeek = '';

                            if (isset($dayNameWeekMap[$dayString])) {
                                $dayOfWeek = $dayNameWeekMap[$dayString];
                            }

                            // Retrieve holidays based on employee details
                            $holidaysForUser = $employeeData ? $this->retrieveHolidaysForUser($employeeData) : [];

                            $rotationFrequency = $automateShift->rotation->rotation_frequency;
                            $isWeekly = $rotationFrequency == 'weekly';
                            $isBiWeekly = $rotationFrequency == 'bi-weekly';
                            $isMonthly = $rotationFrequency == 'monthly';
                            $bothWeekly = $rotationFrequency == 'weekly' || $rotationFrequency == 'bi-weekly';
                            $isCorrectDayOfWeek = $currentDate->dayOfWeek == $dayOfWeek;

                            $currentDates = [];
                            $weekOffDays = [];

                            // Weekly
                            if ($isWeekly && $isCorrectDayOfWeek) {
                                $reqFrom = 'weekly';
                                $currentDates = $this->getOfficeOpenDates($reqFrom, $dayOfWeekMap, $officeOpenDays, $holidaysForUser);
                                $weekOffDays = $this->getWeekendDays($reqFrom, $dayOfWeekMap, $officeOpenDays);
                            }

                            // Bi-weekly
                            if ($isBiWeekly && $isCorrectDayOfWeek) {
                                $shouldGetDates = true;

                                if (!is_null($log) && Carbon::parse($log->cron_run_date)->diffInDays() < 14) {
                                    $shouldGetDates = false;
                                }

                                if ($shouldGetDates) {
                                    $reqFrom = 'bi-weekly';
                                    $currentDates = $this->getOfficeOpenDates($reqFrom, $dayOfWeekMap, $officeOpenDays, $holidaysForUser);
                                    $weekOffDays = $this->getWeekendDays($reqFrom, $dayOfWeekMap, $officeOpenDays);
                                }
                            }

                            // Monthly
                            $rotationDate = $this->getFullDateForCurrentMonth($automateShift->rotation->rotation_date);
                            $isCorrectDayOfMonth = $rotationDate == $currentDate->format('Y-m-d');

                            $carbonDate = Carbon::parse($rotationDate);
                            $lastDateOfMonth = $carbonDate->endOfMonth();

                            if($carbonDate->month === 2 && $lastDateOfMonth->format('d') < $automateShift->rotation->rotation_date && $currentDate->format('Y-m-d') == $rotationDate){
                                $isCorrectDayOfMonth = true;
                            }
                            else if($carbonDate->month !== 2 ) {
                                $isCorrectDayOfMonth = $rotationDate == $currentDate->format('Y-m-d');
                            }

                            if ($isMonthly && $isCorrectDayOfMonth) {
                                $reqFrom = 'monthly';
                                $currentDates = $this->getOfficeOpenDates($reqFrom, $dayOfWeekMap, $officeOpenDays, $holidaysForUser, $rotationDate);
                                $weekOffDays = $this->getWeekendDays($reqFrom, $dayOfWeekMap, $officeOpenDays, $rotationDate);
                            }

                            if ((!empty($currentDates) && (($bothWeekly && $isCorrectDayOfWeek) || ($isMonthly && $isCorrectDayOfMonth))) && ($automateShift->user && $employeeData)) {
                                foreach ($currentDates as $date) {
                                    $date = Carbon::parse($date);

                                    if ($employeeData && $date->greaterThanOrEqualTo($employeeData->employeeDetail->joining_date) && (is_null($officeOpenDays) || (is_array($officeOpenDays) && in_array($date->dayOfWeek, $officeOpenDays)))) {

                                        $shift = EmployeeShiftSchedule::where('date', $date->format('Y-m-d'))->where('user_id', $automateShift->user->id)->first();
                                        if (!$shift) {
                                            $shift = new EmployeeShiftSchedule();
                                            $shift->user_id = $automateShift->user->id;
                                            $shift->date = $date->format('Y-m-d');
                                            $shift->employee_shift_id = $nextShiftSequence->employee_shift_id;
                                            $shift->added_by = null;
                                            $shift->last_updated_by = null;
                                            $shift->shift_start_time = $date->format('Y-m-d') .' '.$employeeShift->office_start_time;
                                            $shift->shift_end_time = $date->format('Y-m-d') .' '.$employeeShift->office_end_time;
                                            $shift->remarks = 'Automate shift rotation assigned';
                                            $shift->save();

                                        }

                                        if($shift && $automateShift->rotation->override_shift == 'yes') {
                                            $shift->user_id = $automateShift->user->id;
                                            $shift->date = $date->format('Y-m-d');
                                            $shift->employee_shift_id = $nextShiftSequence->employee_shift_id;
                                            $shift->added_by = null;
                                            $shift->last_updated_by = null;
                                            $shift->shift_start_time = $date->format('Y-m-d') .' '.$employeeShift->office_start_time;
                                            $shift->shift_end_time = $date->format('Y-m-d') .' '.$employeeShift->office_end_time;
                                            $shift->remarks = 'Automate shift rotation assigned';
                                            $shift->save();
                                        }

                                    }
                                }

                                foreach ($weekOffDays as $weekOffDay) {
                                    $weekOffDay = Carbon::parse($weekOffDay);

                                    $weekEndShift = EmployeeShiftSchedule::where('user_id', $automateShift->user->id)->where('date', $weekOffDay->format('Y-m-d'))->first();

                                    if (!$weekEndShift) {
                                        EmployeeShiftSchedule::create([
                                            'user_id' => $automateShift->user->id,
                                            'date' => $weekOffDay->format('Y-m-d'),
                                            'employee_shift_id' => $dayOff->id,
                                            'added_by' => null,
                                            'last_updated_by' => null,
                                            'shift_start_time' => $weekOffDay->format('Y-m-d') .' '.$dayOff->office_start_time,
                                            'shift_end_time' => $weekOffDay->format('Y-m-d') .' '.$dayOff->office_end_time,
                                            'remarks' => 'Automate shift rotation assigned',
                                        ]);
                                    }
                                    elseif ($weekEndShift && $automateShift->rotation->override_shift == 'yes') {
                                        $weekEndShift->user_id = $automateShift->user->id;
                                        $weekEndShift->date = $weekOffDay->format('Y-m-d');
                                        $weekEndShift->employee_shift_id = $dayOff->id;
                                        $weekEndShift->added_by = null;
                                        $weekEndShift->last_updated_by = null;
                                        $weekEndShift->shift_start_time = $weekOffDay->format('Y-m-d') .' '.$dayOff->office_start_time;
                                        $weekEndShift->shift_end_time = $weekOffDay->format('Y-m-d') .' '.$dayOff->office_end_time;
                                        $weekEndShift->remarks = 'Automate shift rotation assigned';
                                        $weekEndShift->save();
                                    }
                                }

                                if (!$log) {
                                    $log = new RotationAutomateLog();
                                    $log->company_id = $company->id;
                                    $log->user_id = $automateShift->user->id;
                                    $log->employee_shift_rotation_id = $nextShiftSequence->employee_shift_rotation_id;
                                    $log->employee_shift_id = $nextShiftSequence->employee_shift_id;
                                    $log->sequence = $nextShiftSequence->sequence;
                                    $log->cron_run_date = $currentDate->format('Y-m-d');
                                }
                                else {
                                    $log->employee_shift_id = $nextShiftSequence->employee_shift_id;
                                    $log->sequence = $nextShiftSequence->sequence;
                                    $log->cron_run_date = $currentDate->format('Y-m-d');
                                }

                                $log->save();

                                if($automateShift->rotation->send_mail == 'yes') {
                                    event(new ShiftRotationEvent($employeeData, $currentDates, $rotationFrequency));
                                }
                            }
                        }
                    }
                }
            }
        });

        return Command::SUCCESS;
    }

    private function getFullDateForCurrentMonth($dayOfMonth)
    {
        $currentDate = Carbon::now();
        $daysInMonth = $currentDate->daysInMonth;

        $dayOfMonth = min($dayOfMonth, $daysInMonth);
        $fullDate = Carbon::create($currentDate->year, $currentDate->month, $dayOfMonth);

        return $fullDate->format('Y-m-d');
    }

    private function getUserHolidays($holidaysForUser)
    {
        if (!empty($holidaysForUser)) {
            return array_map(function ($holiday) {
                return Carbon::parse($holiday)->format('Y-m-d');
            }, $holidaysForUser);
        }
        else {
            return $holidaysForUser;
        }
    }

    private function getWeekendDays($reqFrom, $dayOfWeekMap, $officeOpenDays, $rotationDate = null)
    {
        $today = Carbon::now();
        $weekendDates = [];
        $officeDays = [];

        $startDate = $today->copy();
        $endDate = match ($reqFrom) {
            'bi-weekly' => $today->copy()->addDays(13),
            default => $today->copy()->addDays(6),
        };

        if ($reqFrom == 'monthly') {
            $currentDate = $rotationDate ? Carbon::parse($rotationDate) : $today;
            $nextMonth = $today->copy()->addMonthNoOverflow();
            $year = $nextMonth->format('Y');
            $month = $nextMonth->format('m');
            $date = Carbon::create($year, $month, 1);
            $daysInNextMonth = $date->daysInMonth;

            if ($daysInNextMonth < 30) {
                // End date is a day before the last date of next month (in feb.)
                if ($daysInNextMonth == 28) {
                    $endDate = $date->copy()->endOfMonth()->subDay();
                }
                else {
                    $endDate = $currentDate->copy()->addDays($daysInNextMonth);
                }
            }
            else if($currentDate->daysInMonth < 30 && $daysInNextMonth >= 30){
                // End date is a day before the last date of next month (in march)
                if ($currentDate->daysInMonth == 29) {
                    $subDays = (31 - $currentDate->daysInMonth);
                    $endDate = $currentDate->copy()->addDays($daysInNextMonth)->subDays($subDays);
                }
                elseif ($currentDate->daysInMonth == 28) {
                    $subDays = (29 - $currentDate->daysInMonth);
                    $endDate = $currentDate->copy()->addDays($daysInNextMonth)->subDays($subDays);
                }
                else {
                    $subDays = (30 - $currentDate->daysInMonth);
                    $endDate = $currentDate->copy()->addDays($daysInNextMonth)->subDays($subDays);
                }
            }
            else {
                $endDate = $currentDate->copy()->addMonth()->subDay(); // End date is a day before the same date of next month
            }
        }

        foreach ($officeOpenDays as $dayOfWeek) {
            if (isset($dayOfWeekMap[$dayOfWeek])) {
                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    if ($date->dayOfWeek == $dayOfWeekMap[$dayOfWeek]) {
                        $officeDays[] = $date->format('Y-m-d');
                    }
                }
            }
        }

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if (!in_array($date->dayOfWeek, $officeOpenDays)) {
                $weekendDates[] = $date->format('Y-m-d');
            }
        }

        sort($weekendDates);

        return $weekendDates;
    }

    private function getOfficeOpenDates($reqFrom, $dayOfWeekMap, $officeOpenDays, $holidaysForUser, $rotationDate = null)
    {
        $currentDates = [];
        $today = Carbon::now();
        $holidayDates = $this->getUserHolidays($holidaysForUser);

        if ($reqFrom === 'bi-weekly') {
            $endDate = $today->copy()->addDays(13); // End date after 14 days from today
        }
        elseif ($reqFrom === 'monthly') {
            $currentDate = $rotationDate ? Carbon::parse($rotationDate) : $today;

            $nextMonth = $today->copy()->addMonthNoOverflow();
            $year = $nextMonth->format('Y');
            $month = $nextMonth->format('m');
            $date = Carbon::create($year, $month, 1);
            $daysInNextMonth = $date->daysInMonth;

            if ($daysInNextMonth < 30) {
                // End date is a day before the last date of next month (feb.)
                $endDate = $currentDate->copy()->addDays($daysInNextMonth);
            }
            else if($currentDate->daysInMonth < 30 && $daysInNextMonth >= 30){
                if ($currentDate->daysInMonth == 29) {
                    $subDays = (31 - $currentDate->daysInMonth);
                    $endDate = $currentDate->copy()->addDays($daysInNextMonth)->subDays($subDays);
                }
                else {
                    $subDays = (30 - $currentDate->daysInMonth);
                    $endDate = $currentDate->copy()->addDays($daysInNextMonth)->subDays($subDays);
                }
            }
            else {
                $endDate = $currentDate->copy()->addMonth()->subDay(); // End date is a day before the same date of next month
            }
        }
        else {
            $endDate = $today->copy()->addDays(6); // End date after 7 days from today
        }

        foreach ($officeOpenDays as $dayOfWeek) {
            if (isset($dayOfWeekMap[$dayOfWeek])) {
                for ($date = ($reqFrom === 'monthly' ? $currentDate : $today)->copy(); $date->lte($endDate); $date->addDay()) {
                    if ($date->dayOfWeek == $dayOfWeekMap[$dayOfWeek] && !in_array($date->format('Y-m-d'), $holidayDates)) {
                        $currentDates[] = $date->format('Y-m-d');
                    }
                }
            }
        }

        sort($currentDates);

        return $currentDates;
    }

    private function retrieveHolidaysForUser($employeeData)
    {
        return Holiday::where(function ($query) use ($employeeData) {
            $query->where(function ($subquery) use ($employeeData) {
                $subquery->where(function ($q) use ($employeeData) {
                    $q->where('department_id_json', 'like', '%"' . $employeeData->employeeDetail->department_id . '"%')
                        ->orWhereNull('department_id_json');
                });
                $subquery->where(function ($q) use ($employeeData) {
                    $q->where('designation_id_json', 'like', '%"' . $employeeData->employeeDetail->designation_id . '"%')
                        ->orWhereNull('designation_id_json');
                });
                $subquery->where(function ($q) use ($employeeData) {
                    $q->where('employment_type_json', 'like', '%"' . $employeeData->employeeDetail->employment_type . '"%')
                        ->orWhereNull('employment_type_json');
                });
            });
        })->get()->pluck('date')->map(function ($date) {
            return $date->format('Y-m-d');
        })->toArray();
    }

}
