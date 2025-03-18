<?php

namespace App\Http\Requests\ProjectMembers;

use App\Http\Requests\CoreRequest;

class SaveGroupMembers extends CoreRequest
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
            'group_id.0' => 'required',
            'project_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'group_id.0.required' => __('validation.selectAtLeastOne'),
        ];
    }

}
