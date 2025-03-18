<?php

namespace App\Http\Requests;

class ChatStoreRequest extends CoreRequest
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

    public function prepareForValidation()
    {
        $this->merge([
            'message' => trim_editor($this->message),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    public function rules()
    {

        $rules = [
            'user_id' => 'required_if:user_type,employee',
            'client_id' => 'required_if:user_type,client',
        ];

        if($this->types == 'modal'){
            $rules['message'] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'user_id.required_if' => 'Select a user to send the message',
            'client_id.required_if' => 'Select a client to send the message',
        ];
    }

}
