<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EmployeeShiftSchedule;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $getShiftSchedule = EmployeeShiftSchedule::whereNull('shift_start_time')->whereDate('date', '>=', now()->toDateString());

        $getShiftSchedule->chunk(100, function ($getShiftSchedule) {
            foreach ($getShiftSchedule as $shiftSchedule) {

                $shiftSchedule->shift_start_time = $shiftSchedule->date->toDateString() . ' ' . $shiftSchedule->shift->office_start_time;

                if (Carbon::parse($shiftSchedule->shift->office_start_time)->gt(Carbon::parse($shiftSchedule->shift->office_end_time))) {
                    $shiftSchedule->shift_end_time = $shiftSchedule->date->addDay()->toDateString() . ' ' . $shiftSchedule->shift->office_end_time;
                }
                else {
                    $shiftSchedule->shift_end_time = $shiftSchedule->date->toDateString() . ' ' . $shiftSchedule->shift->office_end_time;
                }

                $shiftSchedule->saveQuietly();

            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
