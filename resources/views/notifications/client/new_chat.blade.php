@php
$notificationUser = \App\Models\User::find($notification->data['user_one']);
if (!isset($notification->data['from_name'])) {
    $chat = \App\Models\UserChat::with('fromUser')->find($notification->data['id']);
    $fromName = $chat->fromUser->name;
} else {
    $fromName = $notification->data['from_name'];
}
@endphp

@if ($notificationUser)
    <x-cards.notification :notification="$notification"  :link="route('messages.index') . '?user=' . $notification->data['user_one']" :image="$notificationUser->image_url" :title="__('email.newChat.subject')"
        :text="$notificationUser->name"
        :time="$notification->created_at" />
@endif
