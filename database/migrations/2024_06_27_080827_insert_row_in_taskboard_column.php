<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
use App\Models\TaskboardColumn;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $values = [];
        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            $values[] = [
                'column_name' => 'Waiting Approval',
                'slug' => 'waiting-approval',
                'label_color' => '#000',
                'company_id' => $company->id,
                'priority' => 5,
            ];
        }
        TaskboardColumn::insert($values);

        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('approval_send', ['0', '1'])->default('0');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->enum('need_approval_by_admin', ['0', '1'])->default('0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('approval_send');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('need_approval_by_admin');
        });
    }
};
