<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEstimateRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => 'required',
            'description' => 'required',
            'currency_id' => 'required|integer',
        ];
    }

    public function attributes()
    {
        return [
            'client_id' => __('app.client'),
            'currency_id' => __('app.currency'),
        ];
    }

}
