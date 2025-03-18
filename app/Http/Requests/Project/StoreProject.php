<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreProject extends CoreRequest
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
        $setting = company();

        $rules = [
            'project_name' => 'required|max:150',
            'start_date' => 'required|date_format:"' . $setting->date_format . '"',
            'hours_allocated' => 'nullable|numeric',
            'client_id' => 'requiredIf:client_view_task,true',
            'project_code' => $this->project_code != '' ? 'unique:projects,project_short_code,null,id,company_id,' . company()->id : '',
            'miroboard_checkbox' => 'nullable',
            'miro_board_id' => 'nullable|required_if:miroboard_checkbox,checked'
        ];

        if (!request()->public && in_array('employee', user_roles())) {
            $rules['user_id.0'] = 'required';
        }

        if (!$this->has('without_deadline')) {
            $rules['deadline'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:start_date';
        }

        if ($this->has('miroboard_checkbox')) {
            $rules['miro_board_id'] = 'required';
        }

        if ($this->project_budget != '') {
            $rules['project_budget'] = 'numeric';
            $rules['currency_id'] = 'required';
        }

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function messages()
    {
        return [
            'user_id.0.required' => __('messages.atleastOneValidation'),
            'project_code.required' => __('messages.projectCodeRequired'),
        ];
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
