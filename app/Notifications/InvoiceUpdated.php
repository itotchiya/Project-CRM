<?php

namespace App\Notifications;

use App\Http\Controllers\InvoiceController;
use App\Models\EmailNotificationSetting;
use App\Models\GlobalSetting;
use App\Models\Invoice;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use Illuminate\Support\Facades\App;

class InvoiceUpdated extends BaseNotification
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

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.invoice.updateSubject'), $this->invoice->invoice_number);
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
        $invoiceUpdate = parent::build($notifiable);

        if (($this->invoice->project && !is_null($this->invoice->project->client)) || !is_null($this->invoice->client_id)) {
            // For Sending pdf to email
            $invoiceController = new InvoiceController();

            if ($pdfOption = $invoiceController->domPdfObjectForDownload($this->invoice->id)) {

                $pdf = $pdfOption['pdf'];
                $filename = $pdfOption['fileName'];
                $invoiceUpdate->attachData($pdf->output(), $filename . '.pdf');

                App::setLocale($notifiable->locale ?? $this->company->locale ?? 'en');

                $url = url()->temporarySignedRoute('front.invoice', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $this->invoice->hash);
                $url = getDomainSpecificUrl($url, $this->company);

                $content = __('email.invoice.updateText');

                $invoiceUpdate->subject(__('email.invoice.updateSubject') . ' - ' . config('app.name') . '.')
                    ->markdown('mail.email', [
                        'url' => $url,
                        'content' => $content,
                        'themeColor' => $this->company->header_color,
                        'actionText' => __('email.viewInvoice'),
                        'notifiableName' => $notifiable->name
                    ]);

                return $invoiceUpdate;
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

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject(__('email.invoice.updateSubject'))
            ->setBody(__('email.invoice.updateText'));
    }

}
