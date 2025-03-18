<?php

use App\Models\Proposal;
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
        $proposals = Proposal::whereNull('proposal_number')->orderBy('id')->get();

        $invoiceSetting = invoice_setting();

        foreach ($proposals as $proposal) {
            try {
                $lastProposal = Proposal::lastProposalNumber() + 1;
                $zero = '';

                if (strlen($lastProposal) < $invoiceSetting->proposal_digit) {
                    $condition = $invoiceSetting->proposal_digit - strlen($lastProposal);

                    for ($i = 0; $i < $condition; $i++) {
                        $zero = '0' . $zero;
                    }
                }

                $originalNumber = $zero . $lastProposal;
                $proposalNumber = $invoiceSetting->proposal_prefix . $invoiceSetting->proposal_number_separator . $zero . $lastProposal;

                $proposal->update([
                    'proposal_number' => $proposalNumber,
                    'original_proposal_number' => $originalNumber,
                ]);
            }catch (\Exception $e){

            }

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
