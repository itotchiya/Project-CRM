<?php

use App\Models\Company;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('order_number')->after('id')->nullable();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->bigInteger('ticket_number')->after('id')->nullable();
        });

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {

            $orders = Order::where('company_id', $company->id)->get();

            foreach ($orders as $key => $order) {
                $order->order_number = $key + 1;
                $order->saveQuietly();
            }

            $tickets = Ticket::where('company_id', $company->id)->get();

            foreach ($tickets as $key => $ticket) {
                $ticket->ticket_number = $key + 1;
                $ticket->saveQuietly();
            }
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->bigInteger('invoice_number')->change();
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->bigInteger('estimate_number')->change();
        });
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

};
