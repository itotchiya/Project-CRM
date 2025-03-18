<?php

namespace App\Http\Requests;

use App\Http\Requests\CoreRequest;

class StoreDealNote extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'details' => [
                'required',
                function ($attribute, $value, $fail) {
                    $comment = trim_editor($value);;

                    if ($comment == '') {
                        $fail(__('validation.required'));
                    }
                }
            ]
        ];
    }

}
