<?php

namespace App\Http\Requests\Expenses;

use App\Http\Requests\CoreRequest;

class StoreExpenseCategory extends CoreRequest
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
        return [
            'category_name' => 'sometimes|required|unique:expenses_category,category_name,null,id,company_id,' . company()->id
        ];
    }

}
