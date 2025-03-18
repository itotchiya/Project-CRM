<?php

namespace App\Notifications;

use App\Models\Estimate;
use App\Models\EmailNotificationSetting;

class EstimateDeclined extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $estimate;
    protected $company;
    private $emailSetting;

    public function __construct(Estimate $estimate)
    {
        $this->estimate = $estimate;
        $this->company = $this->estimate->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'estimate-notification')->first();
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

        if ($notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
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
        $url = route('estimates.show', $this->estimate->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.estimateDeclined.text');

        $build
            ->subject(__('email.estimateDeclined.subject') . ' - ' . config('app.name') . __('!'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.estimateDeclined.action'),
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
            'id' => $this->estimate->id,
            'estimate_number' => $this->estimate->estimate_number
        ];
    }

    public function toSlack($notifiable)
    {
        return $this->slackBuild($notifiable)->content(__('email.hello') . ' ' . $notifiable->name . ' ' . __('email.estimateDeclined.subject'));

    }

}
