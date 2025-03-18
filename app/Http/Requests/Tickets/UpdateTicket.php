<?php

namespace App\Http\Requests\Tickets;

use App\Http\Requests\CoreRequest;

class UpdateTicket extends CoreRequest
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
            'user_id' => 'required_if:type,note',
            'message2' => 'required_if:type,note',
        ];
    }

    public function messages()
    {
        return [
            'user_id' => __('messages.agentFieldRequired'),
            'message2' => __('messages.descriptionFieldRequired'),
        ];
    }

}
