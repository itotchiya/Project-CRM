<?php

namespace App\Http\Requests\LeadSetting;

use App\Http\Requests\CoreRequest;

class UpdateLeadStage extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:pipeline_stages,name,'.$this->route('lead_stage_setting').',id,company_id,' . company()->id.',lead_pipeline_id,' . $this->pipeline,
            'label_color' => 'required'
        ];
    }

}
