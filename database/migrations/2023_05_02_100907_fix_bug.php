<?php

use App\Enums\MaritalStatus;
use App\Models\EmployeeDetails;
use App\Models\User;
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

        Schema::table('user_taskboard_settings', function (Blueprint $table) {
            $foreignKeys = $this->listTableForeignKeys('user_taskboard_settings');

            if (in_array('user_taskboard_settings_board_column_id_foreign', $foreignKeys)) {
                $table->dropForeign(['board_column_id']);
            }

            $table->foreign('board_column_id')->references('id')->on('taskboard_columns')->onDelete('cascade')->onUpdate('cascade');
        });

        DB::statement("ALTER TABLE `users` CHANGE `gender` `gender` ENUM('male','female','others') NULL DEFAULT 'male';");

        User::whereNull('gender')->update(['gender' => 'male']);

        Schema::table('employee_details', function (Blueprint $table) {
            $table->string('marital_status')->nullable()->default(MaritalStatus::Single->value)->change();
        });

        EmployeeDetails::whereNull('marital_status')->update(['marital_status' => MaritalStatus::Single]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    public function listTableForeignKeys($table)
    {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        return array_map(function ($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }

};
