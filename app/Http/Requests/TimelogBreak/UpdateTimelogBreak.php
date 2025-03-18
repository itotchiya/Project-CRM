<?php

namespace App\Http\Requests\TimelogBreak;

use App\Models\ProjectTimeLog;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimelogBreak extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [];
        $timelog = ProjectTimeLog::find(request('timelog_id'));
        $rules['start_time'] = 'required|after_or_equal:"' . $timelog->start_time->timezone(company()->timezone) . '"';
        $rules['end_time'] = 'required|before_or_equal:"' . $timelog->end_time->timezone(company()->timezone) . '"';
        return $rules;
    }

}
