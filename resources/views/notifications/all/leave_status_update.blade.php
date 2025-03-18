@php
    use App\Models\User;use Carbon\Carbon;$notificationUser = User::find($notification->data['user_id']);
@endphp

@if ($notificationUser)
    <x-cards.notification :notification="$notification" :link="route('leaves.show', $notification->data['id'])"
                          :image="$notificationUser->image_url"
                          :title="__('email.leaves.statusSubject')"
                          :text="Carbon::parse($notification->data['leave_date'])->translatedFormat(company()->date_format)"
                          :time="$notification->created_at"/>
@endif
