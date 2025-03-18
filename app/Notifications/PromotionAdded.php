<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Promotion;
use Illuminate\Notifications\Messages\MailMessage;

class PromotionAdded extends BaseNotification
{

    private $promotion;
    private $emailSetting;

    /**
     * Create a new notification instance.
     */
    public function __construct(Promotion $promotion)
    {
        $this->promotion = $promotion;
        $this->company = $this->promotion->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->promotion->company_id)->where('slug', 'event-notification')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $via = ['database'];

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $build = parent::build($notifiable);
        $subject = '🚀' . __('email.incrementPromotion.subject') . ' ' . $notifiable->name;
        $department = $this->promotion->current_department_id == $this->promotion->previous_department_id ? __('email.incrementPromotion.same') : ' <b>'.$this->promotion->currentDepartment->team_name.'</b>';

        $content = __('email.incrementPromotion.text') . ' <b>' . $this->promotion->currentDesignation->name . '</b> ' . __('email.incrementPromotion.in') . ' ' . $department . ' ' . __('app.menu.teams') . '! 🎊 <br><br>' .
            __('email.incrementPromotion.text1') . '<br><br>' . __('email.incrementPromotion.text2') . '<br><br>';

        $build
            ->subject($subject)
            ->markdown('mail.email', [
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->promotion->id,
            'created_at' => $this->promotion->created_at->format('Y-m-d H:i:s'),
            'title' => __('email.incrementPromotion.subject') . ' ' . $notifiable->name,
            'heading' => __('email.incrementPromotion.text3') . ': ' . $this->promotion->currentDesignation->name,
        ];
    }

    public function toSlack($notifiable)
    {
        try {
            $notifiableName = __('email.hello') . ': ' . $notifiable->name;
            $subject = '🚀 ' . __('email.incrementPromotion.subject') . ' ' . $notifiable->name;

            $department = $this->promotion->current_department_id == $this->promotion->previous_department_id ? __('email.incrementPromotion.same') : ' *' . optional($this->promotion->currentDepartment)->team_name . '*';

            $content = __('email.incrementPromotion.text') . ' *' . optional($this->promotion->currentDesignation)->name . '* ' .
                __('email.incrementPromotion.in') . ' ' . $department . ' ' . __('app.menu.teams') . '! 🎊' . "\n\n" .
                __('email.incrementPromotion.text1') . "\n\n" . __('email.incrementPromotion.text2');

            return $this->slackBuild($notifiable)
                ->content($subject . "\n\n" . $notifiableName . "\n\n" . $content);

        } catch (\Exception $e) {
            return $this->slackRedirectMessage('email.incrementPromotion.subject', $notifiable);
        }
    }

}
