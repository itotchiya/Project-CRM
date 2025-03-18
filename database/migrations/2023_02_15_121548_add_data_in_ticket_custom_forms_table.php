<?php

use App\Models\Company;
use App\Models\TicketCustomForm;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {

        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedInteger('group_id')->after('type_id')->nullable();
            $table->foreign(['group_id'])->references(['id'])->on('ticket_groups')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        $companies = Company::get();

        foreach ($companies as $company) {
            $ticketCustomForm = TicketCustomForm::where('company_id', $company->id)->latest('id')->first();

            if ($ticketCustomForm) {
                TicketCustomForm::create([
                    'field_display_name' => 'Assign Group',
                    'field_name' => 'assign_group',
                    'field_order' => $ticketCustomForm->field_order + 1,
                    'field_type' => 'select',
                    'company_id' => $company->id,
                ]);
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
        Schema::table('ticket_custom_forms', function (Blueprint $table) {
            //
        });
    }

};
