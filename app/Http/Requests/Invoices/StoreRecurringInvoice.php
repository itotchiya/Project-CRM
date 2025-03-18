<?php

namespace App\Http\Requests\Invoices;

use App\Http\Requests\CoreRequest;

class StoreRecurringInvoice extends CoreRequest
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
        $this->has('show_shipping_address') ? $this->request->add(['show_shipping_address' => 'yes']) : $this->request->add(['show_shipping_address' => 'no']);

        $setting = company();

        $rules = [
            'sub_total' => 'required',
            'total' => 'required',
            'currency_id' => 'required',
            'billing_cycle' => 'required'
        ];

        if (!$this->has('immediate_invoice')) {
            $rules['issue_date'] = 'required|date_format:"' . $setting->date_format . '"|after:'.now()->format($setting->date_format);
        }

        if ($this->show_shipping_address == 'on') {
            $rules['shipping_address'] = 'required';
        }

        if ($this->project_id == '') {
            $rules['client_id'] = 'required';
        }

        return $rules;
    }

}
