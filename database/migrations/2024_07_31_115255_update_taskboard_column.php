<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
use App\Models\TaskboardColumn;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        TaskboardColumn::where('slug', 'waiting-approval')->update([
            'slug' => 'waiting_approval'
        ]);
    }

};
