<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->enum('over_utilization', ['not_allowed', 'allow_paid', 'allow_unpaid'])->default('not_allowed');
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->boolean('over_utilized')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('over_utilized');
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['over_utilization']);
        });
    }

};
