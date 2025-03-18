<?php
namespace App;

use App\Models\Contract;
use App\Models\Company;
use App\Models\Permission;
use App\Models\PermissionType;
use App\Models\RoleUser;
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
        Company::renameOrganisationTableToCompanyTable();

        Schema::create('contract_templates', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->string('subject');
            $table->longText('description')->nullable();
            $table->string('amount');
            $table->unsignedBigInteger('contract_type_id');
            $table->foreign('contract_type_id')->references('id')->on('contract_types')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('currency_id')->unsigned()->nullable();
            $table->longText('contract_detail')->nullable();
            $table->foreign('currency_id')->references('id')
                ->on('currencies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->integer('added_by')->default(1);
            $table->timestamps();
        });


        if(Company::first()) {
            /** @phpstan-ignore-next-line */
            Contract::query()->update(['currency_id' => Company::first()->currency_id]);


            $contractModule = \App\Models\Module::firstOrCreate(['module_name' => 'contracts']);

            $admins = RoleUser::join('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('name', 'admin')
                ->get();

            $allTypePermission = PermissionType::ofType('all')->first();

            $perm = Permission::firstOrCreate([
                'name' => 'manage_contract_template',
                'display_name' => ucwords(str_replace('_', ' ', 'manage_contract_template')),
                'is_custom' => 1,
                'module_id' => $contractModule->id,
                'allowed_permissions' => Permission::ALL_ADDED_NONE
            ]);

            foreach ($admins as $item) {
                UserPermission::firstOrCreate(
                    [
                        'user_id' => $item->user_id,
                        'permission_id' => $perm->id,
                        'permission_type_id' => $allTypePermission->id ?? PermissionType::ALL
                    ]
                );
            }

            $proposalModule = \App\Models\Module::firstOrCreate(['module_name' => 'leads']);

            $perm = Permission::firstOrCreate([
                'name' => 'manage_proposal_template',
                'display_name' => ucwords(str_replace('_', ' ', 'manage_proposal_template')),
                'is_custom' => 1,
                'module_id' => $proposalModule->id,
                'allowed_permissions' => Permission::ALL_ADDED_NONE
            ]);

            foreach ($admins as $item) {
                UserPermission::firstOrCreate(
                    [
                        'user_id' => $item->user_id,
                        'permission_id' => $perm->id,
                        'permission_type_id' => $allTypePermission->id ?? PermissionType::ALL
                    ]
                );
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

    }

};
