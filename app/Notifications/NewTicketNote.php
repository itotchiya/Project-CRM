<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;

class NewTicketNote extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $ticket;
    private $ticketReply;
    private $emailSetting;

    public function __construct(TicketReply $ticket)
    {
        $this->ticketReply = $ticket;
        $this->ticket = $ticket->ticket;
        $this->company = $this->ticket->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'new-support-ticket-request')->first();
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

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active' && $notifiable->isEmployee($notifiable->id)) {
            array_push($via, 'slack');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $build = parent::build($notifiable);

        $url = route('tickets.show', $this->ticket->ticket_number);
        $url = getDomainSpecificUrl($url, $this->company);

        if ($this->ticketReply->user_id == $notifiable->id) {
            $text = '<p>' . $this->ticketReply->user->name . ' ' . __('email.ticketReply.receivedNote');

        }
        else {
            $text = '<p>' . $this->ticketReply->user->name . ' ' . __('email.ticketReply.receivedNote');

        }

        $content = new HtmlString($text);

        $build
            ->subject(__('email.ticketReply.noteAdded') . ' - ' . $this->ticket->subject)
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.ticketReply.action'),
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

    public function toSlack($notifiable)
    {

        $url = route('tickets.show', $this->ticket->ticket_number);
        $url = getDomainSpecificUrl($url, $this->company);

        return $this->slackBuild($notifiable)
            ->content('*' . __('email.ticketReply.subject') . '*' . "\n" . $this->ticket->subject . "\n" . __('modules.tickets.requesterName') . ' - ' . $this->ticket->requester->name . "\n" . '<' . $url . '|' . __('modules.tickets.ticket') . ' #' . $this->ticket->id . '>' . "\n");

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
            'id' => $this->ticket->id,
            'created_at' => $this->ticketReply->created_at->format('Y-m-d H:i:s'),
            'subject' => $this->ticket->subject,
            'user_id' => $this->ticketReply->user_id,
            'status' => $this->ticket->status,
            'agent_id' => $this->ticket->agent_id,
            'ticket_number' => $this->ticket->ticket_number
        ];
    }

}
