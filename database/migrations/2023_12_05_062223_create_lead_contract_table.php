<?php

use App\Models\Company;
use App\Models\DashboardWidget;
use App\Models\Deal;
use App\Models\DealFile;
use App\Models\DealFollowUp;
use App\Models\Lead;
use App\Models\LeadPipeline;
use App\Models\LeadProduct;
use App\Models\PipelineStage;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionType;
use App\Models\Proposal;
use App\Models\PurposeConsentLead;
use App\Models\RoleUser;
use App\Models\UserLeadboardSetting;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createTables();
        $this->companySpecificChanges();
        $this->createDealsFromLeads();
        $this->addDealModulePermission();
        $this->cleanUpLeadTables();
    }

    private function createTables()
    {
        if (!Schema::hasTable('lead_pipelines')) {
            Schema::create('lead_pipelines', function (Blueprint $table) {
                $table->id();
                $table->integer('company_id')->unsigned()->nullable();
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->integer('priority')->default(0);
                $table->string('label_color')->default('#ff0000');
                $table->boolean('default');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pipeline_stages')) {
            Schema::create('pipeline_stages', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('company_id')->unsigned()->nullable();
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
                $table->unsignedBigInteger('lead_pipeline_id')->nullable();
                $table->foreign('lead_pipeline_id')->references(['id'])->on('lead_pipelines')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->integer('priority')->default(0);
                $table->boolean('default')->default(0);
                $table->string('label_color')->default('#ff0000');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('deals')) {
            Schema::create('deals', function (Blueprint $table) {
                $table->id();
                $table->integer('company_id')->unsigned()->nullable();
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
                $table->string('name')->nullable();
                $table->integer('column_priority')->default(0);
                $table->unsignedBigInteger('lead_pipeline_id')->nullable();
                $table->foreign('lead_pipeline_id')->references(['id'])->on('lead_pipelines')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->unsignedInteger('pipeline_stage_id')->nullable();
                $table->foreign('pipeline_stage_id')->references(['id'])->on('pipeline_stages')->onUpdate('CASCADE')->onDelete('SET NULL');
                $table->unsignedInteger('lead_id')->nullable();
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade')->onUpdate('cascade');
                $table->date('close_date')->nullable();
                $table->unsignedBigInteger('agent_id')->nullable()->index('leads_agent_id_foreign');
                $table->foreign(['agent_id'])->references(['id'])->on('lead_agents')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->enum('next_follow_up', ['yes', 'no'])->default('yes');
                $table->double('value', 30, 2)->nullable()->default(0);
                $table->longText('note')->nullable();
                $table->text('hash')->nullable();
                $table->unsignedInteger('currency_id')->nullable()->index('leads_currency_id_foreign');
                $table->foreign(['currency_id'])->references(['id'])->on('currencies')->onUpdate('CASCADE')->onDelete('SET NULL');
                $table->unsignedInteger('added_by')->nullable();
                $table->foreign('added_by')->references('id')->on('users')->onDelete('cascade');
                $table->unsignedInteger('last_updated_by')->nullable();
                $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('cascade');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('lead_pipeline_stages')) {
            Schema::create('lead_pipeline_stages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('lead_pipeline_id')->unsigned()->nullable();
                $table->foreign('lead_pipeline_id')->references('id')->on('lead_pipelines')->onDelete('cascade')->onUpdate('cascade');
                $table->integer('pipeline_stages_id')->unsigned()->nullable();
                $table->foreign('pipeline_stages_id')->references('id')->on('pipeline_stages')->onDelete('cascade')->onUpdate('cascade');
                $table->timestamps();
            });
        }

        Schema::whenTableDoesntHaveColumn('user_leadboard_settings', 'pipeline_stage_id', function (Blueprint $table) {
            try {
                if (Schema::hasColumn('user_leadboard_settings', 'board_column_id')) {
                    $table->dropForeign(['board_column_id']);
                    $table->dropColumn('board_column_id');
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }


            $table->unsignedInteger('pipeline_stage_id')->nullable()->after('user_id');
            $table->foreign('pipeline_stage_id')->references(['id'])->on('pipeline_stages')->onUpdate('CASCADE')->onDelete('SET NULL');
        });

        if (!Schema::hasTable('deal_notes')) {
            Schema::create('deal_notes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->longText('details')->nullable();
                $table->unsignedBigInteger('deal_id')->nullable();
                $table->unsignedInteger('added_by')->nullable();
                $table->unsignedInteger('last_updated_by')->nullable();
                $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('added_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade')->onUpdate('cascade');
                $table->timestamps();
            });
        }

        $tables = [
            'lead_follow_up',
            'lead_products',
            'proposals',
            'purpose_consent_leads',
        ];

        foreach ($tables as $tableName) {
            Schema::whenTableDoesntHaveColumn($tableName, 'deal_id', function (Blueprint $table) {
                $table->unsignedBigInteger('deal_id')->nullable()->after('lead_id');
                $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade')->onUpdate('cascade');
            });
        }

        if (Schema::hasTable('lead_files')) {
            Schema::table('lead_files', function (Blueprint $table) {
                $table->dropForeign(['lead_id']);
            });

            Schema::rename('lead_files', 'deal_files');

            Schema::whenTableDoesntHaveColumn('deal_files', 'deal_id', function (Blueprint $table) {
                $table->unsignedBigInteger('deal_id')->nullable()->after('lead_id');
                $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade')->onUpdate('cascade');
            });
        }
    }

    private function companySpecificChanges()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $dashboardWidget = DashboardWidget::where('dashboard_type', 'admin-client-dashboard')
                ->where('widget_name', 'total_deals')
                ->where('company_id', $company->id)
                ->first();

            if ($dashboardWidget) {
                continue;
            }

            $pipeline = (new \App\Observers\CompanyObserver())->leadStages($company);

            $leadStatus = DB::table('lead_status')->where('company_id', $company->id)->get();

            foreach ($leadStatus as $status) {
                if (!in_array($status->type, ['pending', 'in process', 'done'])) {
                    $pipelineStage = new PipelineStage();
                    $pipelineStage->company_id = $company->id;
                    $pipelineStage->lead_pipeline_id = $pipeline->id;
                    $pipelineStage->name = $status->type;
                    $pipelineStage->save();
                }
            }

            $leadBoardSettings = UserLeadboardSetting::where('company_id', $company->id)->get();
            $leadStages = PipelineStage::where('company_id', $company->id)->get();


            foreach ($leadBoardSettings as $leadBoard) {
                $statusData = $leadStatus->where('id', $leadBoard->pipeline_stage_id)->first();

                $stage = match ($statusData?->type) {
                    'pending' => $leadStages->where('name', 'Generated')->first(),
                    'in process' => $leadStages->where('name', 'On going')->first(),
                    'done' => $leadStages->where('name', 'Win')->first(),
                    null => null,
                    default => $leadStages->where('name', $statusData->type)->first(),
                };

                if ($stage) {
                    $leadBoard->pipeline_stage_id = $stage->id;
                    $leadBoard->save();
                }
            }

            if (is_null($dashboardWidget)) {
                $widget = ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'total_deals', 'status' => 1, 'company_id' => $company->id];
                DashboardWidget::create($widget);
            }
        }
    }

    private function createDealsFromLeads()
    {
        $leads = Lead::withoutGlobalScopes()->get();

        foreach ($leads as $lead) {

            if (Deal::where('lead_id', $lead->id)->exists()) {
                continue;
            }

            try {

                $pipeline = LeadPipeline::withoutGlobalScopes()->where('company_id', $lead->company_id)->first();

                $pipelineStages = PipelineStage::withoutGlobalScopes()->where('company_id', $lead->company_id)->get();

                $leadStatus = DB::table('lead_status')->where('id', $lead->status_id)->first();

                $stage = $pipelineStages->where('default', 1)->first();

                $stage = match ($leadStatus->type) {
                    'pending' => $pipelineStages->where('name', 'Generated')->first() ?? $stage,
                    'in process' => $pipelineStages->where('name', 'On going')->first() ?? $stage,
                    'done' => $pipelineStages->where('name', 'Win')->first() ?? $stage,
                    null => $stage,
                    default => $pipelineStages->where('name', $leadStatus->type)->first() ?? $stage,
                };

                // Lead Details save in deals
                $deal = new Deal();
                $deal->company_id = $lead->company_id;
                $deal->name = $lead->client_name;
                $deal->lead_pipeline_id = $pipeline->id;
                $deal->pipeline_stage_id = $stage->id;
                $deal->lead_id = $lead->id;
                $deal->agent_id = $lead->agent_id;
                $deal->next_follow_up = $lead->next_follow_up;
                $deal->value = $lead->value;
                $deal->currency_id = $lead->currency_id;
                $deal->added_by = $lead->added_by;
                $deal->last_updated_by = $lead->last_updated_by;
                $deal->hash = md5(microtime());
                $deal->next_follow_up = 'yes';
                $deal->saveQuietly();

                DealFollowUp::where('lead_id', $lead->id)->update(['deal_id' => $deal->id]);
                LeadProduct::where('lead_id', $lead->id)->update(['deal_id' => $deal->id]);
                Proposal::where('lead_id', $lead->id)->update(['deal_id' => $deal->id]);
                PurposeConsentLead::where('lead_id', $lead->id)->update(['deal_id' => $deal->id]);
                DealFile::where('lead_id', $lead->id)->update(['deal_id' => $deal->id]);

            } catch (\Exception $e) {
                echo "\nError in lead id: " . $lead->id . ' Error: ' . $e->getMessage() . "\n";
            }
        }


        $tables = [
            'lead_follow_up',
            'lead_products',
            'proposals',
            'purpose_consent_leads',
        ];

        foreach ($tables as $tableName) {
            try {
                Schema::whenTableHasColumn($tableName, 'lead_id', function (Blueprint $table) {
                    $table->dropForeign(['lead_id']);
                    $table->dropColumn('lead_id');
                });
            } catch (\Exception $exception) {
                echo $exception->getMessage();
            }
        }


        try {
            Schema::whenTableHasColumn('deal_files', 'lead_id', function (Blueprint $table) {
                $table->dropColumn('lead_id');
            });
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }


    }

    private function addDealModulePermission()
    {
        $addDealsPermission = Permission::where('name', 'add_deals')->first();

        if (!$addDealsPermission) {
            $leadsModule = Module::firstOrCreate(['module_name' => 'leads']);

            $dealPer = [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_deals',
                    'display_name' => ucwords(str_replace('_', ' ', 'add_deals')),
                    'module_id' => $leadsModule->id,
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_deals',
                    'display_name' => ucwords(str_replace('_', ' ', 'view_deals')),
                    'module_id' => $leadsModule->id,
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_deals',
                    'display_name' => ucwords(str_replace('_', ' ', 'edit_deals')),
                    'module_id' => $leadsModule->id,
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'display_name' => ucwords(str_replace('_', ' ', 'delete_deals')),
                    'name' => 'delete_deals',
                    'module_id' => $leadsModule->id,
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'display_name' => ucwords(str_replace('_', ' ', 'manage_deal_stages')),
                    'name' => 'manage_deal_stages',
                    'module_id' => $leadsModule->id,
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'display_name' => ucwords(str_replace('_', ' ', 'change_deal_stages')),
                    'name' => 'change_deal_stages',
                    'module_id' => $leadsModule->id,
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'display_name' => ucwords(str_replace('_', ' ', 'add_deal_pipeline')),
                    'name' => 'add_deal_pipeline',
                    'module_id' => $leadsModule->id,
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'display_name' => ucwords(str_replace('_', ' ', 'view_deal_pipeline')),
                    'name' => 'view_deal_pipeline',
                    'module_id' => $leadsModule->id,
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'display_name' => ucwords(str_replace('_', ' ', 'edit_deal_pipeline')),
                    'name' => 'edit_deal_pipeline',
                    'module_id' => $leadsModule->id,
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'display_name' => ucwords(str_replace('_', ' ', 'delete_deal_pipeline')),
                    'name' => 'delete_deal_pipeline',
                    'module_id' => $leadsModule->id,
                ],

                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'display_name' => ucwords(str_replace('_', ' ', 'add_deal_note')),
                    'name' => 'add_deal_note',
                    'module_id' => $leadsModule->id,
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'display_name' => ucwords(str_replace('_', ' ', 'view_deal_note')),
                    'name' => 'view_deal_note',
                    'module_id' => $leadsModule->id,
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'display_name' => ucwords(str_replace('_', ' ', 'edit_deal_note')),
                    'name' => 'edit_deal_note',
                    'module_id' => $leadsModule->id,
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'display_name' => ucwords(str_replace('_', ' ', 'delete_deal_note')),
                    'name' => 'delete_deal_note',
                    'module_id' => $leadsModule->id,
                ],

            ];

            Permission::whereIn('name', ['manage_lead_status', 'change_lead_status'])->delete();

            foreach ($dealPer as $dealPe) {
                Permission::firstOrCreate($dealPe);
            }

            $admins = RoleUser::join('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('name', 'admin')
                ->get();

            $allTypePermission = PermissionType::ofType('all')->first();

            $dealPermissions = Permission::where('module_id', $leadsModule->id)->get();

            foreach ($dealPermissions as $dealPermission) {
                foreach ($admins as $item) {
                    UserPermission::firstOrCreate(
                        [
                            'user_id' => $item->user_id,
                            'permission_id' => $dealPermission->id,
                            'permission_type_id' => $allTypePermission->id ?? PermissionType::ALL
                        ]
                    );
                }
            }
        }
    }

    private function cleanUpLeadTables()
    {

        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropForeign(['currency_id']);
            $table->dropColumn('agent_id');
            $table->dropColumn('currency_id');
            $table->dropColumn('next_follow_up');
            $table->dropColumn('value');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_pipelines');
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('lead_pipeline_stages');
    }

};
