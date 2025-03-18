<?php

use App\Models\User;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
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
        if (!Schema::hasColumn('users', 'country_phonecode')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('country_phonecode')->nullable()->after('image');
            });

            User::with('country')
                ->withoutGlobalScopes([CompanyScope::class, ActiveScope::class])
                ->whereNotNull('country_id')
                ->update(['country_phonecode' => DB::raw('(SELECT phonecode FROM countries WHERE countries.id = users.country_id)')]);
        }

        if (!Schema::hasColumn('global_settings', 'time_format')) {
            Schema::table('global_settings', function (Blueprint $table) {
                $table->after('currency_key_version', function($table){
                    $table->string('date_format', 20)->default('d-m-Y');
                    $table->string('time_format', 20)->default('h:i a');
                });
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('country_phonecode');
        });
    }

};
