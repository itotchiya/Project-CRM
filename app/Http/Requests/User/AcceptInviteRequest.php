<?php

namespace App\Http\Requests\User;

use App\Models\UserInvitation;
use Illuminate\Foundation\Http\FormRequest;

class AcceptInviteRequest extends FormRequest
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
        $invite = UserInvitation::where('invitation_code', request()->invite)
            ->where('status', 'active')
            ->first();

        $rules = [
            'name' => 'required',
            'password' => 'required|min:8'
        ];

        if (request()->has('email_address')) {
            $rules['email_address'] = 'required';
        }

        $global = global_setting();

        if ($global && $global->sign_up_terms == 'yes') {
            $rules['terms_and_conditions'] = 'required';
        }

        $rules['email'] = 'required|email:rfc,strict|unique:users,email,null,id,company_id,' . $invite->company->id;

        return $rules;
    }

}
