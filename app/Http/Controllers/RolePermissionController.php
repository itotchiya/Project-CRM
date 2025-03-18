<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Role\StoreRole;
use App\Models\Module;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\PermissionType;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use const _PHPStan_7961f7ae1\__;

class RolePermissionController extends AccountBaseController
{

    protected array $permissionTypes = [
        'added' => 1,
        'owned' => 2,
        'both' => 3,
        'all' => 4,
        'none' => 5
    ];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.rolesPermission';
        $this->activeSettingMenu = 'role_permissions';
    }

    public function index()
    {
        abort_403(user()->permission('manage_role_permission_setting') != 'all');

        $this->roles = Role::withCount('users')
            ->orderBy('id', 'asc')
            ->get();

        $otherRoleUserCount = 0;

        foreach ($this->roles->whereNotIn('name', ['employee', 'client']) as $role) {
            $otherRoleUserCount += $role->users_count;
        }

        $this->otherRoleUserCount = $otherRoleUserCount;

        $this->totalPermissions = Permission::count();

        return view('role-permissions.index', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
        abort_403(user()->permission('manage_role_permission_setting') != 'all');

        $this->roles = Role::withCount('unsyncedUsers')->get();

        return view('role-permissions.ajax.create', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(user()->permission('manage_role_permission_setting') != 'all');

        $permissionType = $request->permissionType;

        abort_if($permissionType == '', 404);

        $roleId = $request->roleId;
        $permissionId = $request->permissionId;


        $role = Role::with('users', 'users.role')->findOrFail($roleId);

        // Update role's permission
        $permissionRole = PermissionRole::where('permission_id', $permissionId)
            ->where('role_id', $roleId)
            ->first();

        if ($permissionRole) {
            $permissionRole = PermissionRole::where('permission_id', $permissionId)
                ->where('role_id', $roleId)
                ->update(['permission_type_id' => $permissionType]);

        }
        else {
            $permissionRole = new PermissionRole();
            $permissionRole->permission_id = $permissionId;
            $permissionRole->role_id = $roleId;
            $permissionRole->permission_type_id = $permissionType;
            $permissionRole->save();

        }

        // Prepare user permissions for bulk insert
        $userPermissions = [];
        foreach ($role->users as $roleuser) {
            if (($role->name == 'employee' && count($roleuser->role) == 1) || $role->name != 'employee') {
                if ($roleuser->customised_permissions == 0) {
                    $userPermissions[] = [
                        'permission_id' => $permissionId,
                        'user_id' => $roleuser->id,
                        'permission_type_id' => $permissionType
                    ];
                }

            }

            cache()->forget('sidebar_user_perms_' . $roleuser->id);
        }

        // Perform bulk insert or update for user permissions
        if (!empty($userPermissions)) {
            UserPermission::upsert(
                $userPermissions,
                ['permission_id', 'user_id'],
                ['permission_type_id']
            );
        }

        \Illuminate\Support\Facades\Artisan::call('cache:clear');

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function permissions()
    {
        $roleId = request('roleId');
        $this->role = Role::with('permissions')->where('name', '<>', 'admin')->findOrFail($roleId);

        if ($this->role->name == 'client') {
            $clientModules = ModuleSetting::where('type', 'client')->get()->pluck('module_name');
            $this->modulesData = Module::with('permissions')->withCount('customPermissions')
                ->whereIn('module_name', $clientModules)->where('module_name', '<>', 'messages')->get();

        }
        else {
            $this->modulesData = Module::with('permissions')->where('module_name', '<>', 'messages')->withCount('customPermissions')->get();
        }

            $role = in_array($this->role->name,['employee','client']) ? $this->role->name : 'employee';
            $this->employeeModules = array_merge(
                ModuleSetting::where('module_name', '<>', 'settings')
                                ->where('status', 'active')
                                ->where('type', $role)
                                ->pluck('module_name')
                                ->toArray(),
                ['settings', 'dashboards']
            );

        $html = view('role-permissions.ajax.permissions', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function updateUserPermissions($roleId, $userId)
    {
        $rolePermissions = PermissionRole::where('role_id', $roleId)->get();

        foreach ($rolePermissions as $key => $value) {
            UserPermission::where('permission_id', $value->permission_id)
                ->where('user_id', $userId)
                ->update(['permission_type_id' => $value->permission_type_id]);
        }

        cache()->forget('sidebar_user_perms_' . $userId);

        return Reply::dataOnly(['status' => 'success']);
    }

    public function storeRole(StoreRole $request)
    {
        abort_403(user()->permission('manage_role_permission_setting') != 'all');

        $role = new Role();
        $role->name = $request->name;
        $role->display_name = $request->name;
        $role->save();

        if ($request->import_from_role != '') {
            $importRolePermissions = PermissionRole::where('role_id', $request->import_from_role)->get();

            if (count($importRolePermissions) == 0) {
                return Reply::error(__('messages.noRoleFound'));
            }

            foreach ($importRolePermissions as $perm) {
                $perm->replicate()->fill([
                    'role_id' => $role->id
                ])->save();
            }

        }
        else {
            $allPermissions = Permission::all();
            $role->perms()->sync([]);
            $role->attachPermissions($allPermissions);
        }

        return Reply::success(__('messages.recordSaved'));
    }

    public function deleteRole(Request $request)
    {
        Role::whereId($request->roleId)->delete();

        return Reply::dataOnly(['status' => 'success']);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function customPermissions(Request $request)
    {
        $moduleId = $request->moduleId;
        $roleId = request('roleId');
        $this->role = Role::with('permissions')->findOrFail($roleId);
        $this->modulesData = Module::with('customPermissions')->findOrFail($moduleId);
        $html = view('role-permissions.ajax.custom_permissions', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html]);
    }

    public function resetPermissions()
    {
        $role = Role::with('roleuser', 'roleuser.user.roles')->findOrFail(request('roleId'));
        $allPermissions = Permission::all();

        PermissionRole::where('role_id', $role->id)->delete();

        switch ($role->name) {
        case 'employee':
            $rolePermissionsArray = PermissionRole::employeeRolePermissions();
            break;

        case 'client':
            $rolePermissionsArray = PermissionRole::clientRolePermissions();
            break;

        default:
            return Reply::error(__('messages.permissionDenied'));
        }

        $this->permissionrole($allPermissions, $role->name, $role->company_id);

        $userIds = $role->roleuser->pluck('user_id');

        User::whereIn('id', $userIds)->update(['permission_sync' => 0]);

        return Reply::success(__('messages.recordSaved'));

    }

    public function update(Request $request, $id)
    {
        Role::where('id', $id)->update(['display_name' => $request->role_name]);
    }

    public function addMissingAdminPermission($companyId = null)
    {
        $adminRole = Role::where('name', 'admin')->where('company_id', $companyId)->first();

        if (!$adminRole) {
            return true;
        }

        $adminPermission = PermissionRole::where('role_id', $adminRole->id)->pluck('permission_id')->toArray();

        $allTypePermisison = PermissionType::where('name', 'all')->first();
        $missingPermissions = Permission::select('id')->whereNotIn('id', $adminPermission)->get();

        $data = [];

        foreach ($missingPermissions as $permission) {
            $data[] = [
                'permission_id' => $permission->id,
                'role_id' => $adminRole->id,
                'permission_type_id' => $allTypePermisison->id,
            ];
        }

        foreach (array_chunk($data, 100) as $item) {
            PermissionRole::insert($item);
        }

        if (count($missingPermissions) > 0) {
            $this->addMissingAdminUserPermission($adminRole->id);
        }


    }

    public function addMissingEmployeePermission($companyId)
    {
        $roles = Role::where('name', '<>', 'admin')->where('company_id', $companyId)->get();

        foreach ($roles as $employeeRole) {
            $employeePermission = PermissionRole::where('role_id', $employeeRole->id)->pluck('permission_id')->toArray();
            $noneTypePermisison = PermissionType::where('name', 'none')->first();
            $missingPermissions = Permission::select('id')->whereNotIn('id', $employeePermission)->get();

            $data = [];

            foreach ($missingPermissions as $permission) {
                $data[] = [
                    'permission_id' => $permission->id,
                    'role_id' => $employeeRole->id,
                    'permission_type_id' => $noneTypePermisison->id,
                ];
            }

            foreach (array_chunk($data, 100) as $item) {
                PermissionRole::insert($item);
            }

            if (count($missingPermissions) > 0) {
                $this->addMissingUserPermission($employeeRole->id);
            }

        }

    }

    public function addMissingAdminUserPermission($roleId)
    {

        $role = Role::withCount('permissions')->findOrFail($roleId);
        $users = $role->users;

        foreach ($users as $user) {
            $user->assignUserRolePermission($roleId);
        }
    }

    public function addMissingUserPermission($roleId)
    {
        $role = Role::withCount('permissions')->findOrFail($roleId);
        $users = $role->users;

        foreach ($users as $user) {
            $userRole = $user->roles->pluck('name')->toArray();

            if (!in_array('admin', $userRole)) {
                $user->assignUserRolePermission($roleId);
            }
        }
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

        PermissionRole::whereIn('permission_id', $permissions->pluck('id')->toArray())
            ->where('role_id', $role->id)
            ->delete();

        $updatePermissionArray = [];

        foreach ($permissions as $permission) {
            $updatePermissionArray[] = ['permission_id' => $permission->id, 'role_id' => $role->id, 'permission_type_id' => $permissionArray[$permission->name]];
        }

        PermissionRole::insert($updatePermissionArray);

        if ($type === 'client') {
            foreach ($role->roleuser as $roleuser) {
                $roleuser->user->assignUserRolePermission($role->id);
            }
        }
    }

}
