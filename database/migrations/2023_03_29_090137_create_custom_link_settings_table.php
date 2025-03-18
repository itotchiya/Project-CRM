<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Module;
use App\Models\Company;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('custom_link_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->string('link_title');
            $table->text('url');
            $table->string('can_be_viewed_by')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        $module = Module::where('module_name', 'settings')->first();
        $count = Company::withoutGlobalScope(\App\Scopes\ActiveScope::class)->count();

        if (!is_null($module) && $count > 0) {
            $permissionType = [
                    'module_id' => $module->id,
                    'display_name' => 'Manage Custom Link Setting',
                    'name' => 'manage_custom_link_setting',
                    'is_custom' => 1,
                    'allowed_permissions' => Permission::ALL_NONE,
                ];

            $permission = Permission::where('name', $permissionType['name'])->first() ?: new Permission();
            $permission->name = $permissionType['name'];
            $permission->display_name = $permissionType['display_name'];
            $permission->module_id = $module->id;
            $permission->is_custom = $permissionType['is_custom'];
            $permission->allowed_permissions = $permissionType['allowed_permissions'];
            $permission->save();

            $companies = Company::select('id')->get();

            foreach($companies as $company){
                $role = Role::where('name', 'admin')->where('company_id', $company->id)->first();

                $permissionRole = PermissionRole::where('permission_id', $permission->id)->where('role_id', $role->id)->first() ?: new PermissionRole();
                $permissionRole->permission_id = $permission->id;
                $permissionRole->role_id = $role->id;
                $permissionRole->permission_type_id = 4;
                $permissionRole->save();

                $admins = User::allAdmins($company->id);

                foreach($admins as $admin) {
                    $userPermission = UserPermission::where('permission_id', $permission->id)->where('user_id', $admin->id)->first() ?: new UserPermission();
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
        Schema::dropIfExists('custom_link_settings');
    }

};
