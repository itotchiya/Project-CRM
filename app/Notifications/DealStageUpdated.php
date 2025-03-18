<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Deal;

class DealStageUpdated extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $deal;
    private $emailSetting;

    public function __construct(Deal $deal)
    {
        $this->deal = $deal;
        $this->company = $this->deal->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'lead-notification')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = array('database');

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
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
        $url = route('deals.show', $this->deal->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $leadStage = __('modules.leadContact.stage') . ': ';
        $leadPipeline = __('modules.deal.pipeline') . ': ';
        $content = __('email.dealStatus.subject') . '<br>' . __('modules.lead.clientName') . ': ' .   $this->deal->contact->client_name_salutation . '<br>' . $leadPipeline . $this->deal->pipeline->name. '<br>' . $leadStage . $this->deal->leadStage->name;

        $build
            ->subject(__('email.dealStatus.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.dealStatus.action'),
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
            'id' => $this->deal->id,
            'name' => $this->deal->name,
            'stage' => $this->deal->leadStage->name,
            'agent_id' => $notifiable->id,
            'added_by' => $this->deal->added_by
        ];
    }

}
