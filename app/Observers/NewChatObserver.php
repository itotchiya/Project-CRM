<?php

namespace App\Observers;

use App\Events\NewChatEvent;
use App\Events\NewMentionChatEvent;
use App\Events\NewMessage;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserChat;
use Illuminate\Support\Facades\Config;

class NewChatObserver
{

    public function created(UserChat $userChat)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if ((request()->user_id == request()->mention_user_id) && request()->mention_user_id != null && request()->mention_user_id != '') {
                $userChat->mentionUser()->sync(request()->mention_user_id);
                $mentionUserIds = explode(',', request()->mention_user_id);
                $mentionUser = User::whereIn('id', $mentionUserIds)->get();
                event(new NewMentionChatEvent($userChat, $mentionUser));

            }
            else {
                event(new NewChatEvent($userChat));

            }

            if (pusher_settings()->status == 1 && pusher_settings()->messages == 1) {
                Config::set('queue.default', 'sync'); // Set intentionally for instant delivery of messages
                broadcast(new NewMessage($userChat))->toOthers()->via('pusher');
            }
        }
    }

    public function creating(UserChat $userChat)
    {
        if (company()) {
            $userChat->company_id = company()->id;
        }
    }

    public function deleting(UserChat $userChat)
    {
        $notifyData = ['App\Notifications\NewChat'];

        Notification::deleteNotification($notifyData, $userChat->id);

    }

}
