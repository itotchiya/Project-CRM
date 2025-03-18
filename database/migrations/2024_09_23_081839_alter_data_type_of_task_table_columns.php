<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Task;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable()->change();
            $table->dateTime('due_date')->nullable()->change();
        });


        Task::whereNotNull('created_at')
            ->whereRaw("TIME(start_date) = '00:00:00' AND TIME(due_date) = '00:00:00'")
            ->update([
                'start_date' => DB::raw("CONCAT(DATE(start_date), ' ', TIME(created_at))"),
                'due_date' => DB::raw("CONCAT(DATE(due_date), ' ', TIME(created_at))"),
            ]);



//        $tasks = Task::whereNotNull('created_at')
//            ->whereRaw("TIME(start_date) = '00:00:00' AND TIME(due_date) = '00:00:00'")
//            ->get();
//
//        $tasks->each(function($row) {
//            $startDate = Carbon::parse($row->start_date)->format('Y-m-d');
//            $dueDate = Carbon::parse($row->due_date)->format('Y-m-d');
//            $createdAtTime = Carbon::parse($row->created_at)->format('H:i:s');
//
//            $newStartDate = Carbon::parse("{$startDate} {$createdAtTime}");
//            $newDueDate = Carbon::parse("{$dueDate} {$createdAtTime}");
//
//            // Perform bulk update in one query for each task
//            $row->update([
//                'start_date' => $newStartDate,
//                'due_date' => $newDueDate,
//            ]);
//        });


//        Task::whereNotNull('created_at')->get()->each(function($row) {
//
//            if (Carbon::parse($row->start_date)->format('H:i:s') === '00:00:00' && Carbon::parse($row->start_date)->format('H:i:s') === '00:00:00') {
//
//                $startDate = Carbon::parse($row->start_date)->format('Y-m-d');
//                $dueDate = Carbon::parse($row->due_date)->format('Y-m-d');
//
//                $createdAtTime = Carbon::parse($row->created_at)->format('H:i:s');
//
//                $newStartDate = Carbon::parse("{$startDate} {$createdAtTime}");
//                $newDueDate = Carbon::parse("{$dueDate} {$createdAtTime}");
//
//                Task::where('id', $row->id)->update([
//                        'start_date' => $newStartDate,
//                        'due_date' => $newDueDate,
//                    ]);
//            }
//        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('your_table_name', function (Blueprint $table) {
            $table->date('start_date')->nullable()->change();
            $table->date('due_date')->nullable()->change();
        });
    }
};
