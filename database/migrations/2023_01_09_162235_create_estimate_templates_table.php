<?php

use App\Models\Company;
use App\Models\Contract;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('estimate_templates', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name');
            $table->double('sub_total');
            $table->double('total');
            $table->integer('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('discount_type', ['percent', 'fixed']);
            $table->double('discount');
            $table->boolean('invoice_convert')->default(0);
            $table->enum('status', ['declined', 'accepted', 'waiting'])->default('waiting');
            $table->mediumText('note')->nullable();
            $table->longText('description')->nullable();
            $table->enum('calculate_tax', ['after_discount', 'before_discount'])->default('after_discount');
            $table->text('client_comment')->nullable();
            $table->boolean('signature_approval')->default(1);
            $table->text('hash')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
            $table->integer('last_updated_by')->unsigned()->nullable();
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
            $table->timestamps();
        });

        Schema::create('estimate_template_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('estimate_template_id')->unsigned();
            $table->foreign('estimate_template_id')->references('id')->on('estimate_templates')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('hsn_sac_code')->nullable();
            $table->string('item_name');
            $table->enum('type', ['item', 'discount', 'tax'])->default('item');
            $table->tinyInteger('quantity');
            $table->double('unit_price');
            $table->double('amount');
            $table->text('item_summary')->nullable();
            $table->string('taxes')->nullable();

            $table->timestamps();
        });

        Schema::create('estimate_template_item_images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('estimate_template_item_id')->unsigned();
            $table->foreign('estimate_template_item_id')->references('id')
                ->on('estimate_template_items')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('filename');
            $table->string('hashname')->nullable();
            $table->string('size')->nullable();
            $table->string('external_link')->nullable();
            $table->timestamps();
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->integer('project_id')->unsigned()->nullable()->after('client_id');
            $table->foreign(['project_id'])->references(['id'])->on('projects')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->string('contract_prefix')->default('CONT')->after('credit_note_digit');
            $table->string('contract_number_separator')->default('#')->after('contract_prefix');
            $table->unsignedInteger('contract_digit')->default(3)->after('contract_number_separator');
        });

        Schema::table('lead_notes', function (Blueprint $table) {
            $table->longText('details')->change();
        });

        if (!Schema::hasColumn('client_details', 'company_logo')) {
            Schema::table('client_details', function (Blueprint $table) {
                $table->string('company_logo')->nullable()->after('last_updated_by');
            });
        }

        if (Schema::hasColumn('contracts', 'company_logo')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->dropColumn('company_logo');
            });
        }

        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->boolean('show_status')->default(true)->after('tax_calculation_msg');
            $table->boolean('authorised_signatory')->default(false)->after('show_status');
            $table->string('authorised_signatory_signature')->nullable()->after('authorised_signatory');
        });

        // SET lat long null for default address
        DB::statement("UPDATE `company_addresses` SET `latitude`=NULL and longitude=NULL where latitude='26.91243360'");


        $this->customFieldsContracts();


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimate_templates');
    }

    private function customFieldsContracts()
    {

        $companies = Company::select('id')->get();
        $customFieldGroup = [];

        foreach ($companies as $company) {
            $customFieldGroup = [
                [
                    'name' => 'Contract',
                    'model' => Contract::CUSTOM_FIELD_MODEL,
                    'company_id' => $company->id
                ]
            ];
        }

        CustomFieldGroup::insert($customFieldGroup);
    }

};
