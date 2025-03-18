<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        $permissionName = [
            'view_client_document',
            'edit_client_document',
            'delete_client_document',
            'view_documents',
            'edit_documents',
            'delete_documents'
        ];

        Permission::whereIn('name', $permissionName)->update([
            'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

};
