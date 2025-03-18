<?php

namespace App\Http\Requests;

use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEstimate extends FormRequest
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
        if ($this->estimate_number && is_numeric($this->estimate_number)) {
            $this->merge([
                'estimate_number' => \App\Helper\NumberFormat::estimate($this->estimate_number),
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
        $rules = [
            'estimate_number' => [
                'required',
                /** @phpstan-ignore-next-line */
                Rule::unique('estimates')->where('company_id', company()->id)
                    ->when($this->route('estimate'), function ($q) {
                        /** @phpstan-ignore-next-line */
                        $q->where('id', '<>', $this->route('estimate'));
                    })
            ],
            'client_id' => 'required',
            'valid_till' => 'required',
            'sub_total' => 'required',
            'total' => 'required',
            'currency_id' => 'required'
        ];

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
            'client_id.required' => __('modules.projects.selectClient')
        ];
    }

}
