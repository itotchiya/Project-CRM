<?php

namespace App\Notifications;

use App\Models\Contract;
use App\Models\GlobalSetting;
use Illuminate\Notifications\Messages\MailMessage;

class NewContract extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $contract;

    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
        $this->company = $this->contract->company;
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
        $url = url()->temporarySignedRoute('front.contract.show', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $this->contract->hash);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.newContract.text') . '<br>';

        $build
            ->subject(__('email.newContract.subject'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('app.view') . ' ' . __('app.menu.contract'),
                'notifiableName' => $notifiable->name]);

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
        return $this->contract->toArray();
    }

}
