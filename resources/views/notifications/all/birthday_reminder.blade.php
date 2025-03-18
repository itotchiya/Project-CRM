@php
    use App\Models\User;use Carbon\Carbon;$name = '';
    $count = count($notification->data['birthday_name']) - 1;
    $name = $notification->data['birthday_name'][0]['name'];
    $date = Carbon::parse($notification->data['birthday_name'][0]['date_of_birth'])->timezone($global->timezone)->translatedFormat('Y-m-d');
    $formatDate = Carbon::parse($date)->timezone($global->timezone)->translatedFormat('j M');

    if ($count == 0) {
        $title = $name . ' ' . __('email.BirthdayReminder.birthdayNotification');
    }
    else {
        $title = $name . ' ' . __('app.and') . ' ' . $count . __('email.BirthdayReminder.birthdayNotificationText');
    }

    $notificationUser = User::find($notification->data['birthday_name'][0]['id']);

@endphp

@if ($notificationUser)
    @if (Carbon::today()->timezone($global->timezone)->toDateString() == $date)
        <x-cards.notification :notification="$notification" :link="route('dashboard')"
                              :image="$notificationUser->image_url"
                              :title="$title . ' ' . __('app.today')" :time="$notification->created_at"/>
    @else
        <x-cards.notification :notification="$notification" :link="route('dashboard')"
                              :image="$notificationUser->image_url"
                              :title="$title  . ' ' . $formatDate" :time="$notification->created_at"/>
    @endif
@endif
