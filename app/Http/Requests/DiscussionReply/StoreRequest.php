<?php

namespace App\Http\Requests\DiscussionReply;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
                'description' => trim_editor($this->description)
            ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'description' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'description' => __('app.reply'),
        ];
    }

}
