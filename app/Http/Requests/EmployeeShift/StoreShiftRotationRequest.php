<?php

namespace App\Http\Requests\EmployeeShift;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRotationRequest extends FormRequest
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
        $rotationId = request()->rotation_id;

        return [
            'rotation_name' => [
                'required',
                'unique:employee_shift_rotations,rotation_name,' . $rotationId . ',id,company_id,' . company()->id,
            ],
            'rotation_frequency' => 'required',
            'schedule_on' => 'required_if:rotation_frequency,weekly,bi-weekly',
            'rotation_date' => 'required_if:rotation_frequency,monthly',
            'color_code' => 'required',
        ];
    }

}
