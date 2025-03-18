<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class ProjectMemberMention extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $project;
    private $emailSetting;
    private $projectMember;

    public function __construct(Project $project)
    {

        $this->project = $project;
        $this->projectMember = ProjectMember::where('project_id', $this->project->id)->first();

        $this->company = $this->project->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'project-mention-notification')->first();

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

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->status == 'active') {
            array_push($via, OneSignalChannel::class);
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.newProjectMember.subject'), $this->project->project_name);
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    // phpcs:ignore
    public function toMail($notifiable): MailMessage
    {

        $url = route('projects.show', $this->project->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.newProjectMember.mentionText') . ' - ' . $this->project->project_name . '<br>';

        return parent::build($notifiable)
            ->subject(__('email.newProjectMember.mentionProject') . ' - ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.newProjectMember.action'),
                'notifiableName' => $notifiable->name
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'member_id' => $this->projectMember->id,
            'project_id' => $this->project->id,
            'project' => $this->project->project_name
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        return $this->slackBuild($notifiable)
            ->content('*' . __('email.newProjectMember.mentionText') . '*' . "\n" . __('email.newProjectMember.text') . ' - ' . $this->project->project_name);
    }

    public function toOneSignal()
    {
        return OneSignalMessage::create()
            ->subject(__('email.newProjectMember.subject'))
            ->body($this->project->project_name);
    }

}
