<?php

namespace Database\Seeders;

use App\Models\UserChat;
use App\Models\User;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {

        $count = config('app.seed_record_count');
        UserChat::factory()
            ->count((int)$count)
            ->make()
            ->each(function (UserChat $model) use ($companyId) {
                $uniqueUser = $this->getUniqueUsers($companyId);
                $model->company_id = $companyId;
                $model->user_one = $uniqueUser['from'];
                $model->user_id = $uniqueUser['to'];
                $model->from = $uniqueUser['from'];
                $model->to = $uniqueUser['to'];
                $model->save();
            });
    }

    public function getUniqueUsers($companyId)
    {
        $employees = User::allEmployees(null, false, null, $companyId)->pluck('id')->toArray();

        $from = array_rand($employees);
        $from = $employees[$from];

        foreach (array_keys($employees, $from) as $key) {
            unset($employees[$key]);
        }

        $to = array_rand($employees);
        $to = $employees[$to];

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

}
