<?php

namespace App\Http\Requests\FollowUp;

use App\Http\Requests\CoreRequest;
use App\Models\Deal;

class StoreRequest extends CoreRequest
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
        $deal = Deal::findOrFail($this->deal_id);
        $setting = company();

        $rules = [];

        if(request()->has('send_reminder')){
            $rules['remind_time'] = 'required';
        }

        $rules['next_follow_up_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:'.$deal->created_at->format($setting->date_format);

        return $rules;
    }

}
