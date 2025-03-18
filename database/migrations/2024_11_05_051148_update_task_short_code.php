<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Task;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        $tasks = Task::whereNull('task_short_code')->whereNotNull('project_id')->get();

        foreach ($tasks as $task) {

            try{
                $project = $task->project;
                $task->task_short_code = $project->project_short_code . '-' . $task->id;
                $task->saveQuietly();
            }catch(\Exception $e){}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
