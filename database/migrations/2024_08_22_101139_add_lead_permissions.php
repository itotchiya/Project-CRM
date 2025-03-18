<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // update to all and none permission
        $permission = Permission::where('name', 'view_lead')->first();

        if ($permission) {
            $permission->allowed_permissions = Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5;
            $permission->save();
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
