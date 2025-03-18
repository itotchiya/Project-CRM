<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;

class MonthlyAttendance extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $user;
    public $month;
    public $year;
    public $previousMonth;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->company = $this->user->company;
        $this->previousMonth = now()->timezone($this->company->timezone)->subWeek();
        $this->month = $this->previousMonth->copy()->month;
        $this->year = $this->previousMonth->copy()->year;
        Config::set('app.logo', $this->company->masked_logo_url);
        // $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'event-notification')->first();
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
            Attachment::fromData(fn() => $this->domPdfObjectForDownload()['pdf']->output(), 'Attendance-Report-' . Carbon::parse('01-' . $this->month . '-' . $this->year)->format('F-Y') . '.pdf')
                ->withMime('application/pdf'),
        ];
    }

    public function domPdfObjectForDownload()
    {
        $company = $this->company;

        $employees = User::with(
            [
                'attendance' => function ($query) {
                    $query->whereRaw('MONTH(attendances.clock_in_time) = ?', [$this->month])
                        ->whereRaw('YEAR(attendances.clock_in_time) = ?', [$this->year]);
                },
                'leaves' => function ($query) {
                    $query->whereRaw('MONTH(leaves.leave_date) = ?', [$this->month])
                        ->whereRaw('YEAR(leaves.leave_date) = ?', [$this->year])
                        ->where('status', 'approved');
                },
                'shifts' => function ($query) {
                    $query->whereRaw('MONTH(employee_shift_schedules.date) = ?', [$this->month])
                        ->whereRaw('YEAR(employee_shift_schedules.date) = ?', [$this->year]);
                }]
        )->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'employee_details.department_id', 'users.image')
            ->onlyEmployee()
            ->where('users.company_id', $company->id)
            ->groupBy('users.id');

        $employees = $employees->get();

        $holidays = Holiday::whereRaw('MONTH(holidays.date) = ?', [$this->month])->whereRaw('YEAR(holidays.date) = ?', [$this->year])->get();

        $final = [];
        $holidayOccasions = [];

        $daysInMonth = Carbon::parse('01-' . $this->month . '-' . $this->year)->daysInMonth;


        $now = now()->timezone($this->company->timezone);
        $requestedDate = Carbon::parse(Carbon::parse('01-' . $this->month . '-' . $this->year))->endOfMonth();

        foreach ($employees as $employee) {

            $dataBeforeJoin = null;

            $dataTillToday = array_fill(1, $now->copy()->format('d'), 'Absent');

            if (($now->copy()->format('d') != $daysInMonth) && !$requestedDate->isPast()) {
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), ((int)$daysInMonth - (int)$now->copy()->format('d')), '-');
            }
            else {
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), ((int)$daysInMonth - (int)$now->copy()->format('d')), 'Absent');
            }

            $final[$employee->id . '#' . $employee->name] = array_replace($dataTillToday, $dataFromTomorrow);

            $shiftScheduleCollection = $employee->shifts->keyBy('date');

            foreach ($employee->attendance as $attendance) {
                $clockInTime = Carbon::createFromFormat('Y-m-d H:i:s', $attendance->clock_in_time->timezone($company->timezone)->toDateTimeString(), 'UTC');

                if (isset($shiftScheduleCollection[$clockInTime->copy()->startOfDay()->toDateTimeString()])) {
                    $shiftStartTime = Carbon::parse($clockInTime->copy()->toDateString() . ' ' . $shiftScheduleCollection[$clockInTime->copy()->startOfDay()->toDateTimeString()]->shift->office_start_time);
                    $shiftEndTime = Carbon::parse($clockInTime->copy()->toDateString() . ' ' . $shiftScheduleCollection[$clockInTime->copy()->startOfDay()->toDateTimeString()]->shift->office_end_time);

                    if ($clockInTime->between($shiftStartTime, $shiftEndTime)) {
                        $final[$employee->id . '#' . $employee->name][$clockInTime->day] = '&check;';

                    }
                    else if ($attendance->employee_shift_id == $shiftScheduleCollection[$clockInTime->copy()->startOfDay()->toDateTimeString()]->shift->id) {
                        $final[$employee->id . '#' . $employee->name][$clockInTime->day] = '&check;';

                    }
                    elseif ($clockInTime->betweenIncluded($shiftStartTime->copy()->subDay(), $shiftEndTime->copy()->subDay())) {
                        $final[$employee->id . '#' . $employee->name][$clockInTime->copy()->subDay()->day] = '&check;';

                    }
                    else {
                        $final[$employee->id . '#' . $employee->name][$clockInTime->day] = '&check;';
                    }

                }
                else {
                    $final[$employee->id . '#' . $employee->name][$clockInTime->day] = '&check;';
                }
            }

            $emplolyeeName = $employee->name;

            $final[$employee->id . '#' . $employee->name][] = $emplolyeeName;

            if ($employee->employeeDetail->joining_date->greaterThan(Carbon::parse(Carbon::parse('01-' . $this->month . '-' . $this->year)))) {
                if ($this->month == $employee->employeeDetail->joining_date->format('m') && $this->year == $employee->employeeDetail->joining_date->format('Y')) {
                    if ($employee->employeeDetail->joining_date->format('d') == '01') {
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->format('d'), '-');
                    }
                    else {
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->subDay()->format('d'), '-');
                    }
                }

                if (($this->month < $employee->employeeDetail->joining_date->format('m') && $this->year == $employee->employeeDetail->joining_date->format('Y')) || $this->year < $employee->employeeDetail->joining_date->format('Y')) {
                    $dataBeforeJoin = array_fill(1, $daysInMonth, '-');
                }
            }

            if (Carbon::parse('01-' . $this->month . '-' . $this->year)->isFuture()) {
                $dataBeforeJoin = array_fill(1, $daysInMonth, '-');
            }

            if (!is_null($dataBeforeJoin)) {
                $final[$employee->id . '#' . $employee->name] = array_replace($final[$employee->id . '#' . $employee->name], $dataBeforeJoin);
            }

            foreach ($employee->leaves as $leave) {
                if ($leave->duration == 'half day') {
                    if ($final[$employee->id . '#' . $employee->name][$leave->leave_date->day] == '-' || $final[$employee->id . '#' . $employee->name][$leave->leave_date->day] == 'Absent') {
                        $final[$employee->id . '#' . $employee->name][$leave->leave_date->day] = 'Half Day';
                    }
                }
                else {
                    $final[$employee->id . '#' . $employee->name][$leave->leave_date->day] = 'Leave';
                }

            }

            foreach ($holidays as $holiday) {
                if ($final[$employee->id . '#' . $employee->name][$holiday->date->day] == 'Absent' || $final[$employee->id . '#' . $employee->name][$holiday->date->day] == '-') {
                    $final[$employee->id . '#' . $employee->name][$holiday->date->day] = 'Holiday';
                    $holidayOccasions[$holiday->date->day] = $holiday->occassion;
                }
            }
        }

        $employeeAttendence = $final;

        $weekMap = Holiday::weekMap('D');

        $pdf = app('dompdf.wrapper')->setPaper('A4', 'landscape');

        $options = $pdf->getOptions();
        $options->set(array('enable_php' => true));
        $pdf->getDomPDF()->setOptions($options);
        /** @phpstan-ignore-line */

        $pdf->loadView('attendance-report', ['daysInMonth' => $daysInMonth, 'month' => $this->month, 'year' => $this->year, 'weekMap' => $weekMap, 'employeeAttendence' => $employeeAttendence, 'holidayOccasions' => $holidayOccasions, 'company' => $company]);

        $filename = 'attendance-report';

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

        $build->subject(__('email.attendanceReport.subject') . ' ' . Carbon::parse('01-' . $this->month . '-' . $this->year)->format('F-Y'))
            ->markdown('mail.attendance.monthly-report', ['month' => Carbon::parse('01-' . $this->month . '-' . $this->year)->format('F-Y')]);

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
