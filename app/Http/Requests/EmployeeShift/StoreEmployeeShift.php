<?php

namespace App\Http\Requests\EmployeeShift;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeShift extends FormRequest
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
        $data['office_start_time'] = 'required';
        $data['office_end_time'] = 'required';
        $data['halfday_mark_time'] = 'required';
        $data['shift_short_code'] = 'required';
        $data['color'] = 'required';
        $data['clockin_in_day'] = 'required | integer | min:0';

        if (!request()->has('office_open_days')) {
            $data['office_open_days'] = 'required';
        }

        if (request()->shift_type == 'flexible') {
            $data['total_shift_hours'] = 'required';
            $data['halfday_shift_hours'] = 'required';
            $data['auto_clockout'] = 'required';
        }

        if (request()->shift_type == 'strict') {
            $data['late_mark_duration'] = 'required | integer | min:0';
            $data['auto_clock_out_time'] = 'required';
        }

        return $data;
    }

}
