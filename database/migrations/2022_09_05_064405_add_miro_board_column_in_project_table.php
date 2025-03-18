<?php

use App\Models\Company;
use App\Models\Module;
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
     *
     * @return void
     */

    public function up()
    {
        if (!Schema::hasColumn('projects', 'enable_miroboard')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->boolean('enable_miroboard')->default(false);
                $table->string('miro_board_id')->nullable();
                $table->boolean('client_access')->default(false);
            });
        }

        $module = Module::where('module_name', 'projects')->first();

        if (!is_null($module)) {
            $permissionName = 'view_miroboard';

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
        Permission::where('name', 'view_miroboard')->delete();

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('enable_miroboard');
            $table->dropColumn('miro_board_id');
            $table->dropColumn('client_access');
        });
    }

};
