<?php

namespace App\Http\Requests\TimeLogSetting;

use App\Http\Requests\CoreRequest;

class UpdateTimeLog extends CoreRequest
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

        if($this->has('timelog_report') && $this->timelog_report == 1)
        {
            $data['daily_report_roles'] = 'required';
        }

        return $data;
    }

}
