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
        if (!Schema::hasTable('lead_setting')) {
            Schema::create('lead_setting', function (Blueprint $table) {
                $table->id();
                $table->boolean('status')->default(false);
                $table->unsignedInteger('user_id')->index('lead_setting_user_id_foreign');
                $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->integer('company_id')->unsigned()->nullable();
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('user_permissions', 'id')) {
            //            Schema::table('user_permissions', function (Blueprint $table) {
            //                $table->dropColumn('id');
            //                $table->dropColumn('created_at');
            //                $table->dropColumn('updated_at');
            //            });
            //            session()->forget('sidebar_user_perms');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_setting');
    }

};
