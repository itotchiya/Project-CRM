<?php

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

        // DROP OLD TABLE IF EXIST so as to use new index
        Schema::dropIfExists('device_user');
        Schema::dropIfExists('track_devices');

        $deviceTableName = config('laravel-device-tracking.device_table');
        $fieldName = config('laravel-device-tracking.model_relation_id');

        Schema::create($deviceTableName, function (Blueprint $table) {
            $table->id();
            $table->string('device_uuid')->unique();
            $table->string('device_type')->index();
            $table->string('ip', 40)->index();

            $table->timestamp('device_hijacked_at')->nullable();

            $table->text('data')->nullable();
            $table->boolean('is_rogue_device')->default(false)->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('device_user', function (Blueprint $table) use ($deviceTableName, $fieldName) {
            $table->id();
            $table->foreignId($fieldName)->index();

            $table->foreignId('device_id')->index()
                ->constrained($deviceTableName)
                ->cascadeOnDelete();
            $table->index([$fieldName, 'device_id']);
            $table->string('name')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('reported_as_rogue_at')->nullable()->index();
            $table->text('note')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_user');
    }
};
