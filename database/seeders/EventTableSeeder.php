<?php

namespace Database\Seeders;

use App\Models\Event;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class EventTableSeeder extends Seeder
{

    public function run($companyId)
    {
        $count = config('app.seed_record_count');
        $faker = \Faker\Factory::create();
        $employees = \App\Models\User::allEmployees(null, false, null, $companyId)->pluck('id')->toArray();

        \App\Models\Event::factory()->count((int)$count)->create()->each(function (Event $event) use ($faker, $companyId, $employees) {
            $event->company_id = $companyId;
            $event->save();
            try {
                $randomEmployeeArray = $faker->randomElements($employees, $faker->numberBetween(1, 10));

                foreach ($randomEmployeeArray as $employee) {
                    \App\Models\EventAttendee::create([
                        'user_id' => $employee,
                        'event_id' => $event->id,
                        'company_id' => $companyId
                    ]);
                }
            } catch (Exception $e) {
                Log::info($e);
            }
        });
    }

}
