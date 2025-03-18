<?php

namespace App\Notifications;

use App\Http\Controllers\ProposalController;
use App\Models\GlobalSetting;
use App\Models\Proposal;
use Illuminate\Support\Facades\App;

class NewProposal extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */

    private $proposal;

    public function __construct(Proposal $proposal)
    {
        $this->proposal = $proposal;
        $this->company = $this->proposal->company;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage|void
     */
    // phpcs:ignore
    public function toMail($notifiable)
    {
        $newProposal = parent::build($notifiable);
        $proposalController = new ProposalController();

        if ($pdfOption = $proposalController->domPdfObjectForDownload($this->proposal->id)) {
            $pdf = $pdfOption['pdf'];
            $filename = $pdfOption['fileName'];
            $newProposal->attachData($pdf->output(), $filename . '.pdf');

            App::setLocale($notifiable->locale ?? $this->company->locale ?? 'en');

            $url = url()->temporarySignedRoute('front.proposal', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $this->proposal->hash);
            $url = getDomainSpecificUrl($url, $this->company);

            $content = __('email.proposal.text') . '<br>' . __('app.proposal') . ' ' . __('app.number') . ': ' . $this->proposal->proposal_number . '' . '<br>';

            $newProposal->subject(__('email.proposal.subject') . ' (' . $this->proposal->proposal_number . ')' )
                ->markdown('mail.email', [
                    'url' => $url,
                    'content' => $content,
                    'themeColor' => $this->company->header_color,
                    'actionText' => __('app.view') . ' ' . __('app.proposal'),
                    'notifiableName' => $this->proposal->lead->client_name
                ]);

            return $newProposal;
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
        return $this->proposal->toArray();
    }

}
