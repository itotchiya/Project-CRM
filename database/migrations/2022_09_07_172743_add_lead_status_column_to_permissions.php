<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionType;
use App\Models\RoleUser;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {

        $count = Company::count();

        if ($count > 0) {
            $leadModule = Module::firstOrCreate(['module_name' => 'leads']);
            $allTypePermission = PermissionType::ofType('all')->first();

            $perm = Permission::firstOrCreate([
                'name' => 'manage_lead_status',
                'display_name' => ucwords(str_replace('_', ' ', 'manage_lead_status')),
                'is_custom' => 1,
                'module_id' => $leadModule->id,
                'allowed_permissions' => Permission::ALL_NONE
            ]);


            $admins = RoleUser::join('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('name', 'admin')
                ->get();

            foreach ($admins as $item) {
                UserPermission::firstOrCreate(
                    [
                        'user_id' => $item->user_id,
                        'permission_id' => $perm->id,
                        'permission_type_id' => $allTypePermission->id ?? PermissionType::ALL
                    ]
                );
            }

            $invoiceModule = Module::where('module_name', 'invoices')->first();

            if (!is_null($invoiceModule)) {
                $allTypePermission = PermissionType::ofType('all')->first();

                $perm = Permission::firstOrCreate([
                    'name' => 'manage_recurring_invoice',
                    'display_name' => ucwords(str_replace('_', ' ', 'manage_recurring_invoice')),
                    'is_custom' => 1,
                    'module_id' => $invoiceModule->id,
                    'allowed_permissions' => Permission::ALL_NONE
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
