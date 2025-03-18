<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Leave;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class NewMultipleLeaveRequest extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $leave;
    private $multiDates;
    private $emailSetting;

    public function __construct(Leave $leave, $multiDates)
    {
        $this->leave = $leave;
        $this->multiDates = $multiDates;
        $this->company = $this->leave->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'new-leave-application')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = [];

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->status == 'active') {
            array_push($via, OneSignalChannel::class);
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.leaves.subject'). ' ' . __('app.from') . ' ' . $this->leave->user->name, $this->leave->leave_date->format($this->company->date_format) . ' ' . $this->leave->type->type_name);
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $build = parent::build($notifiable);
        $url = route('leaves.show', $this->leave->unique_id);
        $url = getDomainSpecificUrl($url, $this->company);

        $user = $notifiable;
        $dates = str_replace(',', ' to ', $this->multiDates);;

        $emailDate = '';
        $emailDate .= $dates;
        $content = __('email.leaves.subject') . ' ' . __('app.from') . ' ' . $this->leave->user->name . '.' . '<p><b>' . __('modules.leaves.leaveType') . ':</b> ' . $this->leave->type->type_name . '</p><p><b>' . __('modules.leaves.reason') . '</b></p><p>' . $this->leave->reason . '</p><p><b>' . __('app.leaveDate') . '</b></p><p>' . $emailDate . '</p>';

        $build
            ->subject(__('email.leaves.subject') . ' - ' . config('app.name'))
            ->greeting(__('email.hello') . ' ' . $user->name . '!')
            ->markdown('mail.leaves.multiple', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.leaves.action')
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
//phpcs:ignore
    public function toArray($notifiable)
    {
        return [
            'id' => $this->leave->id,
            'user_id' => $this->leave->user->id,
            'user' => $this->leave->user
        ];
    }

    public function toSlack($notifiable)
    {

        $content = __('email.leaves.subject') . "\n" .
            $this->leave->user->name . "\n" .
            '*' . __('app.date') . '*: ' . $this->leave->leave_date->format($this->company->date_format) . "\n" .
            '*' . __('modules.leaves.leaveType') . '*: ' . $this->leave->type->type_name . "\n" .
            '*' . __('modules.leaves.reason') . '*' . "\n" .
            $this->leave->reason;

        return $this->slackBuild($notifiable)->content($content);


    }

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject(__('email.leaves.subject'))
            ->setBody('by ' . $this->leave->user->name);
    }

}
