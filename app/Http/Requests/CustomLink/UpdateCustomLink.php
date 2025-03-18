<?php

namespace App\Http\Requests\CustomLink;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomLink extends FormRequest
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
        return [
            'link_title' => 'required',
            'url' => 'required|url',
            'can_be_viewed_by' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'can_be_viewed_by.required' => __('messages.atleastOneRole')
        ];
    }
    
}
