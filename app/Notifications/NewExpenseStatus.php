<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Expense;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class NewExpenseStatus extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $expense;
    private $emailSetting;

    public function __construct(Expense $expense)
    {
        $this->expense = $expense;
        $this->company = $this->expense->company;
        if ($this->company) {
            $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'expense-status-changed')->first();
        }
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

        if (is_null($this->company)) {
            array_push($via, 'mail');

            return $via;
        }

        if ($this->emailSetting && $notifiable) {
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
                $pushNotification->sendPushNotifications($pushUsersIds, __('email.expenseStatus.subject'), $this->expense->item_name);
            }
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
        $url = route('expenses.show', $this->expense->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = $this->expense->item_name . ' - ' . __('email.expenseStatus.text') . ' ' . $this->expense->status . '.';

        $build
            ->subject(__('email.expenseStatus.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.expenseStatus.action'),
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
//phpcs:ignore
    public function toArray($notifiable)
    {
        return [
            'id' => $this->expense->id,
            'user_id' => $notifiable->id,
            'item_name' => $this->expense->item_name
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        if ($this->slackUserNameCheck($notifiable)) {
            return $this->slackBuild($notifiable)
                ->content(__('email.expenseStatus.text') . ' ' . $this->expense->status . ' - ' . $this->expense->item_name . ' - ' . $this->expense->currency->currency_symbol . $this->expense->price);
        }

        return $this->slackRedirectMessage('email.expenseStatus.subject', $notifiable);
    }

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject(__('email.expenseStatus.subject'))
            ->setBody($this->expense->item_name . ' - ' . __('email.expenseStatus.text') . ' ' . $this->expense->status . '.');
    }

}
