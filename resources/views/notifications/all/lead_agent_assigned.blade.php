@php
    use App\Models\User;if(isset($notification->data['added_by']))
    {
        $notificationUser = User::find($notification->data['added_by']);
    }
    else
    {
        $notificationUser = User::find(user()->id);
    }
@endphp

@if ($notificationUser)
    <x-cards.notification :notification="$notification" :link="route('deals.show', $notification->data['id'])"
                          :image="$notificationUser->image_url"
                          :title="__('email.leadAgent.subject')"
                          :text="$notification->data['name']"
                          :time="$notification->created_at"/>
@endif
