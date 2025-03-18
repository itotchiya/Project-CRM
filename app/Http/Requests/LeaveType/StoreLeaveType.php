<?php

namespace App\Http\Requests\LeaveType;

use App\Http\Requests\CoreRequest;

class StoreLeaveType extends CoreRequest
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
        $rules = [
            'type_name' => 'required',
            'color' => 'required',
            'gender' => 'required',
            'marital_status' => 'required',
            'department' => 'required',
            'designation' => 'required',
            'role' => 'required',
        ];

        if(!is_null(request('effective_after'))){
            $rules['effective_after'] = 'numeric|min:1';
        }

        if(!is_null(request('leavetype'))){
            $rules['leavetype'] = 'required';
        }

        return $rules;
    }

}
