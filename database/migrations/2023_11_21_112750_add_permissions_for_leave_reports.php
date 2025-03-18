<?php

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up()
    {
        Schema::table('project_time_logs', function (Blueprint $table) {
            $table->text('memo')->nullable()->change();
        });

        \App\Models\EmailNotificationSetting::where('slug', 'clock-in-notification')->delete();

        $module = Module::where('module_name', 'reports')->first();

        if (!is_null($module)) {
            $permissionName = 'view_leave_report';

            $permission = Permission::where('name', $permissionName)->update([
                'name' => $permissionName,
                'display_name' => ucwords(str_replace('_', ' ', $permissionName)),
                'is_custom' => 1,
                'module_id' => $module->id,
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5
            ]);

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }

};
