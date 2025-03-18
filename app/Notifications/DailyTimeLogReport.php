<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class DailyTimeLogReport extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $user;
    public $todayDate;
    public $role;

    public function __construct(User $user, $role)
    {
        $this->user = $user;
        $this->role = $role;
        $this->company = $this->user->company;
        $this->todayDate = Carbon::now()->toDateString();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function attachments()
    {
        return [
            Attachment::fromData(fn() => $this->domPdfObjectForDownload()['pdf']->output(), 'TimeLog-Report-' . $this->todayDate . '.pdf')
                ->withMime('application/pdf'),
        ];
    }

    public function domPdfObjectForDownload()
    {
        $company = $this->company;

        $employees = User::select('users.id', 'users.name')
            ->with(['timeLogs' => function ($query) use ($company) {
                $query->whereRaw('DATE(start_time) = ?', [$this->todayDate]);
                $query->where('company_id', $company->id);
            }, 'timeLogs.breaks'])
            ->when($this->role->name != 'admin', function ($query) {
                $query->where('users.id', $this->user->id);
            })
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')->onlyEmployee()
            ->where('roles.company_id', $company->id)
            ->groupBy('users.id');

        $employees = $employees->get();

        $employeeData = [];

        foreach ($employees as $employee) {
            $employeeData[$employee->name] = [];
            $employeeData[$employee->name]['timelog'] = 0;
            $employeeData[$employee->name]['timelogBreaks'] = 0;

            if (count($employee->timeLogs) > 0) {

                foreach ($employee->timeLogs as $timeLog) {
                    $employeeData[$employee->name]['timelog'] += $timeLog->total_minutes;

                    if (count($timeLog->breaks) > 0) {
                        foreach ($timeLog->breaks as $timeLogBreak) {
                            $employeeData[$employee->name]['timelogBreaks'] += $timeLogBreak->total_minutes;
                        }
                    }
                }
            }
        }

        $now = $this->todayDate;
        $requestedDate = $now;

        $pdf = app('dompdf.wrapper')->setPaper('A4', 'landscape');

        $options = $pdf->getOptions();
        $options->set(array('enable_php' => true));
        $pdf->getDomPDF()->setOptions($options);
        /** @phpstan-ignore-line */

        $pdf->loadView('timelog-report', ['employees' => $employeeData, 'date' => $now, 'company' => $company]);

        $filename = 'timelog-report';

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $build = parent::build($notifiable);

            $pdfOption = $this->domPdfObjectForDownload();
            $pdf = $pdfOption['pdf'];
            $filename = $pdfOption['fileName'];
            $build->attachData($pdf->output(), $filename . '.pdf');

            App::setLocale($notifiable->locale ?? $this->company->locale ?? 'en');

        $build->subject(__('email.dailyTimelogReport.subject') . ' ' . $this->todayDate)
            ->markdown('mail.timelog.timelog-report', ['date' => $this->todayDate, 'name' => $this->user->name]);

        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

}
