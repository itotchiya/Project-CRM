<?php

use App\Models\Proposal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('invoice_settings', 'proposal_prefix')) {
            Schema::table('invoice_settings', function (Blueprint $table) {
                $table->string('proposal_prefix')->default('Proposal')->after('order_digit');
            });
        }

        if (!Schema::hasColumn('invoice_settings', 'proposal_number_separator')) {
            Schema::table('invoice_settings', function (Blueprint $table) {
                $table->string('proposal_number_separator')->default('#')->after('proposal_prefix');
            });
        }

        if (!Schema::hasColumn('invoice_settings', 'proposal_digit')) {
            Schema::table('invoice_settings', function (Blueprint $table) {
                $table->integer('proposal_digit')->default(3)->after('proposal_number_separator');
            });
        }


        if (!Schema::hasColumn('proposals', 'proposal_number')) {
            Schema::table('proposals', function ($table) {
                $table->string('proposal_number')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('proposals', 'original_proposal_number')) {
            Schema::table('proposals', function ($table) {
                $table->string('original_proposal_number')->nullable()->after('proposal_number');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->dropColumn(['proposal_prefix', 'proposal_number_separator', 'proposal_digit']);
        });
    }

};
