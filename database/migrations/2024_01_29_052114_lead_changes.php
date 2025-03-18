<?php

use App\Models\CustomFieldGroup;
use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration only for clan delete it before release
        if (Schema::hasTable('lead_contacts')) {

            try {
                Schema::table('leads', function (Blueprint $table) {
                    $table->dropForeign(['client_id']);
                });
            } catch (\Exception $e) {
                echo "\nForeign key client_id does not exist in leads\n";
            }

            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('client_id');
            });

            Schema::table('lead_notes', function (Blueprint $table) {
                $table->dropForeign(['lead_contact_id']);
            });

            Schema::table('lead_contacts', function (Blueprint $table) {
                $table->renameColumn('name', 'client_name');
                $table->renameColumn('email', 'client_email');
            });

            Schema::rename('leads', 'deals');
            Schema::rename('lead_contacts', 'leads');

            Schema::table('lead_notes', function (Blueprint $table) {
                $table->renameColumn('lead_contact_id', 'lead_id');
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade')->onUpdate('cascade');
            });

            try {
                Schema::table('deals', function (Blueprint $table) {
                    $table->dropForeign(['lead_contact_id']);
                });
            } catch (\Exception $e) {
                echo "\nForeign key lead_contact_id does not exist in deals\n";
            }
            Schema::table('deals', function (Blueprint $table) {
                $table->renameColumn('lead_contact_id', 'lead_id');
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade')->onUpdate('cascade');
            });

            // Change lead custom field data to Deal
            CustomFieldGroup::where('model', 'App\Models\LeadContact')->update(['name' => 'Lead', 'model' => 'App\Models\Lead']); // Change group
            DB::table('custom_fields_data')->where('model', 'App\Models\LeadContact')->update(['model' => 'App\Models\Lead']); // Change model

            Schema::table('deal_notes', function (Blueprint $table) {
                $table->renameColumn('lead_id', 'deal_id');
            });

            Schema::table('lead_follow_up', function (Blueprint $table) {
                $table->renameColumn('lead_id', 'deal_id');
            });

            Schema::table('lead_products', function (Blueprint $table) {
                $table->renameColumn('lead_id', 'deal_id');
            });

            Schema::table('proposals', function (Blueprint $table) {
                $table->renameColumn('lead_id', 'deal_id');
            });

            Schema::table('purpose_consent_leads', function (Blueprint $table) {
                $table->renameColumn('lead_id', 'deal_id');
            });

            DB::statement("UPDATE permissions SET display_name = REPLACE(display_name, 'Deal', 'Lead') WHERE name IN ('view_lead_files', 'add_lead_files',
                            'delete_lead_files', 'view_lead_follow_up', 'add_lead_follow_up', 'edit_lead_follow_up', 'delete_lead_follow_up',
                            'view_lead_proposals', 'add_lead_proposals', 'edit_lead_proposals', 'delete_lead_proposals', 'manage_proposal_template',
                            'view_lead_agents', 'add_lead_agent', 'edit_lead_agent', 'delete_lead_agent')");

            $leadModule = Module::where('module_name', 'leads')->first();
            $dealModule = Module::where('module_name', 'deals')->first();

            if($dealModule){
                Permission::where('module_id', $dealModule->id)->update(['module_id' => $leadModule->id, 'is_custom' => 1]);
                $dealModule->delete();
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
