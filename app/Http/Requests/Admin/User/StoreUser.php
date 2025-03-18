<?php

namespace App\Http\Requests\Admin\User;

use App\Http\Requests\CoreRequest;

class StoreUser extends CoreRequest
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
            'name' => 'required:max:50',
            'email' => 'required|email:rfc,strict|unique:users,email,null,id,company_id,' . company()->id,
            'password' => 'required|min:6',
            'slack_username' => 'nullable|unique:employee_details,slack_username,null,id,company_id,' . company()->id,
            'hourly_rate' => 'nullable|numeric',
            'joining_date' => 'required'
        ];
    }

}
