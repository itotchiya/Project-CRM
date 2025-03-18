<?php

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // update to all and none permission
        $permission = Permission::where('name', 'view_lead')->first();

        if ($permission) {
            $permission->allowed_permissions = Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5;
            $permission->save();
        }

        Schema::whenTableDoesntHaveColumn('leads', 'lead_owner', function (Blueprint $table) {
            $table->integer('lead_owner')->nullable()->after('added_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = Permission::where('name', 'view_lead')->first();

        if ($permission) {
            $permission->allowed_permissions = Permission::ALL_NONE;
            $permission->save();
        }
    }

};
