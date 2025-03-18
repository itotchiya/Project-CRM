<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisaRequest extends FormRequest
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
            'visa_number' => 'required|unique:visa_details,visa_number,' . $this->route('employee_visa').',id,company_id,' . $setting->id,
            'issue_date' => 'required',
            'expiry_date' => 'required|date_format:"' . $setting->date_format . '"|after_or_equal:issue_date',
            'country' => 'required'

        ];
    }

}
