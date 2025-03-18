<?php

namespace App\Http\Requests\Invoices;

use App\Http\Requests\CoreRequest;

class UpdateRecurringInvoice extends CoreRequest
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
        ];

        if ($this->invoice_count == 0) {
            $rules['issue_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:'.now()->format($setting->date_format);
            $rules['currency_id'] = 'required';
            $rules['client_id'] = 'required';

        }

        if ($this->show_shipping_address == 'on') {
            $rules['shipping_address'] = 'required';
        }

        if ($this->project_id == '' && $this->invoice_count == 0) {
            $rules['client_id'] = 'required';
        }

        return $rules;
    }

}
