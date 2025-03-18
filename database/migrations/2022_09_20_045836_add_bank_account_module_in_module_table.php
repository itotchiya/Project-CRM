<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('type')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_type')->nullable();
            $table->integer('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->string('contact_number')->nullable();
            $table->double('opening_balance', 15, 2)->nullable();
            $table->string('bank_logo')->nullable();
            $table->boolean('status')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->integer('last_updated_by')->unsigned()->nullable();
            $table->foreign('last_updated_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->double('bank_balance', 16, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->integer('bank_account_id')->unsigned()->nullable();
            $table->foreign('bank_account_id')
                ->references('id')
                ->on('bank_accounts')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->integer('payment_id')->unsigned()->nullable();
            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->integer('invoice_id')->unsigned()->nullable();
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->integer('expense_id')->unsigned()->nullable();
            $table->foreign('expense_id')
                ->references('id')
                ->on('expenses')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->double('amount', 15, 2)->nullable();
            $table->enum('type', ['Cr', 'Dr'])->default('Cr');
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->integer('last_updated_by')->unsigned()->nullable();
            $table->foreign('last_updated_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->text('memo')->nullable();
            $table->string('transaction_relation')->nullable();
            $table->string('transaction_related_to')->nullable();
            $table->text('title')->nullable();
            $table->date('transaction_date')->nullable();
            $table->double('bank_balance', 16, 2)->nullable();
            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->integer('bank_account_id')->unsigned()->nullable();
            $table->foreign('bank_account_id')
                ->references('id')
                ->on('bank_accounts')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->integer('bank_account_id')->unsigned()->nullable();
            $table->foreign('bank_account_id')
                ->references('id')
                ->on('bank_accounts')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('bank_account_id')->unsigned()->nullable();
            $table->foreign('bank_account_id')
                ->references('id')
                ->on('bank_accounts')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        $count = Company::count();

        if ($count > 0) {

            $types = ['admin', 'employee'];

            $permissionType = [
                [
                    'name' => 'add_bankaccount',
                    'display_name' => 'Add Bankaccount',
                    'allowed_permission' => Permission::ALL_NONE,
                    'is_custom' => 0
                ],
                [
                    'name' => 'view_bankaccount',
                    'display_name' => 'View Bankaccount',
                    'allowed_permission' => Permission::ALL_4_ADDED_1_NONE_5,
                    'is_custom' => 0
                ],
                [
                    'name' => 'edit_bankaccount',
                    'display_name' => 'Edit Bankaccount',
                    'allowed_permission' => Permission::ALL_4_ADDED_1_NONE_5,
                    'is_custom' => 0
                ],
                [
                    'name' => 'delete_bankaccount',
                    'display_name' => 'Delete Bankaccount',
                    'allowed_permission' => Permission::ALL_4_ADDED_1_NONE_5,
                    'is_custom' => 0
                ],
                [
                    'name' => 'add_bank_transfer',
                    'display_name' => 'Add Bank Transfer',
                    'allowed_permission' => Permission::ALL_NONE,
                    'is_custom' => 1,
                ],
                [
                    'name' => 'add_bank_deposit',
                    'display_name' => 'Add Bank Deposit',
                    'allowed_permission' => Permission::ALL_NONE,
                    'is_custom' => 1,
                ],
                [
                    'name' => 'add_bank_withdraw',
                    'display_name' => 'Add Bank Withdraw',
                    'allowed_permission' => Permission::ALL_NONE,
                    'is_custom' => 1,
                ],
            ];

            $module = new Module();
            $module->module_name = 'bankaccount';
            $module->description = null;
            $module->save();

            $companies = Company::select('id')->get();

            foreach($companies as $company){

                foreach($types as $type){
                    $moduleSetting = new ModuleSetting();
                    $moduleSetting->company_id = $company->id;
                    $moduleSetting->module_name = 'bankaccount';
                    $moduleSetting->status = 'active';
                    $moduleSetting->type = $type;
                    $moduleSetting->save();
                }
            }

            foreach($permissionType as $key => $permissionTypes)
            {
                $permission = new Permission();
                $permission->name = $permissionTypes['name'];
                $permission->display_name = $permissionTypes['display_name'];
                $permission->module_id = $module->id;
                $permission->is_custom = $permissionTypes['is_custom'];
                $permission->allowed_permissions = $permissionTypes['allowed_permission'];
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

            $bankRelatedModules = Module::select('id', 'module_name')->whereIn('module_name', ['payments', 'expenses', 'invoices'])->get();

            $modulePermissions = [
                [
                    'name' => 'link_payment_bank_account',
                    'display_name' => 'Link Payment Bank Account',
                    'allowed_permission' => Permission::ALL_NONE
                ],
                [
                    'name' => 'link_expense_bank_account',
                    'display_name' => 'Link Expense Bank Account',
                    'allowed_permission' => Permission::ALL_NONE
                ],
                [
                    'name' => 'link_invoice_bank_account',
                    'display_name' => 'Link Invoice Bank Account',
                    'allowed_permission' => Permission::ALL_NONE
                ],
            ];

            foreach($bankRelatedModules as $bankRelatedModule)
            {
                if($bankRelatedModule->module_name == 'payments'){
                    $modulePermission = $modulePermissions[0];
                }

                elseif($bankRelatedModule->module_name == 'expenses'){
                    $modulePermission = $modulePermissions[1];
                }

                else{
                    $modulePermission = $modulePermissions[2];
                }

                $permission = new Permission();
                $permission->name = $modulePermission['name'];
                $permission->display_name = $modulePermission['display_name'];
                $permission->module_id = $bankRelatedModule->id;
                $permission->is_custom = 1;
                $permission->allowed_permissions = $modulePermission['allowed_permission'];
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('payments_bank_account_id_foreign');
            $table->dropColumn('bank_account_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign('expenses_bank_account_id_foreign');
            $table->dropColumn('bank_account_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign('invoices_bank_account_id_foreign');
            $table->dropColumn('bank_account_id');
        });

        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_accounts');

        Module::where('module_name', 'bankaccount')->delete();
        Permission::where('name', 'link_payment_bank_account')->delete();
        Permission::where('name', 'link_expense_bank_account')->delete();
        Permission::where('name', 'link_invoice_bank_account')->delete();

        $moduleSettings = ModuleSetting::where('module_name', 'bankaccount')->get();

        foreach($moduleSettings as $moduleSetting){
            $moduleSetting->delete();
        }
    }

};
