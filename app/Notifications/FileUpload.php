<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Notifications\Messages\MailMessage;

class FileUpload extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $file;
    private $project;
    private $emailSetting;

    public function __construct(ProjectFile $file)
    {
        $this->file = $file;
        $this->project = Project::findOrFail($this->file->project_id);
        $this->company = $this->file->project->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'employee-assign-to-project')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = [];

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
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
        $url = route('projects.show', [$this->project->id, 'tab' => 'files']);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.fileUpload.subject') . $this->project->project_name . '<br>' . __('modules.projects.fileName') . ' - ' . $this->file->filename . '<br>' . __('app.date') . ' - ' . $this->file->created_at->format($this->company->date_format);

        $build
            ->subject(__('email.fileUpload.subject') . ' ' . $this->project->project_name . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.fileUpload.action'),
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
        return [
            //
        ];
    }

}
