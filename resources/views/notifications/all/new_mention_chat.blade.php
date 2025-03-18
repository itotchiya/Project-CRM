@php
    use App\Models\User;use App\UserChat;$notificationUser = User::find($notification->data['user_one']);
    if (!isset($notification->data['from_name'])) {
        $chat = UserChat::with('fromUser')->find($notification->data['id']);
        $fromName = $chat->fromUser->name;
    } else {
        $fromName = $notification->data['from_name'];
    }
@endphp
@if ($notificationUser)
    <x-cards.notification :notification="$notification"
                          :link="route('messages.index') . '?user=' . $notification->data['user_one']"
                          :image="$notificationUser->image_url" :title="__('email.newChat.mentionSubject')"
                          :text="$notificationUser->name"
                          :time="$notification->created_at"/>
@endif
