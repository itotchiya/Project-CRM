<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\EmailNotificationSetting;
use App\Http\Controllers\InvoiceController;
use App\Models\GlobalSetting;
use NotificationChannels\OneSignal\OneSignalChannel;
use Illuminate\Support\Facades\App;

class NewInvoice extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $invoice;
    private $emailSetting;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->company = $this->invoice->company;
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
        $via = ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') ? ['mail', 'database'] : ['database'];

        if ($this->emailSetting->send_push == 'yes' && push_setting()->status == 'active') {
            array_push($via, OneSignalChannel::class);
        }

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.invoice.subject'), $this->invoice->invoice_number);
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
        $newInvoice = parent::build($notifiable);

        if (($this->invoice->project && !is_null($this->invoice->project->client)) || !is_null($this->invoice->client_id)) {
            // For Sending pdf to email
            $invoiceController = new InvoiceController();

            if ($pdfOption = $invoiceController->domPdfObjectForDownload($this->invoice->id)) {
                $pdf = $pdfOption['pdf'];
                $filename = $pdfOption['fileName'];
                $newInvoice->attachData($pdf->output(), $filename . '.pdf');

                App::setLocale($notifiable->locale ?? $this->company->locale ?? 'en');

                $url = url()->temporarySignedRoute('front.invoice', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $this->invoice->hash);
                $url = getDomainSpecificUrl($url, $this->company);
                $content = __('email.invoice.text') . '<br>' . __('app.invoiceNumber') . ': ' .$this->invoice->invoice_number ;

                $newInvoice->subject(__('email.invoice.subject') . ' (' . $this->invoice->invoice_number . ') - ' . config('app.name') . '.')
                    ->markdown('mail.email', [
                        'url' => $url,
                        'content' => $content,
                        'themeColor' => $this->company->header_color,
                        'actionText' => __('email.viewInvoice'),
                        'notifiableName' => $notifiable->name
                    ]);

                return $newInvoice;
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
            'id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number
        ];
    }

    public function toSlack($notifiable)
    {
        return $this->slackBuild($notifiable)
            ->content(__('email.hello') . ' ' . $notifiable->name . ' ' . __('email.invoice.subject'));

    }

}
