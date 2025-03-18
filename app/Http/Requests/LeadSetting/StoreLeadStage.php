<?php

namespace App\Http\Requests\LeadSetting;

use App\Http\Requests\CoreRequest;
use Illuminate\Validation\Rule;

class StoreLeadStage extends CoreRequest
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
        $rule = [
            'pipeline' => 'required',
            'label_color' => 'required'
        ];

        if($this->pipeline)
        {
            $rule['name'] = [
                'required',
                Rule::unique('pipeline_stages', 'name')->where('company_id', company()->id)->whereIn('lead_pipeline_id', $this->pipeline),
            ];
        }

        return $rule;
    }

}
