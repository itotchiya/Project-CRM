<?php

namespace App\Http\Requests\LeadSetting;

use App\Http\Requests\CoreRequest;

class StoreLeadAgent extends CoreRequest
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
            'agent_id' => 'required',
            'category_id.0' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'category_id.0.required' => __('messages.atleastOneCategory'),
        ];
    }

}
