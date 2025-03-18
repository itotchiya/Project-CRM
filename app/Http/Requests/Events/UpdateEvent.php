<?php

namespace App\Http\Requests\Events;

use App\Http\Requests\CoreRequest;

class UpdateEvent extends CoreRequest
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
        $setting = company();
        $rules = [
            'event_name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required|date_format:"' . $setting->date_format . '"|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required',
            'all_employees' => 'sometimes',
            'where' => 'required',
            'user_id.0' => 'required_unless:all_employees,true',
            'description' => 'required',
            'event_link' => 'nullable|url'
        ];

        if ($this->start_date == $this->end_date) {
            $rules['end_time'] = 'required|after_or_equal:start_time';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'user_id.0.required_unless' => __('messages.atleastOneValidation'),
            'end_time.after_or_equal' => __('messages.endTimeAfterOrEqual'),
        ];
    }

}
