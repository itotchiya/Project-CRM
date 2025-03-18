<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Notifications\Messages\MailMessage;

class ProjectRating extends BaseNotification
{

    private $project;

    /**
     * Create a new notification instance.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $via = array('database');

        if ($notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $build = parent::build($notifiable);

        $url = route('projects.show', $this->project->id) . '?tab=rating';
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.projectRating.text') . ' ' . $this->project->project_name;

        $build
            ->subject(__('email.projectRating.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company?->header_color,
                'actionText' => __('email.projectRating.action'),
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    //phpcs:ignore
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->project->id,
            'created_at' => $this->project->rating->created_at->format('Y-m-d H:i:s'),
            'heading' => __('email.projectRating.subject'),
        ];
    }

}
