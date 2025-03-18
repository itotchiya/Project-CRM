<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Module;
use App\Models\Company;
use App\Models\EstimateRequest;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $module = Module::firstOrCreate(['module_name' => 'estimates']);
        $permissions = [
            [
                'module_id' => $module->id,
                'name' => 'add_estimate_request',
                'allowed_permissions' => Permission::ALL_NONE,
                'is_custom' => 1
            ],
            [
                'module_id' => $module->id,
                'name' => 'view_estimate_request',
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                'is_custom' => 1
            ],
            [
                'module_id' => $module->id,
                'name' => 'edit_estimate_request',
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                'is_custom' => 1
            ],
            [
                'module_id' => $module->id,
                'name' => 'delete_estimate_request',
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                'is_custom' => 1
            ],
            [
                'module_id' => $module->id,
                'name' => 'reject_estimate_request',
                'allowed_permissions' => Permission::ALL_NONE,
                'is_custom' => 1
            ],
        ];

        foreach ($permissions as $permissionData) {
            $permission = Permission::updateOrCreate(
                [
                    'name' => $permissionData['name'],
                    'module_id' => $permissionData['module_id'],
                ],
                [
                    'display_name' => ucwords(str_replace('_', ' ', $permissionData['name'])),
                    'is_custom' => $permissionData['is_custom'],
                    'allowed_permissions' => $permissionData['allowed_permissions'],
                ]
            );
            $companies = Company::all();

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

        if (!Schema::hasColumn('estimate_requests', 'estimate_request_number')) {
            Schema::table('estimate_requests', function ($table) {
                $table->string('estimate_request_number')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('estimate_requests', 'original_request_number')) {
            Schema::table('estimate_requests', function ($table) {
                $table->string('original_request_number')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('estimate_requests', 'added_by')) {
            Schema::table('estimate_requests', function ($table) {
                $table->unsignedInteger('added_by')->nullable();
                $table->foreign('added_by')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
            });
        }

        if (!Schema::hasColumn('estimates', 'estimate_request_id')) {
            Schema::table('estimates', function ($table) {
                $table->bigInteger('estimate_request_id')->unsigned()->nullable();
                $table->foreign('estimate_request_id')->references('id')->on('estimate_requests')->onDelete('cascade')->onUpdate('cascade');
            });
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
