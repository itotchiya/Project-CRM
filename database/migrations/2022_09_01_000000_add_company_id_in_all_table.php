<?php

use App\Models\GlobalSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        $tables = GlobalSetting::COMPANY_TABLES;

        $count = Company::count();

        /** @phpstan-ignore-next-line */
        $first = Company::pluck('id')->first();
        // Delete if more than one companies in database for worksuite
        // Delete all other companies except first
        if ($count > 1 && $first === 1) {
            Company::where('id', '<>', $first)->delete();
        }

        try {

            foreach ($tables as $table) {

                // Check if company id exists in database
                if (!Schema::hasColumn($table, 'company_id')) {

                    Schema::table($table, function (Blueprint $table) {
                        $table->integer('company_id')->unsigned()->nullable()->after('id');
                        $table->foreign('company_id')
                            ->references('id')
                            ->on('companies')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                    });

                    // This is done for existing customers. To update the company id with 1
                    if ($first === 1) {
                        DB::table($table)->update(['company_id' => 1]);
                    }
                }
            }

        } catch (Exception $e) {
            dd($e->getMessage());
        }

        if (!Schema::hasColumn('companies', 'status')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->integer('default_task_status')->unsigned()->nullable()->default(null)->change();
                $table->enum('status', ['active', 'inactive'])->after('active_theme')->default('active');
                $table->dateTime('last_login')->nullable();
                $table->boolean('rtl')->default(false);
            });
        }

        if (!Schema::hasColumn('companies', 'hash')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('hash')->nullable()->after('token');
            });
        }

        $this->removeUnique();

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {

            $this->saveUniqueHash($company);
            $company->taskSetting()->firstOrCreate();
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    private function saveUniqueHash($company)
    {
        $company->hash = md5(microtime());
        $company->saveQuietly();
    }

    private function removeUnique()
    {
        // Remove unique
        try {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique(['name']);
                $table->unique(['name', 'company_id']);
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        try {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropUnique(['name']);
                $table->unique(['name', 'module_id']);
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        try {
            Schema::table('employee_details', function (Blueprint $table) {
                $table->dropUnique(['employee_id']);
                $table->dropUnique(['slack_username']);

                $table->unique(['employee_id', 'company_id']);
                $table->unique(['slack_username', 'company_id']);
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        try {
            Schema::table('estimates', function (Blueprint $table) {
                $table->dropUnique(['estimate_number']);
                $table->unique(['estimate_number', 'company_id']);
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        try {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropUnique(['invoice_number']);
                $table->unique(['invoice_number', 'company_id']);
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        try {
            Schema::table('lead_sources', function (Blueprint $table) {
                $table->dropUnique(['type']);
                $table->unique(['type', 'company_id']);
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        try {
            Schema::table('lead_status', function (Blueprint $table) {
                $table->dropUnique(['type']);
                $table->unique(['type', 'company_id']);
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropUnique(['event_id']);
                $table->dropUnique(['transaction_id']);

                $table->unique(['event_id', 'company_id']);
                $table->unique(['transaction_id', 'company_id']);
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        try {
            Schema::table('push_subscriptions', function (Blueprint $table) {
                $table->dropUnique(['endpoint']);
                $table->unique(['endpoint', 'company_id']);
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        try {
            Schema::table('taskboard_columns', function (Blueprint $table) {
                $table->dropUnique(['column_name']);
                $table->unique(['column_name', 'company_id']);
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        try {
            Schema::table('ticket_channels', function (Blueprint $table) {
                $table->dropUnique(['channel_name']);
                $table->unique(['channel_name', 'company_id']);
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        try {
            Schema::table('ticket_types', function (Blueprint $table) {
                $table->dropUnique(['type']);
                $table->unique(['type', 'company_id']);
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['email']);
                $table->unique(['email', 'company_id']);
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

};
