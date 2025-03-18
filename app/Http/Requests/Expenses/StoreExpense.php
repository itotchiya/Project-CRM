<?php

namespace App\Http\Requests\Expenses;

use App\Models\BankAccount;
use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreExpense extends CoreRequest
{
    use CustomFieldsRequestTrait;

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
            'item_name' => 'required',
            'purchase_date' => 'required',
            'price' => 'required|numeric',
            'currency_id' => 'required'
        ];

        $rules = $this->customFieldRules($rules);


        if (request('bank_account_id') != '') {
            $bankBalance = BankAccount::findOrFail(request('bank_account_id'));

            $rules['price'] = 'required|numeric|max:'.$bankBalance->bank_balance;
        }

        return $rules;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
