<?php

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permission = Permission::where('name', 'view_holiday')->first();
        $role = Role::where('name', 'employee')->first();

        if ($permission && $role) {

            PermissionRole::where('permission_id', $permission->id)
                ->where('role_id', $role->id)
                ->update([
                    'permission_type_id' => 2,
                ]);

            UserPermission::whereHas('user.role', function ($query) use ($role) {
                $query->whereHas('role', function ($roleQuery) use ($role) {
                    $roleQuery->where('name', $role->name);
                });
            })
                ->where('permission_id', $permission->id)
                ->update(['permission_type_id' => 2]); // Owned
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
