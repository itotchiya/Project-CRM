<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;

class UserPermissionController extends AccountBaseController
{

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $userPermission = UserPermission::where('permission_id', $request->permissionId)
            ->where('user_id', $id)
            ->firstOrNew();

        $userPermission->permission_type_id = $request->permissionType;
        $userPermission->user_id = $id;
        $userPermission->permission_id = $request->permissionId;
        $userPermission->save();

        if ($request->permissionCustomised == 1) {
            User::where('id', $id)->update(['customised_permissions' => 1]);
        }

        cache()->forget('sidebar_user_perms_' . $id);

        return Reply::dataOnly(['status' => 'success']);
    }

    public function customPermissions(Request $request, $id)
    {
        $this->employee = User::with('role')->findOrFail($id);

        $roleId = $this->employee->role[0]->role_id;
        $this->role = Role::with('permissions')->findOrFail($roleId);

        $this->modulesData = Module::with('customPermissions')->findOrFail($request->moduleId);

        $html = view('employees.ajax.custom_permissions', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html]);
    }

    public function resetPermissions($userId)
    {
        $user = User::with('roles')->findOrFail($userId);
        $userRoles = $user->roles;


        if (count($userRoles) > 1) {
            $role = $userRoles->where('name', '!=', 'employee')->first();
        }
        else {
            $role = $userRoles->first();
        }

        if (!$role) {
            return Reply::error(__('messages.roleNotFound', ['user' => $user->name]));
        }

        $user->assignUserRolePermission($role->id);

        User::where('id', $userId)->update(['customised_permissions' => 0]);

        cache()->forget('sidebar_user_perms_' . $userId);

        return Reply::dataOnly(['status' => 'success']);

    }

}
