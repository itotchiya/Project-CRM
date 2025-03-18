<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This is created as old file was not executed for many customers
        if (!Schema::hasColumn('holidays', 'department_id_json')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->text('department_id_json')->nullable();
                $table->text('designation_id_json')->nullable();
                $table->text('employment_type_json')->nullable();
            });
        }

        if (Schema::hasColumns('holidays', ['department_id', 'designation_id', 'employment_type'])) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->dropColumn('department_id');
                $table->dropColumn('designation_id');
                $table->dropColumn('employment_type');
            });
        }


        Permission::whereIn('name', ['view_holiday', 'edit_holiday', 'delete_holiday'])->update(['allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn('department_id_json');
            $table->dropColumn('designation_id_json');
            $table->dropColumn('employment_type_json');
        });
    }

};
