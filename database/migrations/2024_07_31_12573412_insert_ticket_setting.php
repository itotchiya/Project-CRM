<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
use App\Models\TicketSettingForAgents;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('ticket_settings_for_agents')) {
            Schema::table('ticket_settings_for_agents', function (Blueprint $table) {
                $table->unsignedInteger('user_id')->nullable()->default(null)->change();
            });

            $companyIds = Company::pluck('id');

            $existingCompanyIds = TicketSettingForAgents::whereIn('company_id', $companyIds)
                ->pluck('company_id')
                ->toArray();

            $newCompanyIds = array_diff($companyIds->toArray(), $existingCompanyIds);

            $insertData = array_map(function ($companyId) {
                return [
                    'ticket_scope' => 'assigned_tickets',
                    'company_id' => $companyId,
                ];
            }, $newCompanyIds);

            if (!empty($insertData)) {
                TicketSettingForAgents::insert($insertData);
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_settings_for_agents', function (Blueprint $table) {
            // Revert the column to its previous state, adjust as necessary
            $table->unsignedInteger('user_id')->default('None')->change();
        });
    }

};
