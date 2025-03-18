<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Module;
use App\Models\Company;
use App\Models\Permission;
use App\Models\PermissionRole;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\UserPermission;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {

        $module = Module::where('module_name', 'employees')->first();

        if(!is_null($module)){
            $permissionType = [
                [
                    'module_id' => $module->id,
                    'display_name' => 'Add Immigration',
                    'name' => 'add_immigration',
                    'is_custom' => 1,
                    'allowed_permissions' => Permission::ALL_4_OWNED_2_NONE_5,
                ],


                [
                    'module_id' => $module->id,
                    'display_name' => 'View Immigration',
                    'name' => 'view_immigration',
                    'is_custom' => 1,
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                ],

                [
                    'module_id' => $module->id,
                    'display_name' => 'Edit Immigration',
                    'name' => 'edit_immigration',
                    'is_custom' => 1,
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                ],

                [
                    'module_id' => $module->id,
                    'display_name' => 'Delete Immigration',
                    'name' => 'delete_immigration',
                    'is_custom' => 1,
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                ],
            ];

            $companies = Company::select('id')->get();

            foreach($permissionType as $key => $permissionTypes)
            {
                $permission = new Permission();
                $permission->name = $permissionTypes['name'];
                $permission->display_name = $permissionTypes['display_name'];
                $permission->module_id = $module->id;
                $permission->is_custom = $permissionTypes['is_custom'];
                $permission->allowed_permissions = $permissionTypes['allowed_permissions'];
                $permission->save();

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

        Schema::create('passport_details', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('country_id')->nullable()->index('passport_details_country_id_foreign');
            $table->foreign(['country_id'])->references(['id'])->on('countries')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
            $table->string('passport_number');
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->string('file')->nullable();
            $table->timestamps();
        });

        Schema::create('visa_details', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('country_id')->nullable()->index('visa_details_country_id_foreign');
            $table->foreign(['country_id'])->references(['id'])->on('countries')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
            $table->string('visa_number');
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->string('file')->nullable();
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('passport_details');
        Schema::dropIfExists('visa_details');
    }

};
