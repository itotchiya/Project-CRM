<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;
use App\Models\PermissionRole;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\DashboardWidget;
use App\Models\Module;
use App\Models\AwardIcon;
use App\Models\Company;
use App\Models\EmailNotificationSetting;

return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('award_icons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('icon');
            $table->timestamps();
        });

        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('title');
            $table->unsignedBigInteger('award_icon_id')->nullable()->default(null);
            $table->foreign('award_icon_id')->references('id')
                ->on('award_icons')->onDelete('cascade')->onUpdate('cascade');
            $table->text('summary')->nullable()->default(null);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('color_code');
            $table->timestamps();
        });

        Schema::create('appreciations', function (Blueprint $table) {
            $table->id();

            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('award_id');
            $table->foreign('award_id')->references('id')->on('awards')->onDelete('cascade')->onUpdate('cascade');

            $table->integer('award_to')->unsigned();
            $table->foreign('award_to')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->date('award_date');
            $table->string('image')->nullable()->default(null);
            $table->text('summary')->nullable()->default(null);
            $table->integer('added_by')->unsigned();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });

        $module = Module::where('module_name', 'employees')->first();

        if(!is_null($module)){

            $permissions = [
                [
                    'name' => 'add_appreciation',
                    'display' => 'Add Appreciation',
                    'allowed_permission' => Permission::ALL_NONE
                ],
                [
                    'name' => 'view_appreciation',
                    'display' => 'View Appreciation',
                    'allowed_permission' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5
                ],
                [
                    'name' => 'edit_appreciation',
                    'display' => 'Edit Appreciation',
                    'allowed_permission' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5
                ],
                [
                    'name' => 'delete_appreciation',
                    'display' => 'Delete Appreciation',
                    'allowed_permission' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5
                ],
                ['name' => 'manage_award',
                    'display' => 'Manage Award',
                    'allowed_permission' => Permission::ALL_NONE
                ]
            ];


            $companies = Company::select('id')->get();

            foreach($permissions as $permissionData){
                $permission = new Permission();
                $permission->name = $permissionData['name'];
                $permission->display_name = $permissionData['display'];
                $permission->description = null;
                $permission->module_id = $module->id;
                $permission->is_custom = 1;
                $permission->allowed_permissions = $permissionData['allowed_permission'];
                $permission->save();


                foreach ($companies as $company)
                {
                    $role = Role::where('name', 'admin')->where('company_id', $company->id)->first();

                    $permissionRole = new PermissionRole();
                    $permissionRole->permission_id = $permission->id;
                    $permissionRole->role_id = $role->id;
                    $permissionRole->permission_type_id = 4;
                    $permissionRole->save();

                    $adminUser = User::withOut('clientDetails')->withRole('admin')->get();

                    foreach($adminUser as $adminUsers) {
                        $userPermission = new UserPermission();
                        $userPermission->user_id = $adminUsers->id;
                        $userPermission->permission_id = $permission->id;
                        $userPermission->permission_type_id = 4;
                        $userPermission->save();
                    }

                    if($permissionData['name'] == 'view_appreciation')
                    {
                        $admins = $adminUser->pluck('id')->toArray();

                        $employeeUsers = User::withOut('clientDetails')->withRole('employee')->whereNotIn('id', $admins)->get();

                        foreach($employeeUsers as $employeeUser) {
                            $userPermission = new UserPermission();
                            $userPermission->user_id = $employeeUser->id;
                            $userPermission->permission_id = $permission->id;
                            $userPermission->permission_type_id = 2;
                            $userPermission->save();
                        }
                    }
                }

            }

            foreach ($companies as $company)
            {

                $widget = [
                    'widget_name' => 'appreciation',
                    'status' => 1,
                    'dashboard_type' => 'private-dashboard',
                    'company_id' => $company->id
                ];
                DashboardWidget::create($widget);

                $notification = [
                    'send_email' => 'yes',
                    'send_push' => 'no',
                    'send_slack' => 'no',
                    'setting_name' => 'Employee Appreciation',
                    'slug' => 'appreciation-notification',
                    'company_id' => $company->id,
                ];
                EmailNotificationSetting::firstOrCreate($notification);

            }

            $icons = [
                ['title' => 'Trophy', 'icon' => 'trophy'],
                ['title' => 'Thumbs Up', 'icon' => 'hand-thumbs-up'],
                ['title' => 'Award', 'icon' => 'award'],
                ['title' => 'Book', 'icon' => 'book'],
                ['title' => 'Gift', 'icon' => 'gift'],
                ['title' => 'Watch', 'icon' => 'watch'],
                ['title' => 'Cup', 'icon' => 'cup-hot'],
                ['title' => 'Puzzle', 'icon' => 'puzzle'],
                ['title' => 'Plane', 'icon' => 'airplane'],
                ['title' => 'Money', 'icon' => 'piggy-bank'],
            ];

            foreach ($icons as $icon) {
                AwardIcon::create($icon);
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
        Schema::dropIfExists('appreciations');
        Schema::dropIfExists('user_appreciations');

        $permissions = ['add_appreciation', 'view_appreciation', 'edit_appreciation', 'delete_appreciation', 'manage_appriciation_type'];
        Permission::whereIn('name', $permissions)->delete();
        Schema::dropIfExists('appreciations_likes');
        DashboardWidget::where('widget_name', 'appreciation')->delete();
        EmailNotificationSetting::where('slug', 'appreciation-notification')->delete();
        Schema::dropIfExists('appreciation_icons');
    }

};
