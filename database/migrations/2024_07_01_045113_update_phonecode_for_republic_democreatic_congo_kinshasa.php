<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Country;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Country::where('iso', 'CD')
            ->where('iso3', 'COD')
            ->update(['phonecode' => 243]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Country::where('iso', 'CD')
            ->where('iso3', 'COD')
            ->update(['phonecode' => 242]);
    }

};
