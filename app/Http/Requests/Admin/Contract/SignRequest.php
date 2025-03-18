<?php

namespace App\Http\Requests\Admin\Contract;

use Illuminate\Foundation\Http\FormRequest;

class SignRequest extends FormRequest
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
            'first_name' => 'required',
            'last_name' => 'required',
            'place' => 'required',
            'email' => 'required|email:rfc,strict',
        ];

        if(request('signature_type') == 'upload'){
            $rules['image'] = 'required';
        }
        else {
            $rules['signature'] = 'required';
        }

        return $rules;
    }

}
