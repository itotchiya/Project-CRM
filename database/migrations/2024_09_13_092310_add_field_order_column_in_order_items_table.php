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
        if (!Schema::hasColumn('order_items', 'field_order')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->integer('field_order')->default(0);
            });
        }

        if (!Schema::hasColumn('invoice_items', 'field_order')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->integer('field_order')->default(0);
            });
        }

        if (!Schema::hasColumn('estimate_items', 'field_order')) {
            Schema::table('estimate_items', function (Blueprint $table) {
                $table->integer('field_order')->default(0);
            });
        }

        if (!Schema::hasColumn('proposal_items', 'field_order')) {
            Schema::table('proposal_items', function (Blueprint $table) {
                $table->integer('field_order')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('field_order');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('field_order');
        });

        Schema::table('estimate_items', function (Blueprint $table) {
            $table->dropColumn('field_order');
        });

        Schema::table('proposal_items', function (Blueprint $table) {
            $table->dropColumn('field_order');
        });
    }

};
