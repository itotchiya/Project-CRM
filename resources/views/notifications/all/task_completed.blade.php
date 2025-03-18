@php
    use App\Models\TaskHistory;$notificationUser = TaskHistory::where('task_id', $notification->data['id'])
        ->orderByDesc('id')
        ->first();
@endphp
@if ($notificationUser)
    <x-cards.notification :notification="$notification" :link="route('tasks.show', $notification->data['id'])"
                          :image="$notificationUser->user->image_url" :title="__('email.taskComplete.subject')"
                          :text="$notification->data['heading']" :time="$notification->created_at"/>
@endif
