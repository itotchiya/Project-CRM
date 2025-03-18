<?php

use App\Models\LanguageSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $languages = LanguageSetting::get();

        if ($languages->count()) {

            $bgLanguage = $languages->where('language_code', 'bg')->first();

            if (!$bgLanguage) {
                LanguageSetting::create([
                    'language_code' => 'bg',
                    'flag_code' => 'bg',
                    'language_name' => 'Bulgarian',
                    'status' => 'disabled',
                ]);
            }

            $idLanguage = $languages->where('language_code', 'id')->first();

            if (!$idLanguage) {
                LanguageSetting::create([
                    'language_code' => 'id',
                    'flag_code' => 'id',
                    'language_name' => 'Indonesian',
                    'status' => 'disabled',
                ]);
            }

            // Delete duplicate language only if there is more than one language
            foreach ($languages as $language) {
                $duplicateLanguages = $languages->where('language_code', $language->language_code);

                if ($duplicateLanguages->count() > 1) {
                    $duplicateLanguages->shift();

                    foreach ($duplicateLanguages as $duplicateLanguage) {
                        $duplicateLanguage->delete();
                    }
                }
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
