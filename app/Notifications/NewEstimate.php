<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Estimate;
use App\Models\GlobalSetting;
use Illuminate\Notifications\Messages\MailMessage;

class NewEstimate extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $estimate;
    private $user;

    public function __construct(Estimate $estimate)
    {
        $this->estimate = $estimate;
        $this->user = User::findOrFail($estimate->client_id);
        $this->company = $this->estimate->company;
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

        if (push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.estimate.subject'), $this->estimate->estimate_number);
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    // phpcs:ignore
    public function toMail($notifiable): MailMessage
    {
        $build = parent::build($notifiable);
        $url = url()->temporarySignedRoute('front.estimate.show', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $this->estimate->hash);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.estimate.text') . '<br>' . __('app.menu.estimate') . ' ' . __('app.number') . ': ' .$this->estimate->estimate_number ;

        $build
            ->subject(__('email.estimate.subject') . ' (' . $this->estimate->estimate_number . ') - ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.estimateDeclined.action'),
                'notifiableName' => $this->user->name
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
            'id' => $this->estimate->id,
            'estimate_number' => $this->estimate->estimate_number
        ];
    }

}
