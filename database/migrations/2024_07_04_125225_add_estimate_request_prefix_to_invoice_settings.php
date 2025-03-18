<?php

use App\Models\EstimateRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->string('estimate_request_prefix')->default('ESTRQ')->after('contract_number_separator');
            $table->string('estimate_request_number_separator')->default('#')->after('estimate_request_prefix');
            $table->integer('estimate_request_digit')->default(3)->after('estimate_request_number_separator');
        });

        $estimateRequests = EstimateRequest::whereNull('estimate_request_number')->orderBy('id')->get();

        $invoiceSetting = invoice_setting();

        foreach ($estimateRequests as $estimateRequest) {
            $lastEstimate = EstimateRequest::lastRequestNumber() + 1;

            $zero = str_repeat('0', $invoiceSetting->estimate_request_digit - strlen($lastEstimate));

            $originalNumber = $zero . $lastEstimate;
            $requestNumber = $invoiceSetting->estimate_request_prefix . $invoiceSetting->estimate_request_number_separator . $zero . $lastEstimate;
            $estimateRequest->update([
                'estimate_request_number' => $requestNumber,
                'original_request_number' => $originalNumber,
            ]);

            if ($estimateRequest->estimate_id) {
                $estimateRequest->estimate->update([
                    'estimate_request_id' => $estimateRequest->id
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->dropColumn(['estimate_request_prefix', 'estimate_request_number_separator', 'estimate_request_number_length']);
        });
    }

};
