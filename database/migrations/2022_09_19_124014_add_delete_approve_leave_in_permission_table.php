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
     *
     * @return void
     */

    public function up()
    {
        $module = Module::where('module_name', 'leaves')->first();

        if (!is_null($module)) {

            $permissionName = 'delete_approve_leaves';

            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'display_name' => ucwords(str_replace('_', ' ', $permissionName)),
                'is_custom' => 1,
                'module_id' => $module->id,
                'allowed_permissions' => Permission::ALL_NONE
            ]);

            $companies = Company::select('id')->get();

            foreach ($companies as $company) {

                $role = Role::where('name', 'admin')
                    ->where('company_id', $company->id)
                    ->first();

                $permissionRole = new PermissionRole();
                $permissionRole->permission_id = $permission->id;
                $permissionRole->role_id = $role->id;
                $permissionRole->permission_type_id = 4; // All
                $permissionRole->save();
            }


            $adminUser = User::allAdmins();

            foreach ($adminUser as $adminUsers) {
                $userPermission = new UserPermission();
                $userPermission->user_id = $adminUsers->id;
                $userPermission->permission_id = $permission->id;
                $userPermission->permission_type_id = 4; // All
                $userPermission->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Permission::where('name', 'delete_approve_leaves')->delete();

    }

};
