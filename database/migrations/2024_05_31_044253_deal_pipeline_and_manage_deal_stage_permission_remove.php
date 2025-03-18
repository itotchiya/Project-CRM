<?php

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'add_deal_pipeline', 'view_deal_pipeline',
            'delete_deal_pipeline', 'edit_deal_pipeline', 'manage_deal_stages'
        ];
        
        $permissionIds = Permission::whereIn('name', $permissions)
            ->pluck('id');

        Permission::whereIn('name', $permissions)->delete();
        UserPermission::whereIn('permission_id', $permissionIds)->delete();
        PermissionRole::whereIn('permission_id', $permissionIds)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
