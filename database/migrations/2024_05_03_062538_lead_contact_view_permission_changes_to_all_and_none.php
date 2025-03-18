<?php

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\UserPermission;
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
            $permission->allowed_permissions = Permission::ALL_NONE;
            $permission->save();

            // update to all permission if added , owned , both assigned
            UserPermission::where('permission_id', $permission->id)
                ->whereIn('permission_type_id', [1, 2, 3])
                ->update(['permission_type_id' => 4]);

            // update to all permission if added , owned , both assigned
            PermissionRole::where('permission_id', $permission->id)
                ->whereIn('permission_type_id', [1, 2, 3])
                ->update(['permission_type_id' => 4]);
        }


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = Permission::where('name', 'view_lead')->first();
        if ($permission) {
            $permission->allowed_permissions = Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5;
            $permission->save();
        }
    }

};
