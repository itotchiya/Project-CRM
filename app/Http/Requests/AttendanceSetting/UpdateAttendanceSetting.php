<?php

namespace App\Http\Requests\AttendanceSetting;

use App\Http\Requests\CoreRequest;

class UpdateAttendanceSetting extends CoreRequest
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
        $data = [];

        if($this->radius_check == 'yes')
        {
            $data['radius'] = 'required';
        }

        if($this->has('alert_after_status') && $this->has('alert_after_status') == 'on')
        {
            $data['alert_after'] = 'required';
        }

        if($this->has('monthly_report') && $this->has('monthly_report') == '1')
        {
            $data['monthly_report_roles'] = 'required';
        }

        return $data;
    }

}
