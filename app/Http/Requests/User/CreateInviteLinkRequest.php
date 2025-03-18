<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateInviteLinkRequest extends FormRequest
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
            'allow_email' => 'required',
        ];

        if ($this->allow_email === 'selected') {
            $rules['email_domain'] = 'required|regex:/^(?!:\/\/)(?=.{1,255}$)((.{1,63}\.){1,127}(?![0-9]*$)[a-z0-9-]+\.?)$/i';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'email_domain.regex' => __('validation.email_domain')
        ];
    }

}
