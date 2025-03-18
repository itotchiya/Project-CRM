<?php
namespace Database\Seeders;

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $employees = User::allEmployees(null, false, null, $companyId)->pluck('id')->toArray();
        $leaveTypes = LeaveType::where('company_id', $companyId)->get()->pluck('id')->toArray();

        $employee = $employees[array_rand($employees)];
        $leaveType = $leaveTypes[array_rand($leaveTypes)];

        $count = config('app.seed_record_count');
        \App\Models\Leave::factory()->count((int)$count)->make()->each(function (Leave $leave)use($companyId, $leaveType, $employee){
            $leave->user_id = $employee;
            $leave->leave_type_id = $leaveType;
            $leave->company_id = $companyId;
            $leave->save();
        });
    }

}
