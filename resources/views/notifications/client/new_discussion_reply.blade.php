@php
if (!isset($notification->data['discussion_id'])) {
    $discussionReply = \App\Models\DiscussionReply::with('discussion')->find($notification->data['id']);
    $projectId = $discussionReply->discussion->project_id;
    $notificationUser = \App\Models\User::find($discussionReply->user_id);
} else {
    $discussion = \App\Models\Discussion::find($notification->data['discussion_id']);
    $projectId = $notification->data['project_id'];
    $notificationUser = \App\Models\User::find($discussion->user_id);
}
$route = route('projects.show', $projectId) . '?tab=discussion';
@endphp

@if ($notificationUser)
    <x-cards.notification :notification="$notification"  :link="$route" :image="$notificationUser->image_url"
        :title="$notification->data['user'] . ' ' . __('email.discussionReply.subject')"
        :text="$notification->data['title']" :time="$notification->created_at" />
@endif
