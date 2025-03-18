<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('lead_agents', 'lead_category_id')) {
            Schema::table('lead_agents', function (Blueprint $table) {
                $table->unsignedInteger('lead_category_id')->after('user_id')->nullable();
                $table->foreign('lead_category_id')->references('id')->on('lead_category')->onDelete('CASCADE')->onUpdate('CASCADE');
            });

            Schema::table('deals', function (Blueprint $table) {
                $table->unsignedInteger('category_id')->after('agent_id')->nullable();
                $table->foreign('category_id')->references('id')->on('lead_category')->onUpdate('CASCADE')->onDelete('SET NULL');
            });

            $deals = \App\Models\Deal::get();

            foreach ($deals as $deal) {
                if (!is_null($deal->contact->category) && is_null($deal->category_id)) {
                    $deal->category_id = $deal->contact->category->id;
                    $deal->save();
                }
            }
        }


        Schema::table('deals', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->foreign('agent_id')->references('id')->on('lead_agents')->onDelete('SET NULL')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            //
        });
    }

};
