<?php

namespace Database\Seeders;

use App\Models\Notice;
use Illuminate\Database\Seeder;

class NoticesTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $count = config('app.seed_record_count');

        Notice::factory()
            ->count((int)$count)
            ->make()
            ->each(function (Notice $model) use ($companyId) {
                $model->company_id = $companyId;
                $model->save();
            });
    }

}
