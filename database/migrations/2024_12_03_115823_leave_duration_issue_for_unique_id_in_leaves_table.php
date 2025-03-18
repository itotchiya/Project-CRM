<?php

use App\Models\Leave;
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
        Leave::where('duration', 'multiple')
                ->whereNull('unique_id')
                ->update(['duration' => 'single']);
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
