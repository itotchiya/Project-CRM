<?php

namespace App\Http\Requests\TimeLogs;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreTimeLog extends CoreRequest
{
    use CustomFieldsRequestTrait;

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
        $rules = array();

        $rules['start_time'] = 'required';
        $rules['end_time'] = 'required';
        $rules['memo'] = 'required';
        $rules['task_id'] = 'required';
        $rules['user_id'] = 'required';

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    public function messages()
    {
        return [
            'project_id.required' => __('messages.chooseProject'),
            'task_id.required' => __('messages.fieldBlank'),
            'user_id.required' => __('messages.fieldBlank'),
        ];
    }

}
