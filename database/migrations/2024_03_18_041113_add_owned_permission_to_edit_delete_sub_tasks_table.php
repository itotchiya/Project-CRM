<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Permission::whereIn('name', ['edit_sub_tasks', 'delete_sub_tasks'])->update(['allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}']);
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Permission::whereIn('name', ['edit_sub_tasks', 'delete_sub_tasks'])
            ->update(['allowed_permissions' => '{"all":4, "added":1, "none":5}']);
    }

};
