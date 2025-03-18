<?php

use App\Models\Leave;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ProjectMilestone;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $milestones = ProjectMilestone::with('project')->whereNull('company_id')->get();

        foreach($milestones as $milestone){
            $milestone->company_id = $milestone->project->company_id;
            $milestone->saveQuietly();
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
