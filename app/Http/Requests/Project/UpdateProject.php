<?php

namespace App\Http\Requests\Project;

use App\Models\Project;
use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdateProject extends CoreRequest
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
        $rules = [
            'project_name' => 'required|max:150',
            'start_date' => 'required',
            'hours_allocated' => 'nullable|numeric',
            'client_id' => 'requiredIf:client_view_task,true',
            'project_code' => $this->project_code != '' ? 'unique:projects,project_short_code,' . $this->project_id . ',id,company_id,' . company()->id : '',
        ];

        if (!$this->has('without_deadline')) {
            $rules['deadline'] = 'required';
        }

        if ($this->project_budget != '') {
            $rules['project_budget'] = 'numeric';
            $rules['currency_id'] = 'required';
        }

        $project = Project::findOrFail(request()->project_id);

        if (request()->private && in_array('employee', user_roles()))  {
            $rules['user_id.0'] = 'required';
        }

        if ($project->public == 0 && !request()->has('public')) {
            if (!request()->has('member_id') || (!request()->private && !request()->public)) {
                $rules['member_id.0'] = 'required';
            }
        }

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function messages()
    {
        return [
            'user_id.0.required' => __('messages.atleastOneValidation'),
            'project_code.required' => __('messages.projectCodeRequired'),
            'member_id.0.required' => __('messages.atleastOneValidation')
        ];
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
