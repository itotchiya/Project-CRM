<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePassportRequest extends FormRequest
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
        $setting = company();

        return [
            'passport_number' => 'required|unique:passport_details,passport_number,' . $this->route('passport').',id,company_id,' . $setting->id,
            'issue_date' => 'required',
            'expiry_date' => 'required|date_format:"' . $setting->date_format . '"|after_or_equal:issue_date',
            'nationality' => 'required'
        ];
    }

}
