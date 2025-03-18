<?php

namespace App\Http\Requests;

class UpdateQuickBooksSetting extends CoreRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        if (!request()->has('status')) {
            return [];
        }

        $rules = ['environment' => 'required|in:Production,Development'];

        if ($this->environment == 'Development') {
            $rules['sandbox_client_id'] = 'required';
            $rules['sandbox_client_secret'] = 'required';
        }
        else {
            $rules['client_id'] = 'required';
            $rules['client_secret'] = 'required';
        }

        return $rules;
    }

}
