<?php

namespace App\Observers;

use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\User;
use App\Models\UserLeadboardSetting;
use Illuminate\Support\Str;

class LeadStageObserver
{

    public function created(PipelineStage $leadStages)
    {
        $employees = User::allEmployees();

        foreach ($employees as $item) {
            UserLeadboardSetting::create([
                'user_id' => $item->id,
                'pipeline_stage_id' => $leadStages->id
            ]);
        }
    }

    public function deleting(PipelineStage $leadStages)
    {
        $defaultStage = PipelineStage::where('default', 1)->first();
        abort_403($defaultStage->id == $leadStages->id);

        Deal::where('pipeline_stage_id', $leadStages->id)->update(['pipeline_stage_id' => $defaultStage->id]);
    }

    public function creating(PipelineStage $leadStages)
    {
        if (company()) {
            $leadStages->company_id = company()->id;
        }

        $leadStages->slug = Str::slug($leadStages->name);
    }

}
