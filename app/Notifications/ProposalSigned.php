<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\GlobalSetting;
use App\Models\Proposal;
use Illuminate\Notifications\Messages\MailMessage;

class ProposalSigned extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $proposal;
    private $emailSetting;

    public function __construct(Proposal $proposal)
    {
        $this->proposal = $proposal;
        $this->company = $this->proposal->company;
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
        $via = ['database'];

        if ($notifiable->email_notifications && $this->emailSetting->send_email == 'yes' && $notifiable->email != '') {
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

        $url = url()->temporarySignedRoute('front.proposal', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $this->proposal->hash);
        $url = getDomainSpecificUrl($url, $this->company);


        if ($this->proposal->status == 'accepted') {

            $content = __('app.proposal') . ' ' . __('app.number') . ': ' . $this->proposal->proposal_number . '' . '<br>' . __('app.status') . ' : ' . $this->proposal->status;

            $build
                ->subject(__('email.proposalSigned.subject') . ' (' . $this->proposal->proposal_number . ')' )
                ->markdown('mail.email', [
                    'url' => $url,
                    'content' => $content,
                    'themeColor' => $this->company->header_color,
                    'actionText' => __('app.view') . ' ' . __('app.proposal'), 'notifiableName' => $notifiable->name
                ]);

            parent::resetLocale();

            return $build;
        }

        $content = __('app.proposal') . ' ' . __('app.number') . ': ' . $this->proposal->proposal_number . '' . '<br>' . __('email.proposalRejected.rejected') . ' : ' . $this->proposal->client_comment . '<br>' . __('app.status') . ': ' . $this->proposal->status;

        $build
            ->subject(__('email.proposalRejected.subject') . ' (' . $this->proposal->proposal_number . ')' )
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('app.view') . ' ' . __('app.proposal'),
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
        return $this->proposal->toArray();
    }

}
