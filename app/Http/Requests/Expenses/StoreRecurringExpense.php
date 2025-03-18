<?php

namespace App\Http\Requests\Expenses;

use App\Http\Requests\CoreRequest;
use App\Models\BankAccount;

class StoreRecurringExpense extends CoreRequest
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
        $rotation = $this->get('rotation');
        $setting = company();

        $rules = [
            'item_name' => 'required',
            'price' => 'required|numeric',
            'billing_cycle' => 'required',
        ];


        if (request('bank_account_id') != '') {
            $bankBalance = BankAccount::findOrFail(request('bank_account_id'));

            $rules['price'] = 'required|numeric|max:'.$bankBalance->bank_balance;
        }

        $issueDate = $this->get('hidden_issue_date', $this->get('issue_date'));

        if (!$this->has('immediate_expense') && !$this->has('hidden_issue_date')) {
            $rules['issue_date'] = 'required|date_format:"' . $setting->date_format . '"|after:'.now()->format($setting->date_format);
        }

        return $rules;
    }

}
