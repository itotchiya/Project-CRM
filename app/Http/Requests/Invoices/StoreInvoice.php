<?php

namespace App\Http\Requests\Invoices;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Validation\Rule;

class StoreInvoice extends CoreRequest
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

    protected function prepareForValidation()
    {
        if ($this->invoice_number) {
            $this->merge([
                'invoice_number' => \App\Helper\NumberFormat::invoice($this->invoice_number),
            ]);
        }
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
            'invoice_number' => [
                'required',
                Rule::unique('invoices')->where('company_id', company()->id)
            ],
            'issue_date' => 'required',
            'sub_total' => 'required',
            'total' => 'required',
            'currency_id' => 'required',
            'exchange_rate' => 'required',
            'gateway' => 'required_if:payment_status,1',
            'offline_methods' => 'required_if:gateway,Offline',
        ];

        if ($this->has('due_date')) {
            $rules['due_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:'.$this->issue_date;
        }

        $rules['client_id'] = 'required';

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    public function messages()
    {
        return [
            'client_id.required' => __('modules.projects.selectClient'),
            'gateway.required_if' => __('modules.projects.selectPayment')
        ];
    }

}
