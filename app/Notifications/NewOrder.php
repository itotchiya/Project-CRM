<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Order;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class NewOrder extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $order;
    private $emailSetting;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->company = $this->order->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'order-createupdate-notification')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') ? ['mail', 'database'] : ['database'];

        if ($this->emailSetting->send_push == 'yes' && push_setting()->status == 'active') {
            array_push($via, OneSignalChannel::class);
        }

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        $subject = (!in_array('client', user_roles()) ? __('email.orders.subject') : __('email.order.subject'));
        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, $subject, $this->order->order_number);
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage|void
     */
    public function toMail($notifiable)
    {
        $build = parent::build($notifiable);

        if ($this->order) {

            $url = route('orders.show', $this->order->id);
            $url = getDomainSpecificUrl($url, $this->company);
            $subject = (!in_array('client', user_roles()) ? __('email.orders.subject') : __('email.order.subject'));
            $content = __('email.order.text') . '<br>' . __('app.orderNumber') . ': ' . $this->order->order_number .'<br>';

            $build
                ->subject($subject . ' (' . $this->order->order_number . ') - ' . config('app.name') . '.')
                ->markdown('mail.email', [
                    'url' => $url,
                    'content' => $content,
                    'themeColor' => $this->company->header_color,
                    'actionText' => __('email.order.action'),
                    'notifiableName' => $notifiable->name
                ]);

            parent::resetLocale();

            return $build;
        }
    }

      // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject((!in_array('client', user_roles()) ? __('email.orders.subject') : __('email.order.subject')))
            ->setBody(__('email.order.text'));
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
            'id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'client_id' => $this->order->client_id,
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

        return $this->slackBuild($notifiable)
            ->content('*' . __('email.orders.subject') . '!*' . "\n" . __('app.orderNumber') . ': ' . $this->order->order_number);

    }
}
