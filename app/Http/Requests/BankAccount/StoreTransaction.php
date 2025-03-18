<?php

namespace App\Http\Requests\BankAccount;

use App\Http\Requests\CoreRequest;
use App\Models\BankAccount;

class StoreTransaction extends CoreRequest
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
            'amount' => 'required',
        ];

        if (request('from_bank_account') != '') {
            $bankBalance = BankAccount::findOrFail(request('from_bank_account'));

            $rules = ['amount' => 'required|numeric|max:'.$bankBalance->bank_balance];
        }

        if ($this->type == 'account')
        {
            $rules['to_bank_account'] = 'required';
        }

        return $rules;
    }

}
