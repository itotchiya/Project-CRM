<?php

use App\Models\User;
use App\Scopes\ActiveScope;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('payment_gateway_credentials', 'test_razorpay_webhook_secret')) {
            Schema::table('payment_gateway_credentials', function (Blueprint $table) {
                $table->string('test_razorpay_webhook_secret')->nullable()->after('test_razorpay_secret');
                $table->string('live_razorpay_webhook_secret')->nullable()->after('live_razorpay_secret');
            });
        }

        User::withoutGlobalScope(ActiveScope::class)
            ->where('status', '<>', 'deactive')
            ->update(['status' => 'active']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
