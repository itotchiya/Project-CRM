<?php

use App\Models\EmployeeShift;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $employeeShift = EmployeeShift::where('shift_name', 'Day Off')->get();

        foreach ($employeeShift as $shift) {

            $shift->update([
                'halfday_mark_time' => '00:00:00',
            ]);

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
