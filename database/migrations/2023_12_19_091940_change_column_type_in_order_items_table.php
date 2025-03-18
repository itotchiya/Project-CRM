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
        Schema::table('orders', function (Blueprint $table) {
            $table->double('sub_total', 30, 2)->change();
            $table->double('total', 30, 2)->change();
            $table->double('discount', 30, 2)->default(0)->change();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->double('unit_price', 30, 2)->change();
            $table->double('quantity', 30, 2)->change();
        });
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->double('adjustment_amount', 30, 2)->nullable()->change();
            $table->double('sub_total', 30, 2)->change();
            $table->double('total', 30, 2)->change();
            $table->double('discount', 30, 2)->default(0)->change();
        });
        Schema::table('credit_note_items', function (Blueprint $table) {
            $table->double('unit_price', 30, 2)->change();
            $table->double('amount', 30, 2)->change();
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->double('due_amount', 30, 2)->default(0)->change();
            $table->double('discount', 30, 2)->default(0)->change();
            $table->double('total', 30, 2)->change();
            $table->double('sub_total', 30, 2)->change();
        });
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->double('quantity', 30, 2)->change();
            $table->double('unit_price', 30, 2)->change();
            $table->double('amount', 30, 2)->change();
        });
        Schema::table('quotations', function (Blueprint $table) {
            $table->double('sub_total', 30, 2)->change();
            $table->double('total', 30, 2)->change();
        });
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->double('amount', 30, 2)->change();
        });
        Schema::table('estimates', function (Blueprint $table) {
            $table->double('sub_total', 30, 2)->change();
            $table->double('total', 30, 2)->change();
            $table->double('discount', 30, 2)->default(0)->change();
        });
        Schema::table('estimate_templates', function (Blueprint $table) {
            $table->double('sub_total', 30, 2)->change();
            $table->double('total', 30, 2)->change();
            $table->double('discount', 30, 2)->change();
        });
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->double('quantity', 30, 2)->change();
            $table->double('unit_price', 30, 2)->change();
            $table->double('amount', 30, 2)->change();
        });
        Schema::table('estimate_template_items', function (Blueprint $table) {
            $table->double('quantity', 30, 2)->change();
            $table->double('unit_price', 30, 2)->change();
            $table->double('amount', 30, 2)->change();
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->double('price', 30, 2)->change();
        });
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->double('cost', 30, 2)->change();
        });
        Schema::table('proposals', function (Blueprint $table) {
            $table->double('sub_total', 30, 2)->change();
            $table->double('total', 30, 2)->change();
            $table->double('discount', 30, 2)->change();
        });
        Schema::table('proposal_items', function (Blueprint $table) {
            $table->double('quantity', 30, 2)->change();
            $table->double('unit_price', 30, 2)->change();
            $table->double('amount', 30, 2)->change();
        });
        Schema::table('proposal_templates', function (Blueprint $table) {
            $table->double('sub_total', 30, 2)->change();
            $table->double('total', 30, 2)->change();
            $table->double('discount', 30, 2)->change();
        });
        Schema::table('proposal_template_items', function (Blueprint $table) {
            $table->double('unit_price', 30, 2)->change();
            $table->double('amount', 30, 2)->change();
            $table->double('quantity', 30, 2)->change();
        });
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->double('bank_balance', 30, 2)->nullable()->change();
            $table->double('opening_balance', 30, 2)->nullable()->change();
        });
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->double('bank_balance', 30, 2)->nullable()->change();
            $table->double('amount', 30, 2)->nullable()->change();
        });
        Schema::table('order_carts', function (Blueprint $table) {
            $table->double('quantity', 30, 2)->change();
            $table->double('unit_price', 30, 2)->change();
            $table->double('amount', 30, 2)->change();
        });
        Schema::table('expenses_recurring', function (Blueprint $table) {
            $table->double('price', 30, 2)->change();
        });
        Schema::table('invoice_recurring', function (Blueprint $table) {
            $table->double('sub_total', 30, 2)->default(0)->change();
            $table->double('total', 30, 2)->default(0)->change();
            $table->double('discount', 30, 2)->default(0)->change();
        });
        Schema::table('invoice_recurring_items', function (Blueprint $table) {
            $table->double('quantity', 30, 2)->change();
            $table->double('unit_price', 30, 2)->change();
            $table->double('amount', 30, 2)->change();
        });

        if (Schema::hasColumn('leads', 'value')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->double('value', 30, 2)->nullable()->default(0)->change();
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->double('amount', 30, 2)->change();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->double('project_budget', 30, 2)->nullable()->change();
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
