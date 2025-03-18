<?php

namespace App\Http\Requests\Notice;

use App\Http\Requests\CoreRequest;

class StoreNotice extends CoreRequest
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
            'heading' => 'required',
            'employees.0' => 'required_if:to,employee',
            'clients.0' => 'required_if:to,client',
        ];
    }

    public function messages()
    {
        return [
            'employees.0.required_if' => __('messages.atleastOneValidation'),
            'clients.0.required_if' => __('messages.atleastOneValidation')
        ];
    }

}
