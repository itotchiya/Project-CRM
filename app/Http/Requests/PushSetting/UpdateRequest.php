<?php

namespace App\Http\Requests\PushSetting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        $rules = [];

        if (request()->get('status') == 'active') {
            $rules['onesignal_app_id'] = 'required';
            $rules['onesignal_rest_api_key'] = 'required';
        }

        if (request()->get('beams_push_status') == 'active') {
            $rules['instance_id'] = 'required';
            $rules['beam_secret'] = 'required';
        }

        return $rules;
    }

}
