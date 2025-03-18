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
     */
    public function up(): void
    {
        $module = Module::where('module_name', 'employees')->first();

        if($module){

            $permissions = [
                [
                    'module_id' => $module->id,
                    'name' => 'view_increment_promotion',
                    'display_name' => 'View Increment Promotion',
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1
                ],
                [
                    'module_id' => $module->id,
                    'name' => 'manage_increment_promotion',
                    'display_name' => 'Manage Increment Promotion',
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1
                ]
            ];

            $companies = Company::select('id')->get();

            foreach ($permissions as $permissionData) {
                $permission = Permission::updateOrCreate(
                    [
                        'name' => $permissionData['name'],
                        'module_id' => $permissionData['module_id'],
                        'display_name' => $permissionData['display_name'],
                        'is_custom' => $permissionData['is_custom'],
                        'allowed_permissions' => $permissionData['allowed_permissions'],
                    ]
                );

                foreach ($companies as $company) {
                    $role = Role::where('name', 'admin')
                        ->where('company_id', $company->id)
                        ->first();

                    if ($role) {
                        $permissionRole = PermissionRole::where('permission_id', $permission->id)
                            ->where('role_id', $role->id)
                            ->first();

                        $permissionRole = $permissionRole ?: new PermissionRole();
                        $permissionRole->permission_id = $permission->id;
                        $permissionRole->role_id = $role->id;
                        $permissionRole->permission_type_id = 4; // All
                        $permissionRole->save();
                    }
                }

                $adminUsers = User::allAdmins();

                foreach ($adminUsers as $adminUser) {
                    $userPermission = UserPermission::where('user_id', $adminUser->id)->where('permission_id', $permission->id)->first() ?: new UserPermission();
                    $userPermission->user_id = $adminUser->id;
                    $userPermission->permission_id = $permission->id;
                    $userPermission->permission_type_id = 4; // All
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
        $module = Module::where('module_name', 'employees')->first();

        if (!is_null($module)) {
            $permissions = ['view_increment_promotion', 'manage_increment_promotion'];

            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)
                    ->where('module_id', $module->id)->first();

                if ($permission) {
                    PermissionRole::where('permission_id', $permission->id)
                        ->where('permission_type_id', 4)->delete();

                    UserPermission::where('permission_id', $permission->id)
                        ->where('permission_type_id', 4)->delete();

                    $permission->delete();
                }
            }
        }
    }

};
