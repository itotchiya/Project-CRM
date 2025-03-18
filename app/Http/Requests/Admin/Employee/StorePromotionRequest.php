<?php

namespace App\Http\Requests\Admin\Employee;

use App\Http\Requests\CoreRequest;

class StorePromotionRequest extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:"' . company()->date_format . '"',
            'current_designation_id' => 'required|numeric',
            'current_department_id' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'current_designation_id.required' => __('messages.SelectaDesignation'),
            'current_department_id.required' => __('messages.SelectaDesignation'),
        ];
    }

}
