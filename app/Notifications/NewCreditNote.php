<?php

namespace App\Notifications;

use App\Http\Controllers\CreditNoteController;
use App\Models\CreditNotes;
use App\Models\EmailNotificationSetting;
use NotificationChannels\OneSignal\OneSignalChannel;
use Illuminate\Support\Facades\App;

class NewCreditNote extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $creditNotes;
    private $emailSetting;

    public function __construct(CreditNotes $creditNotes)
    {
        $this->creditNotes = $creditNotes;

        $this->company = $this->creditNotes->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'invoice-createupdate-notification')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ($notifiable->email_notifications && $notifiable->email != '') ? ['mail', 'database'] : ['database'];

        if ($this->emailSetting->send_push == 'yes' && push_setting()->status == 'active') {
            array_push($via, OneSignalChannel::class);
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.creditNote.subject'), $this->creditNotes->cn_number);
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
        $newCreditNote = parent::build($notifiable);

        if (!is_null($this->creditNotes->client_id)) {
            // For Sending pdf to email
            $invoiceController = new CreditNoteController();

            if ($pdfOption = $invoiceController->domPdfObjectForDownload($this->creditNotes->id)) {
                $pdf = $pdfOption['pdf'];
                $filename = $pdfOption['fileName'];
                $newCreditNote->attachData($pdf->output(), $filename . '.pdf');

                App::setLocale($notifiable->locale ?? $this->company->locale ?? 'en');

                $url = route('creditnotes.show', $this->creditNotes->id);
                $url = getDomainSpecificUrl($url, $this->company);

                $content = __('email.creditNote.text') . '<br>' . __('app.creditnoteNumber') . ': ' . $this->creditNotes->cn_number .'<br>';

                $newCreditNote->subject(__('email.creditNote.subject') . ' (' . $this->creditNotes->cn_number . ') - ' . config('app.name') . '.')
                    ->markdown('mail.email', [
                        'url' => $url,
                        'content' => $content,
                        'themeColor' => $this->company->header_color,
                        'actionText' => __('email.creditNote.action'),
                        'notifiableName' => $notifiable->name
                    ]);



                return $newCreditNote;
            }
        }
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
            'id' => $this->creditNotes->id,
            'cn_number' => $this->creditNotes->cn_number
        ];
    }

}
