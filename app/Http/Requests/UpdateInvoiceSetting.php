<?php

namespace App\Http\Requests;

class UpdateInvoiceSetting extends CoreRequest
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
     * @return array
     */
    public function rules()
    {
        $rules = [
            'due_after' => 'required|numeric',
            'invoice_terms' => 'required'
        ];

        return $rules;
    }

}
