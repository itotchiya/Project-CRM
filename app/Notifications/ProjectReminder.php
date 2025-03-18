<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class ProjectReminder extends BaseNotification
{


    private $projects;
    private $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($projects, $data)
    {
        $this->projects = $projects;
        $this->data = $data;

        if (isset($this->projects[0])) {
            $this->company = $this->projects[0]->company;
        }

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = array();

        if ($notifiable->email_notifications && $notifiable->email != '') {
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

        $url = route('projects.index');
        $url = getDomainSpecificUrl($url, $this->company);

        $list = $this->projectList();
        $content = __('email.projectReminder.text') . ' ' . now($this->data['company']->timezone)->addDays($this->data['project_setting']->remind_time)->toFormattedDateString() . '<br>' . new HtmlString($list) . '<br>' . __('email.messages.loginForMoreDetails');

        $build
            ->subject(__('email.projectReminder.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company?->header_color,
                'actionText' => __('email.projectReminder.action'),
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
        return $this->projects->toArray();
    }

    private function projectList()
    {
        $list = '<ol>';

        foreach ($this->projects as $project) {
            $list .= '<li><strong>' . $project->project_short_code . '</strong> ' . $project->project_name . '</li>';
        }

        $list .= '</ol>';

        return $list;
    }

}
