@php
    use App\Models\User;$notificationUser = User::find($notification->data['user_id']);
@endphp

@if ($notificationUser)
    <x-cards.notification :notification="$notification" :link="route('leaves.show', $notification->data['id'])"
                          :image="$notificationUser->image_url"
                          :title="__('email.leaves.subject')" :text="$notification->data['user']['name']"
                          :time="$notification->created_at"/>
@endif
