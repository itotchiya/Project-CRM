<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeePermissionSeeder extends Seeder
{

    protected array $permissionTypes = [
        'added' => 1,
        'owned' => 2,
        'both' => 3,
        'all' => 4,
        'none' => 5
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $this->insertUserRolePermission($companyId);
    }

    public function insertUserRolePermission($companyId)
    {
        DB::beginTransaction();
        // Employee role
        $employeeRole = Role::with('roleuser', 'roleuser.user.roles')
            ->where('name', 'employee')
            ->where('company_id', $companyId)
            ->first();

        $allPermissions = Permission::all();

        $this->permissionRole($allPermissions, 'employee', $companyId);

        // Employee permissions will be synced via cron
        $userIds = $employeeRole->roleuser->pluck('user_id');
        try {
            User::whereIn('id', $userIds)->update(['permission_sync' => 0]);
        } catch (\Exception $exception) {
            Log::info($exception);
        }

        // Admin role
        $adminRole = Role::with('roleuser', 'roleuser.user.roles')
            ->where('name', 'admin')
            ->where('company_id', $companyId)
            ->first();

        PermissionRole::where('role_id', $adminRole->id)->delete();

        $this->rolePermissionInsert($allPermissions, $adminRole->id, 'all');

        foreach ($adminRole->roleuser as $roleuser) {
            try {
                $roleuser->user->assignUserRolePermission($adminRole->id);
            } catch (\Exception $e) {
                echo($e->getMessage());
            }
        }

        // Client role
        $this->permissionRole($allPermissions, 'client', $companyId);

        DB::commit();
    }

    public function rolePermissionInsert($allPermissions, $roleId, $permissionType = 'none')
    {
        $data = [];

        foreach ($allPermissions as $permission) {
            $data[] = [
                'permission_id' => $permission->id,
                'role_id' => $roleId,
                'permission_type_id' => $this->permissionTypes[$permissionType],
            ];
        }

        foreach (array_chunk($data, 100) as $item) {
            PermissionRole::insert($item);
        }

    }

    public function permissionRole($allPermissions, $type, $companyId)
    {
        $role = Role::with('roleuser', 'roleuser.user.roles')
            ->where('name', $type)
            ->where('company_id', $companyId)
            ->first();

        PermissionRole::where('role_id', $role->id)->delete();

        $this->rolePermissionInsert($allPermissions, $role->id);

        $permissionArray = [];

        if ($type === 'client') {
            $permissionArray = PermissionRole::clientRolePermissions();

        }
        elseif ($type === 'employee') {
            $permissionArray = PermissionRole::employeeRolePermissions();

        }

        $permissionArrayKeys = array_keys($permissionArray);

        $permissions = Permission::whereIn('name', $permissionArrayKeys)->get();

        foreach ($permissions as $ep) {

            PermissionRole::where('permission_id', $ep->id)
                ->where('role_id', $role->id)
                ->update([
                    'permission_type_id' => $permissionArray[$ep->name]
                ]);
        }

        if ($type === 'client') {
            foreach ($role->roleuser as $roleuser) {
                $roleuser->user->assignUserRolePermission($role->id);
            }
        }
    }

}
