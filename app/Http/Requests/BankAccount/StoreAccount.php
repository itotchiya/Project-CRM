<?php

namespace App\Http\Requests\BankAccount;

use App\Http\Requests\CoreRequest;

class StoreAccount extends CoreRequest
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
            'account_name' => 'required',
            'opening_balance' => 'required',
            'status' => 'required',
            'contact_number' => 'required',
            'currency_id' => 'required',
        ];

        if (request('type') == 'bank')
        {
            $rules['bank_name'] = 'required';
            $rules['account_number'] = 'required';
        }

        return $rules;
    }

}
