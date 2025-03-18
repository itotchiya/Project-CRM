<?php

use App\Models\AwardIcon;
use App\Models\Task;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tasks = Task::whereNull('hash')->get();

        foreach ($tasks as $task) {
            $task->hash = md5(microtime() . rand(1, 99999999));
            $task->save();
        }

        $column = 'icon';

        // phpcs:ignore
        AwardIcon::where('icon', 'LIKE', '%-fill%')->update([$column => DB::raw("REPLACE($column,'-fill','')")]);


        \App\Models\Country::where('iso3', 'RUS')->update(['phonecode' => 7]);

        cache()->forget('countries');

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
