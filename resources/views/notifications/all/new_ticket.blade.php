@php
    use App\Models\User;use App\Scopes\ActiveScope;$notificationUser = User::withoutGlobalScope(ActiveScope::class)->find($notification->data['user_id']);
@endphp

@if ($notificationUser)
    <x-cards.notification :notification="$notification"
                          :link="route('tickets.show', $notification->data['ticket_number'])"
                          :image="$notificationUser->image_url"
                          :title="__('email.newTicket.subject') . ' #' . $notification->data['ticket_number']"
                          :text="$notification->data['subject']"
                          :time="$notification->created_at"/>
@endif
