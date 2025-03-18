<?php

use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\DashboardWidget;
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
        Schema::create('task_comment_emoji', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('comment_id')->unsigned()->nullable();
            $table->foreign('comment_id')->references('id')->on('task_comments')->onDelete('cascade')->onUpdate('cascade');
            $table->string('emoji_name')->nullable();
            $table->timestamps();
        });


        Schema::table('company_addresses', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->default(null)->nullable();
            $table->decimal('longitude', 11, 8)->default(null)->nullable();
        });


        $companies = Company::get();

        foreach ($companies as $company) {

            // Update company address
            $company->companyAddress()->whereNull('latitude')
                ->update([
                    'latitude' => $company->latitude,
                    'longitude' => $company->longitude
                ]);

            // Create new widget ticket
            $widget = [
                'widget_name' => 'ticket',
                'status' => 1,
                'company_id' => $company->id,
                'dashboard_type' => 'private-dashboard',
            ];

            DashboardWidget::create($widget);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_comment_emoji');
        Schema::table('company_addresses', function (Blueprint $table) {
            $table->dropColumn(['latitude']);
            $table->dropColumn(['longitude']);
        });
    }

};
