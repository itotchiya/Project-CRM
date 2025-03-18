<?php

namespace App\Notifications;

use App\Models\TicketEmailSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\TicketReply as ModelsTicketReply;

class MailTicketReply extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $ticketReply;
    private $ticketEmailSetting;

    public function __construct(ModelsTicketReply $ticketReply, TicketEmailSetting $ticketEmailSetting)
    {
        $this->ticketReply = $ticketReply;
        $this->ticketEmailSetting = $ticketEmailSetting;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $build = parent::build($notifiable);
        $previousReply = ModelsTicketReply::where('ticket_id', $this->ticketReply->ticket_id)
            ->whereNotNull('imap_message_id')->orderByDesc('id')
            ->first();

        if ($this->ticketEmailSetting->status == 1) {
            $build->from($this->ticketEmailSetting->mail_from_email, $this->ticketEmailSetting->mail_from_name)
                ->subject($this->ticketReply->ticket->subject)
                ->view('emails.ticket.reply');

            if (!is_null($previousReply) && !is_null($previousReply->imap_message_id)) {
                ModelsTicketReply::where('id', $this->ticketReply->id)->update(['imap_message_id' => $previousReply->imap_message_id]);
            }

            parent::resetLocale();

            return $build;
        }

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
            //
        ];
    }

}
