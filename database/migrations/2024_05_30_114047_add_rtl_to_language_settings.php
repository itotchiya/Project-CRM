<?php

use App\Models\LanguageSetting;
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
        Schema::whenTableDoesntHaveColumn('language_settings', 'is_rtl', function (Blueprint $table) {
            $table->boolean('is_rtl')->default(false);
        });

        // Fetch the language settings data
        $rtlLanguages = ['ar', 'fa'];

        // Update the rtl column based on language
        LanguageSetting::whereIn('language_code', $rtlLanguages)->update(['is_rtl' => true]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::whenTableHasColumn('language_settings', 'is_rtl', function (Blueprint $table) {
            $table->dropColumn('is_rtl');
        });
    }

};
