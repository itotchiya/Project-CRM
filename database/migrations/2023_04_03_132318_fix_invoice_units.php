<?php

use App\Models\Company;
use App\Models\UnitType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tablesForUnitId = [
            'invoices',
            'orders',
            'proposals',
            'estimates',
            'credit_notes',
            'estimate_templates',
            'proposal_templates',
            'invoice_recurring'
        ];


        foreach ($tablesForUnitId as $tableName) {
            if (Schema::hasColumn($tableName, 'unit_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $foreignKeys = $this->listTableForeignKeys($tableName);

                    if (in_array($tableName.'_unit_id_foreign', $foreignKeys)) {
                        $table->dropForeign($tableName.'_unit_id_foreign');
                    }

                    $table->dropColumn('unit_id');
                });
            }
        }

        $tablesForUnitId = [
            'order_items',
            'order_carts',
            'invoice_items',
            'proposal_items',
            'estimate_items',
            'credit_note_items',
            'estimate_template_items',
            'proposal_template_items',
            'invoice_recurring_items'
        ];


        foreach ($tablesForUnitId as $table) {
            if (!Schema::hasColumn($table, 'unit_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->bigInteger('unit_id')->unsigned()->nullable();

                    $table->foreign('unit_id')
                        ->references('id')
                        ->on('unit_types')
                        ->onDelete('SET NULL')
                        ->onUpdate('cascade');
                });
            }

            if (!Schema::hasColumn($table, 'product_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->unsignedInteger('product_id')->nullable();
                    $table->foreign('product_id')->references('id')->on('products')->onUpdate('CASCADE')->onDelete('SET NULL');
                });
            }
        }

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            $defaultUnit = UnitType::where('default', 1)->where('company_id', $company->id)->first();

            if ($defaultUnit) {
                DB::table('invoices')
                    ->leftJoin('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.id')
                    ->where('invoices.company_id', $company->id)
                    ->update(['invoice_items.unit_id' => $defaultUnit->id]);

                DB::table('orders')
                    ->leftJoin('order_items', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.company_id', $company->id)
                    ->update(['order_items.unit_id' => $defaultUnit->id]);

                DB::table('proposals')
                    ->leftJoin('proposal_items', 'proposal_items.proposal_id', '=', 'proposals.id')
                    ->where('proposals.company_id', $company->id)
                    ->update(['proposal_items.unit_id' => $defaultUnit->id]);

                DB::table('estimates')
                    ->leftJoin('estimate_items', 'estimate_items.estimate_id', '=', 'estimates.id')
                    ->where('estimates.company_id', $company->id)
                    ->update(['estimate_items.unit_id' => $defaultUnit->id]);

                DB::table('credit_notes')
                    ->leftJoin('credit_note_items', 'credit_note_items.credit_note_id', '=', 'credit_notes.id')
                    ->where('credit_notes.company_id', $company->id)
                    ->update(['credit_note_items.unit_id' => $defaultUnit->id]);

                DB::table('estimate_templates')
                    ->leftJoin('estimate_template_items', 'estimate_template_items.estimate_template_id', '=', 'estimate_templates.id')
                    ->where('estimate_templates.company_id', $company->id)
                    ->update(['estimate_template_items.unit_id' => $defaultUnit->id]);

                DB::table('proposal_templates')
                    ->leftJoin('proposal_template_items', 'proposal_template_items.proposal_template_id', '=', 'proposal_templates.id')
                    ->where('proposal_templates.company_id', $company->id)
                    ->update(['proposal_template_items.unit_id' => $defaultUnit->id]);

                DB::table('invoice_recurring')
                    ->leftJoin('invoice_recurring_items', 'invoice_recurring_items.invoice_recurring_id', '=', 'invoice_recurring.id')
                    ->where('invoice_recurring.company_id', $company->id)
                    ->update(['invoice_recurring_items.unit_id' => $defaultUnit->id]);
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
        $tablesForUnitId = [
            'order_items',
            'order_carts',
            'invoice_items',
            'proposal_items',
            'estimate_items',
            'credit_note_items',
            'estimate_template_items',
            'proposal_template_items',
            'invoice_recurring_items'
        ];


        foreach ($tablesForUnitId as $table) {
            if (Schema::hasColumn($table, 'unit_id')) {
                Schema::table($table, function (Blueprint $table) {

                    $foreignKeys = $this->listTableForeignKeys($table);
                    /** @phpstan-ignore-next-line */
                    if (in_array($table.'_unit_id_foreign', $foreignKeys)) {
                         /** @phpstan-ignore-next-line */
                        $table->dropForeign($table.'_unit_id_foreign');
                    }

                    $table->dropColumn('unit_id');
                });
            }

            if (Schema::hasColumn($table, 'product_id')) {
                Schema::table($table, function (Blueprint $table) {

                    $foreignKeys = $this->listTableForeignKeys($table);

                    /** @phpstan-ignore-next-line */
                    if (in_array($table.'_product_id_foreign', $foreignKeys)) {
                        /** @phpstan-ignore-next-line */
                        $table->dropForeign($table.'_product_id_foreign');
                    }

                    $table->dropColumn('product_id');
                });
            }
        }
    }

    public function listTableForeignKeys($table)
    {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        return array_map(function ($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }

};
