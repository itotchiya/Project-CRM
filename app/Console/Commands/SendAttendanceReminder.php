<?php

namespace App\Console\Commands;

use App\Events\AttendanceReminderEvent;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\EmployeeShiftSchedule;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAttendanceReminder extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-attendance-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send attendance reminder to the employee';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        Company::active()
            ->select(['companies.id as id', 'timezone', 'attendance_settings.*'])
            ->join('attendance_settings', 'attendance_settings.company_id', '=', 'companies.id')
            ->where('alert_after_status', 1)
            ->whereNotNull('alert_after')
            ->where('alert_after', '<>', 0)->chunk(50, function ($companies) {

                foreach ($companies as $company) {

                    $today = now($company->timezone)->format('Y-m-d');

                    $holiday = Holiday::where('company_id', $company->id)->where('date', $today)->first();

                    // Today is holiday
                    if ($holiday) {
                        continue;
                    }

                    $employeeShiftTime = EmployeeShiftSchedule::with('shift', 'user')
                        ->where(function ($query) use ($company) {
                            $query->where('shift_start_time', '<=', now($company->timezone));
                            $query->where('shift_end_time', '>=', now($company->timezone));
                        })
                        ->whereHas('user', function ($query) use ($today) {
                            $query->whereHas('employeeDetail', function ($query) use ($today) {
                                $query->where('attendance_reminder', '!=', $today)
                                    ->orWhereNull('attendance_reminder');
                            });
                        })->get();


                    foreach ($employeeShiftTime as $employeeShiftTimes) {

                        if (is_null($employeeShiftTimes->shift->office_start_time)) {
                            continue;
                        }

                        $startDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $employeeShiftTimes->shift->office_start_time, $company->timezone);
                        $currentDateTime = now($company->timezone)->addMinutes($company->alert_after);

                        if ($currentDateTime->greaterThan($startDateTime)) {

                            $clockInCount = Attendance::getTotalUserClockInWithTime(Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $employeeShiftTimes->shift->office_start_time), Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $employeeShiftTimes->shift->office_end_time), $employeeShiftTimes->user_id);

                            if (!$clockInCount) {
                                event(new AttendanceReminderEvent($employeeShiftTimes->user));
                                $employeeShiftTimes->user->employeeDetail->attendance_reminder = $today;
                                $employeeShiftTimes->user->employeeDetail->saveQuietly();
                            }

                        }

                    }
                }

            });

        return Command::SUCCESS;

    }

}
