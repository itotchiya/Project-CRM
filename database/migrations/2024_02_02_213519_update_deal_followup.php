<?php

use App\Models\Deal;
use App\Models\DealFollowUp;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set all deals default follow up yes
        Deal::where(function ($q) {
            $q->where('next_follow_up', 'no');
            $q->orWhere('next_follow_up', '');
            $q->orWhere('next_follow_up', null);
        })
            ->update(['next_follow_up' => 'yes']);

        DealFollowUp::where('status', 'incomplete')->update(['status' => 'pending']);
    }

};
