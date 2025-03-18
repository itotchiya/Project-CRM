<?php

namespace App\Http\Requests\TicketAgentGroups;

use App\Http\Requests\CoreRequest;

class UpdateAgentGroup extends CoreRequest
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
            'groupId.0' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => __('messages.atleastOneValidation').' '.__('modules.tickets.agent'),
            'groupId.0.required' => __('modules.tickets.groupName').' '.__('app.required')
        ];
    }

}
