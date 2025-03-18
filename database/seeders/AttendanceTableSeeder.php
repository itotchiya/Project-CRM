<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $faker = \Faker\Factory::create();

        $userIds = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'employee')
            ->where('users.company_id', $companyId)
            ->pluck('users.id')
            ->toArray();

        $adminId = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'admin')
            ->where('users.company_id', $companyId)
            ->value('users.id');

        $data = [];

        foreach ($userIds as $userId) {
            $date = $faker->randomElement([$faker->dateTimeThisMonth()->format('Y-m-d'), $faker->dateTimeThisYear('now')->format('Y-m-d')]);
            $start = $date . 'T' . $faker->randomElement(['09:00', '10:00', '11:00', '12:00', '13:00']) . '+00:00';

            $clockIn = Carbon::parse($start)->addMinutes($faker->randomElement([0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 7, 10, 15, -20, 45, 120]))->format('Y-m-d H:i:s');
            $clockInIp = $faker->ipv4;

            $data[] = [
                'user_id' => $userId,
                'company_id' => $companyId,
                'half_day' => 'no',
                'late' => $faker->randomElement(['yes', 'no']),
                'clock_in_time' => $clockIn,
                'clock_out_time' => Carbon::parse($clockIn)->addHours($faker->numberBetween(1, 9))->format('Y-m-d H:i:s'),
                'clock_in_ip' => $clockInIp,
                'clock_out_ip' => $clockInIp,
                'created_at' => $faker->dateTimeThisYear(),
                'added_by' => $adminId,
            ];
        }

        Attendance::insert($data);
    }

}
