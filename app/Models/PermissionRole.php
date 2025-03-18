<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PermissionRole
 *
 * @property int $permission_id
 * @property int $role_id
 * @property int $permission_type_id
 * @property-read mixed $icon
 * @property-read \App\Models\PermissionType $permissionType
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole wherePermissionTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole whereRoleId($value)
 * @property-read \App\Models\Permission $permission
 * @mixin \Eloquent
 */
class PermissionRole extends BaseModel
{

    protected $table = 'permission_role';

    protected $fillable = ['role_id', 'permission_id', 'permission_type_id'];

    public $timestamps = false;

    /**
     * XXXXXXXXXXX
     *
     * @return BelongsTo
     */
    public function permissionType(): BelongsTo
    {
        return $this->belongsTo(PermissionType::class, 'permission_type_id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    public static function employeeRolePermissions()
    {

        $employeePermissionsArray = [
            'view_projects' => PermissionType::OWNED,
            'view_project_files' => PermissionType::ALL,
            'add_project_files' => PermissionType::ALL,
            'edit_project_files' => PermissionType::ADDED,
            'delete_project_files' => PermissionType::ADDED,
            'view_project_members' => PermissionType::ALL,
            'view_project_discussions' => PermissionType::ALL,
            'add_project_discussions' => PermissionType::ALL,
            'edit_project_discussions' => PermissionType::ADDED,
            'delete_project_discussions' => PermissionType::ADDED,
            'view_project_note' => PermissionType::ALL,

            'view_attendance' => PermissionType::OWNED,

            'add_tasks' => PermissionType::ADDED,
            'view_tasks' => PermissionType::BOTH,
            'edit_tasks' => PermissionType::ADDED,
            'delete_tasks' => PermissionType::ADDED,
            'view_project_tasks' => PermissionType::ALL,
            'view_sub_tasks' => PermissionType::ALL,
            'add_sub_tasks' => PermissionType::ALL,
            'edit_sub_tasks' => PermissionType::ADDED,
            'delete_sub_tasks' => PermissionType::ADDED,
            'view_task_files' => PermissionType::ALL,
            'add_task_files' => PermissionType::ALL,
            'edit_task_files' => PermissionType::ADDED,
            'delete_task_files' => PermissionType::ADDED,
            'view_task_comments' => PermissionType::ALL,
            'add_task_comments' => PermissionType::ALL,
            'edit_task_comments' => PermissionType::ADDED,
            'delete_task_comments' => PermissionType::ADDED,
            'view_task_notes' => PermissionType::ALL,
            'add_task_notes' => PermissionType::ALL,
            'edit_task_notes' => PermissionType::ADDED,
            'delete_task_notes' => PermissionType::ADDED,

            'add_timelogs' => PermissionType::ADDED,
            'edit_timelogs' => PermissionType::ADDED,
            'view_timelogs' => PermissionType::BOTH,
            'view_project_timelogs' => PermissionType::ALL,

            'add_tickets' => PermissionType::ADDED,
            'view_tickets' => PermissionType::BOTH,
            'edit_tickets' => PermissionType::BOTH,
            'delete_tickets' => PermissionType::ADDED,

            'view_events' => PermissionType::OWNED,

            'view_notice' => PermissionType::OWNED,

            'add_leave' => PermissionType::ADDED,
            'view_leave' => PermissionType::BOTH,
            'view_leaves_taken' => PermissionType::ALL,

            'add_lead' => PermissionType::ADDED,
            'view_lead' => PermissionType::ALL,
            'edit_lead' => PermissionType::ADDED,
            'view_lead_files' => PermissionType::ADDED,
            'add_lead_files' => PermissionType::ALL,
            'view_lead_follow_up' => PermissionType::ALL,
            'add_lead_follow_up' => PermissionType::ALL,
            'edit_lead_follow_up' => PermissionType::ADDED,
            'delete_lead_follow_up' => PermissionType::ADDED,

            'view_holiday' => PermissionType::OWNED,

            'add_expenses' => PermissionType::ADDED,
            'view_expenses' => PermissionType::BOTH,
            'edit_expenses' => PermissionType::ADDED,
            'delete_expenses' => PermissionType::ADDED,
            'view_appreciation' => PermissionType::OWNED,

            'view_immigration' => PermissionType::OWNED,
            'add_immigration' => PermissionType::OWNED,
            'edit_immigration' => PermissionType::OWNED,
            'delete_immigration' => PermissionType::OWNED,

        ];

        return $employeePermissionsArray;
    }

    public static function clientRolePermissions()
    {

        $clientPermissionsArray = [
            'view_projects' => PermissionType::OWNED,
            'view_project_files' => PermissionType::ALL,
            'add_project_files' => PermissionType::ALL,
            'edit_project_files' => PermissionType::ADDED,
            'delete_project_files' => PermissionType::ADDED,
            'view_project_members' => PermissionType::ALL,
            'view_project_discussions' => PermissionType::ALL,
            'add_project_discussions' => PermissionType::ALL,
            'edit_project_discussions' => PermissionType::ADDED,
            'delete_project_discussions' => PermissionType::ADDED,
            'view_project_note' => PermissionType::ALL,

            'view_tasks' => PermissionType::OWNED,
            'view_project_tasks' => PermissionType::ALL,
            'view_sub_tasks' => PermissionType::ALL,
            'view_task_files' => PermissionType::ALL,
            'view_task_comments' => PermissionType::ALL,
            'add_task_comments' => PermissionType::ALL,
            'edit_task_comments' => PermissionType::ADDED,
            'delete_task_comments' => PermissionType::ADDED,
            'view_task_notes' => PermissionType::ALL,
            'add_task_notes' => PermissionType::ALL,
            'edit_task_notes' => PermissionType::ADDED,
            'delete_task_notes' => PermissionType::ADDED,

            'view_timelogs' => PermissionType::OWNED,
            'view_project_timelogs' => PermissionType::ALL,

            'add_tickets' => PermissionType::ADDED,
            'view_tickets' => PermissionType::BOTH,
            'edit_tickets' => PermissionType::ADDED,
            'delete_tickets' => PermissionType::ADDED,

            'view_events' => PermissionType::OWNED,

            'view_notice' => PermissionType::OWNED,

            'view_estimates' => PermissionType::OWNED,

            'view_invoices' => PermissionType::OWNED,
            'view_project_invoices' => PermissionType::ALL,

            'view_payments' => PermissionType::OWNED,
            'view_project_payments' => PermissionType::ALL,

            'view_product' => PermissionType::ALL,
            'view_contract' => PermissionType::OWNED,
            'add_contract_discussion' => PermissionType::ALL,
            'view_contract_discussion' => PermissionType::ALL,
            'view_contract_files' => PermissionType::ALL,

            'add_order' => PermissionType::ALL,
            'view_order' => PermissionType::OWNED,

        ];

        return $clientPermissionsArray;
    }

    public static function insertModuleRolePermission($moduleName, $companyId)
    {
        $modulePermissions = \App\Models\Module::with('permissionsAll')->where('module_name', $moduleName)->firstOrFail();

        $adminRole = Role::withoutGlobalScope(CompanyScope::class)->with('roleuser', 'roleuser.user.roles')
            ->where('name', 'admin')
            ->where('company_id', $companyId)
            ->first();

        if ($adminRole) {
            PermissionRole::whereHas('permission', function ($query) use ($modulePermissions) {
                $query->where('module_id', $modulePermissions->id);
            })->where('role_id', $adminRole->id)->delete();


            foreach ($modulePermissions->permissionsAll as $permission) {

                PermissionRole::create([
                    'permission_id' => $permission->id,
                    'role_id' => $adminRole->id,
                    'permission_type_id' => PermissionType::ALL
                ]);
            }

            foreach ($adminRole->roleuser as $roleuser) {

                foreach ($modulePermissions->permissionsAll as $permission) {

                    UserPermission::firstOrCreate([
                        'permission_id' => $permission->id,
                        'user_id' => $roleuser->user_id,
                        'permission_type_id' => PermissionType::ALL
                    ]);

                }
            }
        }

        $otherRoles = Role::withoutGlobalScope(CompanyScope::class)->with('roleuser', 'roleuser.user.roles')
            ->where('name', '<>', 'admin')
            ->where('company_id', $companyId)
            ->get();

        foreach ($otherRoles as $key => $role) {

            foreach ($modulePermissions->permissionsAll as $permission) {
                $permissionRole = PermissionRole::where('permission_id', $permission->id)
                    ->where('role_id', $role->id)
                    ->first();

                if (!$permissionRole) {
                    PermissionRole::create([
                        'permission_id' => $permission->id,
                        'role_id' => $role->id,
                        'permission_type_id' => PermissionType::NONE
                    ]);
                }
            }

            foreach ($role->roleuser as $roleuser) {

                foreach ($modulePermissions->permissionsAll as $permission) {

                    $userPermission = UserPermission::where('permission_id', $permission->id)
                        ->where('user_id', $roleuser->user_id)
                        ->first();

                    if (!$userPermission) {
                        UserPermission::create([
                            'permission_id' => $permission->id,
                            'user_id' => $roleuser->user_id,
                            'permission_type_id' => PermissionType::NONE
                        ]);
                    }

                }

            }
        }

    }

}
