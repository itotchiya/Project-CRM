<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     */

    public function up(): void
    {
        $module = Module::where('module_name', 'orders')->first();

        $count = Company::withoutGlobalScope(\App\Scopes\ActiveScope::class)->count();

        if (!is_null($module) && $count > 0) {
            $permissionType = [
                'module_id' => $module->id,
                'display_name' => 'View Project Orders',
                'name' => 'view_project_orders',
                'is_custom' => 1,
                'allowed_permissions' => Permission::ALL_NONE,
            ];

            $permission = Permission::where('name', $permissionType['name'])->where('module_id', $module->id)->first() ?: new Permission();
            $permission->name = $permissionType['name'];
            $permission->display_name = $permissionType['display_name'];
            $permission->module_id = $module->id;
            $permission->is_custom = $permissionType['is_custom'];
            $permission->allowed_permissions = $permissionType['allowed_permissions'];
            $permission->save();

            $companies = Company::select('id')->get();

            foreach ($companies as $company) {

                $role = Role::where('name', 'admin')->where('company_id', $company->id)->first();

                if ($role) {
                    $permissionRole = PermissionRole::where('permission_id', $permission->id)->where('role_id', $role->id)->where('permission_type_id', 4)->first() ?: new PermissionRole();
                    $permissionRole->permission_id = $permission->id;
                    $permissionRole->role_id = $role->id;
                    $permissionRole->permission_type_id = 4;
                    $permissionRole->save();

                }


                $admins = User::allAdmins($company->id);

                foreach ($admins as $admin) {
                    $userPermission = UserPermission::where('user_id', $admin->id)->where('permission_id', $permission->id)->first() ?: new UserPermission();
                    $userPermission->user_id = $admin->id;
                    $userPermission->permission_id = $permission->id;
                    $userPermission->permission_type_id = 4;
                    $userPermission->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }

};
