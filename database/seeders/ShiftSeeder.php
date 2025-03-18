<?php

namespace Database\Seeders;

use App\Models\EmployeeShift;
use App\Models\EmployeeShiftSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        EmployeeShift::create([
            'company_id' => $companyId,
            'shift_name' => 'Night Shift',
            'color' => '#4d4c4c',
            'shift_short_code' => 'NS',
            'office_start_time' => '22:00:00',
            'office_end_time' => '06:00:00',
            'halfday_mark_time' => '01:00:00',
            'late_mark_duration' => 15,
            'clockin_in_day' => 1,
            'office_open_days' => '["1","2","3","4","5"]'
        ]);

        EmployeeShift::create([
            'company_id' => $companyId,
            'shift_name' => 'Day Shift',
            'color' => '#ff0000',
            'shift_short_code' => 'DS',
            'office_start_time' => '08:00:00',
            'office_end_time' => '17:00:00',
            'halfday_mark_time' => '13:30:00',
            'late_mark_duration' => 15,
            'clockin_in_day' => 1,
            'office_open_days' => '["1","2","3","4","5"]'
        ]);


        $users = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->where('roles.name', 'employee')
            ->where('users.company_id', $companyId)
            ->select('users.id')
            ->groupBy('users.id')->pluck('id')->toArray();

        $shiftIds = EmployeeShift::where('company_id', $companyId)->where('shift_name', '<>', 'Day Off')->get();

        foreach ($users as $key => $value) {
            for ($i = 0; $i < 20; $i++) {
                $empShift = $shiftIds->random();
                $date = Carbon::parse(now()->year . '-' . now()->month . '-' . ($i + 1));
                $schedule = EmployeeShiftSchedule::firstOrNew([
                    'user_id' => $value,

                    'date' => $date,
                    'shift_start_time' => $date->format('Y-m-d') . ' ' . $empShift->office_start_time,
                    'shift_end_time' => $date->format('Y-m-d') . ' ' . $empShift->office_end_time
                ]);
                $schedule->employee_shift_id = $empShift->id;
                $schedule->save();
            }
        }
    }

}
