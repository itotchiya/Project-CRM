@php
    use App\Models\EmployeeShift;use App\Models\User;use Carbon\Carbon;$notificationUser = User::find($notification->data['user_id']);
    $shift = EmployeeShift::find($notification->data['shift_id']);
@endphp

@if ($notificationUser && $shift)
    <x-cards.notification :notification="$notification" :link="route('dashboard')"
                          :image="$notificationUser->image_url"
                          :title="__('email.shiftScheduled.subject') . ' - '.Carbon::parse($notification->data['date'])->translatedFormat(company()->date_format)"
                          :text="__('modules.attendance.shiftName').': '.$shift->shift_name"
                          :time="$notification->created_at"/>
@endif
