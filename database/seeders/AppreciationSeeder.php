<?php

namespace Database\Seeders;

use App\Models\Appreciation;
use App\Models\Award;
use App\Models\AwardIcon;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppreciationSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $awardIcons = AwardIcon::all()->pluck('id')->toArray();
        $iconColors = ['#282E33', '#495E67', '#FF3838', '#3DADDD', '#387B1C', '#7B1C2E'];

        $awardList = [
            'Best Team Player',
            'Most Innovative Project',
            'Best Technical Solution',
            'Best Customer Service',
            'Employee of the Month',
            'Best Mentor',
            'Top Sales Performer',
            'Best Project Manager',
            'Top Code Contributor',
            'Most Improved Employee',
            'Best New Hire',
            'Best Presentation',
            'Best Quality Control',
            'Best Technical Writer',
            'Most Valuable Employee',
            'Star Performer Award'
        ];

        $awardInsert = [];

        foreach ($awardList as $award) {
            $awardInsert[] = [
                'award_icon_id' => $awardIcons[array_rand($awardIcons)],
                'color_code' => $iconColors[array_rand($iconColors)],
                'title' => $award,
                'company_id' => $companyId,
            ];
        }

        Award::insert($awardInsert);

        $employees = User::allEmployees(null, false, null, $companyId)->pluck('id')->toArray();
        $awards = Award::where('company_id', $companyId)->get()->pluck('id')->toArray();

        $date = fake()->randomElement([fake()->dateTimeThisMonth()->format('Y-m-d'), fake()->dateTimeThisYear()->format('Y-m-d')]);

        $appreciations = [];

        for ($i = 0; $i < 10; $i++) {

            $appreciations[] = [
                'award_to' => $employees[array_rand($employees)],
                'award_id' => $awards[array_rand($awards)],
                'company_id' => $companyId,
                'award_date' => $date,
                'added_by' => $employees[array_rand($employees)],
            ];
        }

        Appreciation::insert($appreciations);
    }

}
