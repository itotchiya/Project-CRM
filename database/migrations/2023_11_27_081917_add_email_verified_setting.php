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
        Schema::whenTableDoesntHaveColumn('smtp_settings', 'email_verified', function (Blueprint $table) {
            $table->boolean('email_verified')->default(false)->after('mail_encryption');
        });

        $smtpSettings = \App\Models\SmtpSetting::first();

        if ($smtpSettings) {
            $smtpSettings->email_verified = env('MAIL_FROM_VERIFIED_EMAIL', false);
            $smtpSettings->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::whenTableHasColumn('smtp_settings', 'email_verified', function (Blueprint $table) {
            $table->dropColumn('email_verified');
        });
    }

};
