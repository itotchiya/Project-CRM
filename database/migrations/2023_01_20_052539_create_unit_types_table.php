<?php

use App\Models\Company;
use App\Models\Estimate;
use App\Models\EstimateTemplate;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Proposal;
use App\Models\ProposalTemplate;
use App\Models\RecurringInvoice;
use App\Models\UnitType;
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
        if (!Schema::hasTable('unit_types')) {
            Schema::create('unit_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('company_id')->unsigned()->nullable();
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
                $table->string('unit_type');
                $table->boolean('default')->default(0);
                $table->timestamps();
            });
        }

        $tablesForUnitId = [
            'products',
            'invoices',
            'proposals',
            'estimates',
            'credit_notes',
            'estimate_templates',
            'proposal_templates',
            'invoice_recurring'
        ];


        foreach ($tablesForUnitId as $table) {
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
        }

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {

            $units = [
                'unit_type' => 'Pcs',
                'default' => 1,
                'company_id' => $company->id
            ];

            $unitData = UnitType::create($units);

            $modelsToUpdate = [
                Product::class,
                Invoice::class,
                Proposal::class,
                Estimate::class,
                EstimateTemplate::class,
                ProposalTemplate::class,
                RecurringInvoice::class,
            ];

            foreach ($modelsToUpdate as $model) {
                $model::where('company_id', $company->id)
                    ->whereNull('unit_id')
                    ->update(['unit_id' => $unitData->id]);
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
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::table('proposals', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::dropIfExists('unit_types');

    }

};
