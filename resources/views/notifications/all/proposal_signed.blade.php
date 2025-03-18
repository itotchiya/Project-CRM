@if ($notification->data['status'] == 'accepted')
    <x-cards.notification :notification="$notification" :link="route('proposals.show', $notification->data['id'])"
                          :image="company()->logo_url" :title="__('email.proposalSigned.subject')"
                          :text="$notification->data['proposal_number']"
                          :time="$notification->created_at"/>
@else
    <x-cards.notification :notification="$notification" :link="route('proposals.show', $notification->data['id'])"
                          :image="company()->logo_url" :title="__('email.proposalRejected.subject')"
                          :text="$notification->data['proposal_number']"
                          :time="$notification->created_at"/>
@endif
