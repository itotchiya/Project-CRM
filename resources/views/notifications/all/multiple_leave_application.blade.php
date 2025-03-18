@php
    use App\Models\User;$notificationUser = User::find($notification->data['user_id']);
@endphp

@if ($notificationUser)
    <x-cards.notification :notification="$notification" :link="route('leaves.index')"
                          :image="$notificationUser->image_url" :title="__('email.leave.applied')"
                          :time="$notification->created_at"/>
@endif
