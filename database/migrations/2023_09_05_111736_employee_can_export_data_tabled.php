<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        if (!Schema::hasColumn('global_settings', 'sign_up_terms')) {
            Schema::table('global_settings', function (Blueprint $table) {
                $table->enum('sign_up_terms', ['yes', 'no'])->default('no')->after('show_update_popup');
                $table->text('terms_link')->nullable()->after('show_update_popup');
            });
        }

        if (!Schema::hasColumn('companies', 'employee_can_export_data')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->boolean('employee_can_export_data')->default(true);
            });
        }
    }

};
