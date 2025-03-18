<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Deal;

class LeadAgentAssigned extends BaseNotification
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

        $leadEmail = __('modules.lead.clientEmail') . ': ';
        $clientEmail = !is_null($this->deal->contact->client_email) ? $leadEmail : '';
        $content = __('email.leadAgent.subject') . '<br>' .__('modules.deal.dealName') . ': '  . $this->deal->name . '<br>' .  __('modules.lead.clientName') . ': '  . $this->deal->contact->client_name_salutation . '<br>' . $clientEmail . $this->deal->contact->client_email;

        $build
            ->subject(__('email.leadAgent.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.leadAgent.action'),
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
            'agent_id' => $notifiable->id,
            'added_by' => $this->deal->added_by
        ];
    }

}
