<?php

use App\Models\Company;
use App\Models\QuickBooksSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quick_books_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('sandbox_client_id');
            $table->string('sandbox_client_secret');
            $table->string('client_id');
            $table->string('client_secret');
            $table->string('access_token');
            $table->string('refresh_token');
            $table->string('realmid');
            $table->enum('sync_type', ['one_way', 'two_way'])->default('one_way');
            $table->enum('environment', ['Development', 'Production'])->default('Production');
            $table->boolean('status');
            $table->timestamps();
        });


        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('quickbooks_invoice_id')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->integer('quickbooks_payment_id')->nullable();
        });

        Schema::table('client_details', function (Blueprint $table) {
            $table->integer('quickbooks_client_id')->nullable();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->integer('datatable_row_limit')->default(10)->after('taskboard_length');
        });

        Schema::table('global_settings', function (Blueprint $table) {
            $table->integer('datatable_row_limit')->default(10)->after('allowed_file_size');
        });

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            QuickBooksSetting::create(['status' => 0, 'company_id' => $company->id]);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('quickbooks_invoice_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('quickbooks_payment_id');
        });

        Schema::table('client_details', function (Blueprint $table) {
            $table->dropColumn('quickbooks_client_id');
        });

        Schema::dropIfExists('quick_books_settings');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('datatable_row_limit');
        });
    }

};
