<?php

use App\Models\Order;
use App\Models\Company;
use App\Models\UnitType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        $table = 'orders';

        if (!Schema::hasColumn($table, 'unit_id')) {
            Schema::table($table, function (Blueprint $table) {
                $table->bigInteger('unit_id')->unsigned()->nullable()->default(null);
                $table->foreign('unit_id')
                    ->references('id')
                    ->on('unit_types')
                    ->onDelete('SET NULL')
                    ->onUpdate('cascade');
            });
        }

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {

            $unitData = UnitType::where('company_id', $company->id)->first();

            Order::where('company_id', $company->id)
                ->whereNull('unit_id')
                ->update(['unit_id' => $unitData->id]);

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('unit_id');
        });
    }

};
