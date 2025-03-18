<?php

use App\Models\LanguageSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        LanguageSetting::where('language_code', 'gr')
            ->where('language_code', '<>', 'el')
            ->update(['language_code' => 'el']); // Greek language code is el, not gr

        if (File::isDirectory(lang_path('gr'))) {
            // Rename the directory to el
            try {
                File::move(lang_path('gr'), lang_path('el'));
            } catch (\Exception $e) {
                echo $e->getMessage();
            }

        }
    }

    public function down(): void
    {
        //
    }

};
