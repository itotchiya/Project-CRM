<?php

namespace App\Observers;

use App\Events\NewNoticeEvent;
use App\Models\Notice;
use App\Models\NoticeView;
use App\Models\Notification;
use App\Models\UniversalSearch;
use App\Models\User;

class NoticeObserver
{

    public function saving(Notice $notice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $notice->last_updated_by = user()->id;

            if (request()->_method == 'PUT') {
                $this->sendNotification($notice, 'update');
            }
        }
    }

    public function creating(Notice $notice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $notice->added_by = user()->id;
        }

        if (company()) {
            $notice->company_id = company()->id;
        }
    }

    public function created(Notice $notice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $this->sendNotification($notice);
        }
    }

    public function deleting(Notice $notice)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $notice->id)->where('module_type', 'notice')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        $notifyData = ['App\Notifications\NewNotice', 'App\Notifications\NoticeUpdate'];
        Notification::deleteNotification($notifyData, $notice->id);

    }

    public function sendNotification($notice, $action = 'create')
    {
        if ($notice->to == 'employee') {
            $empIds = request()->employees;
            $users = $users = User::whereIn('id', $empIds)->where('status', 'active')->get();

            foreach ($users as $userData) {
                NoticeView::updateOrCreate(array(
                    'user_id' => $userData->id,
                    'notice_id' => $notice->id
                ));
            }

            event(new NewNoticeEvent($notice, $users, $action));
        }

        if ($notice->to == 'client') {
            $clientIds = request()->clients;
            $users = $users = User::whereIn('id', $clientIds)->where('status', 'active')->get();

            foreach ($users as $userData) {
                NoticeView::updateOrCreate(array(
                    'user_id' => $userData->id,
                    'notice_id' => $notice->id
                ));
            }

            event(new NewNoticeEvent($notice, $users, $action));
        }

    }

}
