<?php

namespace App\Notifications;

use App\Models\EstimateRequest;
use App\Models\EmailNotificationSetting;

class EstimateRequestRejected extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $estimateRequest;
    protected $company;
    private $emailSetting;

    public function __construct(EstimateRequest $estimateRequest)
    {
        $this->estimateRequest = $estimateRequest;
        $this->company = $this->estimateRequest->company;
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
        $build = parent::build();
        $url = route('estimate-request.show', $this->estimateRequest->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.estimateRequestRejected.text') . '<br>' . __('modules.estimateRequest.estimateRequest') . ' ' . __('app.number') . ': ' .$this->estimateRequest->estimate_request_number;
        $content .= '<br><br>' . __('app.reason') . ': ' . $this->estimateRequest->reason;


        $build
            ->subject(__('email.estimateRequestRejected.subject') . ' (' . $this->estimateRequest->estimate_request_number . ') - ' . config('app.name') . __('!'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.estimateRequestRejected.action'),
                'notifiableName' => $notifiable->name,
                'reason' => $this->estimateRequest->reason
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
            'id' => $this->estimateRequest->id,
            'estimate_request_number' => $this->estimateRequest->estimate_request_number,
        ];
    }

    public function toSlack($notifiable)
    {
        return $this->slackBuild($notifiable)->content(__('email.hello') . ' ' . $notifiable->name . ' ' . __('email.estimateRequestRejected.subject'));

    }

}
