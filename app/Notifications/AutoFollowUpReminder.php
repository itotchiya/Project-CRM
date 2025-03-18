<?php

namespace App\Notifications;

use App\Models\DealFollowUp;
use App\Models\GlobalSetting;
use Illuminate\Support\Facades\App;
use App\Models\EmailNotificationSetting;

class AutoFollowUpReminder extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $leadFollowup;
    private $subject;
    private $emailSetting;

    public function __construct(DealFollowUp $leadFollowup,$subject)
    {
        $this->leadFollowup = $leadFollowup;
        $this->subject = $subject;
        $this->company = $leadFollowup->lead->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'follow-up-reminder')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        $mailSubject = ($this->subject) ?  __('email.followUpReminder.newFollowUpSubject') : __('email.followUpReminder.subject');
        $followUpLead = $this->leadFollowup?->lead?->name;

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, $mailSubject, $followUpLead);
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $build = parent::build($notifiable);
        $url = route('deals.show', $this->leadFollowup->lead->id) . '?tab=follow-up';

        $url = getDomainSpecificUrl($url, $this->company);

        $followUpLead = $this->leadFollowup?->lead?->name;

        $followUpDate = $this->leadFollowup?->next_follow_up_date->format($this->company->date_format);

        $followUpTime = $this->leadFollowup?->next_follow_up_date->format($this->company->time_format);

        $content = __('email.followUpReminder.followUpLeadText') .'<br><br>' .__('email.followUpReminder.followUpLead') . ' :- ' . $followUpLead . '<br>' . __('email.followUpReminder.nextFollowUpDate') . ' :- ' . $followUpDate . '<br>' . __('email.followUpReminder.nextFollowUpTime') . ' :- ' . $followUpTime . '<br>' . $this->leadFollowup->remark;
        $mailSubject = ($this->subject) ?  __('email.followUpReminder.newFollowUpSubject') : __('email.followUpReminder.subject');
        $build
            ->subject($mailSubject . ' #' . $this->leadFollowup->lead->id . ' - ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.followUpReminder.action'),
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    // phpcs:ignore
    public function toArray($notifiable)
    {
        return [
            'follow_up_id' => $this->leadFollowup->id,
            'id' => $this->leadFollowup->lead->id,
            'created_at' => $this->leadFollowup->created_at->format('Y-m-d H:i:s'),
            'heading' => __('email.followUpReminder.subject'),
        ];
    }

    public function toSlack($notifiable)
    {

        $followUpLead = $this->leadFollowup?->lead?->client_name;

        $followUpDate = $this->leadFollowup?->next_follow_up_date->format($this->company->date_format);

        $followUpTime = $this->leadFollowup?->next_follow_up_date->format($this->company->time_format);

        return $this->slackBuild($notifiable)
            ->content(__('email.followUpReminder.followUpLeadText') .'<br><br>' .__('email.followUpReminder.followUpLead') . ' :- ' . $followUpLead . '<br>' . __('email.followUpReminder.nextFollowUpDate') . ' :- ' . $followUpDate . '<br>' . __('email.followUpReminder.nextFollowUpTime') . ' :- ' . $followUpTime . '<br>' . $this->leadFollowup->remark);

    }

}
