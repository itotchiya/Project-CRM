<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Module;
use App\Models\Company;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        $module = Module::where('module_name', 'tickets')->first();

        $count = Company::withoutGlobalScope(\App\Scopes\ActiveScope::class)->count();

        if (!is_null($module) && $count > 0) {
            $permissionType = [
                    'module_id' => $module->id,
                    'display_name' => 'Manage Ticket Groups',
                    'name' => 'manage_ticket_groups',
                    'is_custom' => 1,
                    'allowed_permissions' => Permission::ALL_NONE,
                ];

            $permission = new Permission();
            $permission->name = $permissionType['name'];
            $permission->display_name = $permissionType['display_name'];
            $permission->module_id = $module->id;
            $permission->is_custom = $permissionType['is_custom'];
            $permission->allowed_permissions = $permissionType['allowed_permissions'];
            $permission->save();

            $companies = Company::select('id')->get();

            foreach($companies as $company){

                $role = Role::where('name', 'admin')->where('company_id', $company->id)->first();

                $permissionRole = new PermissionRole();
                $permissionRole->permission_id = $permission->id;
                $permissionRole->role_id = $role->id;
                $permissionRole->permission_type_id = 4;
                $permissionRole->save();

                $admins = User::allAdmins($company->id);

                foreach($admins as $admin) {
                    $userPermission = new UserPermission();
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
     *
     * @return void
     */
    public function down()
    {

    }

};
