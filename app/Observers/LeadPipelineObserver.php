<?php

namespace App\Observers;

use App\Models\LeadPipeline;
use App\Models\PipelineStage;
use Illuminate\Support\Str;

class LeadPipelineObserver
{

    public function creating(LeadPipeline $pipeline)
    {
        if (company()) {
            $pipeline->company_id = company()->id;
        }

        $pipeline->slug = Str::slug($pipeline->name, '-');
    }

    public function created(LeadPipeline $pipeline)
    {
        if (company()) {

            $pipelineStages = [
                ['name' => 'Generated', 'slug' => 'generated', 'lead_pipeline_id' => $pipeline->id, 'priority' => 1, 'default' => 1, 'label_color' => '#FFE700', 'company_id' => company()->id],
                ['name' => 'On going', 'slug' => 'on-going', 'lead_pipeline_id' => $pipeline->id, 'priority' => 2, 'default' => 0, 'label_color' => '#009EFF', 'company_id' => company()->id],
                ['name' => 'Win', 'slug' => 'win', 'lead_pipeline_id' => $pipeline->id, 'priority' => 3, 'default' => 0, 'label_color' => '#1FAE07', 'company_id' => company()->id],
                ['name' => 'Lost', 'slug' => 'lost', 'lead_pipeline_id' => $pipeline->id, 'priority' => 4, 'default' => 0, 'label_color' => '#DB1313', 'company_id' => company()->id]
            ];

            PipelineStage::insert($pipelineStages);

        }
    }

}
