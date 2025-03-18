<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadAgent;
use App\Models\LeadCategory;
use App\Models\LeadPipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {

        $faker = \Faker\Factory::create();

        $count = config('app.seed_record_count');
        $leadCategories = [
            [
                'category_name' => 'Best Case',
                'company_id' => $companyId
            ],
            [
                'category_name' => 'Closed',
                'company_id' => $companyId
            ],
            [
                'category_name' => 'Commit',
                'company_id' => $companyId
            ],
        ];

        LeadCategory::insert($leadCategories);

        $leadAgents = User::select('users.id')
            ->join('employee_details', 'users.id', '=', 'employee_details.user_id')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'employee')
            ->where('users.company_id', $companyId)
            ->inRandomOrder()
            ->get()->pluck('id')
            ->toArray();

        $categories = $this->getCategories($companyId);

        for ($i = 1; $i <= 4; $i++) {
            $agent = new LeadAgent();
            $agent->company_id = $companyId;
            $agent->user_id = $faker->randomElement($leadAgents);
            $agent->lead_category_id = $faker->randomElement($categories);
            $agent->save();
        }

        $currencyID = Currency::where('company_id', $companyId)->first()->id;

        $randomLeadId = LeadAgent::where('company_id', $companyId)->inRandomOrder()->first()->id;

        $randomPipelineId = LeadPipeline::where('company_id', $companyId)->inRandomOrder()->first()->id;
        $randomStageId = PipelineStage::where('company_id', $companyId)->where('lead_pipeline_id', $randomPipelineId)->inRandomOrder()->first()->id;

        foreach (range(0, 10) as $number) {
            $leadContact = new Lead();
            $leadContact->company_id = $companyId;
            $leadContact->website = 'https://worksuite.biz';
            $leadContact->address = $faker->address;
            $leadContact->client_name = $faker->name;
            $leadContact->company_name = $faker->company;
            $leadContact->client_email = 'fake@example.com';
            $leadContact->mobile = $faker->phoneNumber;
            $leadContact->note = 'Quas consectetur, tempor incidunt, aliquid voluptatem, velit mollit et illum, adipisicing ea officia aliquam placeat';
            $leadContact->save();
        }

        $lead = new Deal();
        $lead->lead_id = $leadContact->id;
        $lead->lead_pipeline_id = $randomPipelineId;
        $lead->pipeline_stage_id = $randomStageId;
        $lead->company_id = $companyId;
        $lead->agent_id = $randomLeadId;
        $lead->name = 'Buying Worksuite';
        $lead->value = rand(10000, 99999);
        $lead->currency_id = $currencyID;
        $lead->next_follow_up = 'yes';
        $lead->note = 'Quas consectetur, tempor incidunt, aliquid voluptatem, velit mollit et illum, adipisicing ea officia aliquam placeat';
        $lead->save();

    }

    private function getCategories($companyId)
    {
        return LeadCategory::inRandomOrder()
            ->where('company_id', $companyId)
            ->get()->pluck('id')
            ->toArray();
    }

}
