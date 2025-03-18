<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('
            DELETE p1 FROM user_permissions p1
            INNER JOIN user_permissions p2
            WHERE
                p1.id > p2.id AND
                p1.permission_id = p2.permission_id AND
                p1.user_id = p2.user_id;
        ');

        // Step 2: Add unique constraint
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->unique(['permission_id', 'user_id']);
        });




    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
