<?php

namespace App\Http\Requests\Admin\Client;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreClientRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

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
            'name' => 'required',
            'email' => 'nullable|email:rfc,strict|required_if:login,enable|unique:users,email,null,id,company_id,' . company()->id,
            'password' => 'nullable|required_if:login,enable|min:8',
            'slack_username' => 'nullable',
            'website' => 'nullable|url',
            'country' => 'required_with:mobile',
            'mobile' => 'nullable|numeric'
        ];

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function messages()
    {
        return [
            'website.url' => 'The website format is invalid. Add https:// or http to url'
        ];
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
