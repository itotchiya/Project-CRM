<?php

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
        if(!Schema::hasColumn('project_milestones', 'company_id')){
            Schema::table('project_milestones', function (Blueprint $table) {
                $table->unsignedInteger('company_id')->nullable()->index('project_milestones_company_id_index')->after('id');
                $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
            });
        }

        $milestones = ProjectMilestone::with('project')->get();

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
        //
    }
};
