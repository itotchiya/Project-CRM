<?php

use App\Models\Company;
use App\Models\ProjectTimeLog;
use App\Models\Task;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add project_id in project_time_logs where missing

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            $tasks = Task::whereNotNull('project_id')->whereHas('timeLogged')->where('company_id', $company->id)->select('id', 'project_id')->get();

            foreach($tasks as $task) {
                ProjectTimeLog::where('task_id', $task->id)->update(['project_id' => $task->project_id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

};
