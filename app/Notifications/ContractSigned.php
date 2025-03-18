<?php

namespace App\Notifications;

use App\Http\Controllers\ContractController;
use App\Models\Contract;
use App\Models\ContractSign;
use Illuminate\Support\HtmlString;

class ContractSigned extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $contract;
    private $contractSign;

    public function __construct(Contract $contract, ContractSign $contractSign)
    {
        $this->contract = $contract;
        $this->contractSign = $contractSign;
        $this->company = $this->contract->company;
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

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $contract = parent::build($notifiable);
        $publicUrlController = new ContractController();
        $pdfOption = $publicUrlController->downloadView($this->contract->id);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        $content = new HtmlString(__('email.contractSign.text', ['contract' => '<strong>' . $this->contract->subject . '</strong>', 'client' => '<strong>' . $this->contractSign->full_name . '</strong>']));

        $contract->subject(__('email.contractSign.subject'))
            ->markdown('mail.email', [
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'notifiableName' => $notifiable->name
            ]);

        $contract->attachData($pdf->output(), $filename . '.pdf');

        return $contract;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */

    // phpcs:ignore
    public function toArray($notifiable)
    {
        return $this->contract->toArray();
    }

}
