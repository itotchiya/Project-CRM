<?php

namespace App\Notifications;

use App\Http\Controllers\InvoiceController;
use App\Models\EmailNotificationSetting;
use App\Models\Invoice;

class NewInvoiceRecurring extends BaseNotification
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
        $newInvoiceRecurring = parent::build($notifiable);

        if (($this->invoice->project && !is_null($this->invoice->project->client)) || !is_null($this->invoice->client_id)) {
            // For Sending pdf to email
            $invoiceController = new InvoiceController();

            if ($pdfOption = $invoiceController->domPdfObjectForConsoleDownload($this->invoice->id)) {
                $pdf = $pdfOption['pdf'];
                $filename = $pdfOption['fileName'];

                $url = route('invoices.show', $this->invoice->id);
                $url = getDomainSpecificUrl($url, $this->company);

                $content = __('email.invoice.text') . '<br>' . __('app.invoiceNumber') . ': ' .$this->invoice->invoice_number;

                $newInvoiceRecurring  ->subject(__('email.invoice.subject') . ' (' . $this->invoice->invoice_number . ') - ' . config('app.name') . '.')
                    ->markdown('mail.email', [
                        'url' => $url,
                        'content' => $content,
                        'themeColor' => $this->company->header_color,
                        'actionText' => __('email.invoice.action'),
                        'notifiableName' => $notifiable->name
                    ]);

                $newInvoiceRecurring->attachData($pdf->output(), $filename . '.pdf');

                return $newInvoiceRecurring;
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

}
