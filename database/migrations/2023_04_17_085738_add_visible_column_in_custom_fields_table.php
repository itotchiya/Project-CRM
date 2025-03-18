<?php

use App\Models\EmployeeShift;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */

    public function up(): void
    {
        if (!Schema::hasColumn('proposals', 'send_status')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->boolean('send_status')->default(true);
            });
        }

        if (!Schema::hasColumn('attendances', 'overwrite_attendance')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->enum('overwrite_attendance', ['yes', 'no'])->default('no');
            });
        }

        if (!Schema::hasColumn('custom_fields', 'custom_fields')) {
            Schema::table('custom_fields', function (Blueprint $table) {
                $table->enum('visible', ['true', 'false'])->default('false')->after('export')->nullable();
            });
        }

        EmployeeShift::where('shift_name', 'Day Off')->update(['color' => '#E8EEF3']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->dropColumn('visible');
        });
    }

};
