@php
    use App\Models\Discussion;use App\Models\DiscussionReply;use App\Models\User;if (!isset($notification->data['discussion_id'])) {
        $discussionReply = DiscussionReply::with('discussion')->find($notification->data['id']);
        $projectId = $discussionReply->discussion->project_id;
        $notificationUser = User::find($discussionReply->user_id);
    } else {
        $discussion = Discussion::find($notification->data['discussion_id']);
        $projectId = $notification->data['project_id'];
        $notificationUser = User::find($discussion->user_id);
    }
    $route = route('projects.show', $projectId) . '?tab=discussion';
@endphp

@if ($notificationUser)
    <x-cards.notification :notification="$notification" :link="$route" :image="$notificationUser->image_url"
                          :title="$notification->data['user'] . ' ' . __('email.discussionReply.subject')"
                          :text="$notification->data['title']" :time="$notification->created_at"/>
@endif
