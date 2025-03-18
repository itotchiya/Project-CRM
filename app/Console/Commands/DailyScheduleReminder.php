<?php

namespace App\Console\Commands;

use App\Events\DailyScheduleEvent;
use App\Models\EmailNotificationSetting;
use App\Models\Event;
use App\Models\Holiday;
use App\Models\TaskboardColumn;
use App\Models\User;
use Illuminate\Console\Command;
use Modules\Recruit\Entities\RecruitInterviewEmployees;

class DailyScheduleReminder extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily-schedule-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send the daily updates to employees about their tasks, leaves, holidays, events and interviews';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyIds = EmailNotificationSetting::where('slug', 'daily-schedule-notification')
            ->where('send_email', 'yes')
            ->pluck('company_id')
            ->toArray();

        $data = [];


        foreach ($companyIds as $companyId) {

            $completedTaskColumn = TaskboardColumn::where('slug', 'completed')
                ->where('company_id', $companyId)
                ->first();

            $users = User::withRole('employee')
                ->with([
                    'company',
                    'tasks' => function ($query) use ($completedTaskColumn) {
                        $query->whereDate('due_date', '=', now())
                            ->where('board_column_id', '<>', $completedTaskColumn->id);
                    },
                    'leaves' => function ($leaves) {
                        $leaves->whereDate('leave_date', '=', now())
                            ->where('leaves.status', 'approved');
                    },
                ])
                ->where('company_id', $companyId)
                ->get();

            foreach ($users as $user) {
                $events = Event::with('attendee', 'attendee.user')
                    ->where(function ($query) use ($user) {
                        $query->whereHas('attendee', function ($query) use ($user) {
                            $query->where('user_id', $user->id);
                        });
                        $query->orWhere('added_by', $user->id);
                    })
                    ->whereDate('start_date_time', '<=', now()->toDateString())
                    ->whereDate('end_date_time', '>=', now()->toDateString())
                    ->count();

                $holiday = Holiday::where(function ($query) use ($user) {
                    $query->where('added_by', $user->id)
                        ->orWhere(function ($query) use ($user) {
                            $query->where(function ($q) use ($user) {
                                $q->orWhere('department_id_json', 'like', '%"' . $user->employeeDetail->department_id . '"%')
                                    ->orWhereNull('department_id_json');
                            });
                            $query->where(function ($q) use ($user) {
                                $q->orWhere('designation_id_json', 'like', '%"' . $user->employeeDetail->designation_id . '"%')
                                    ->orWhereNull('designation_id_json');
                            });
                            $query->where(function ($q) use ($user) {
                                $q->orWhere('employment_type_json', 'like', '%"' . $user->employeeDetail->employment_type . '"%')
                                    ->orWhereNull('employment_type_json');
                            });
                        });
                })->whereDate('date', '=', now())->count();

                if (module_enabled('Recruit')) {
                    $interview = RecruitInterviewEmployees::with(['schedule' => function ($q) {
                        $q->whereDate('schedule_date', '=', now());
                    }])->where('user_id', $user->id)->count();

                    $data[$user->id]['interview'] = $interview;
                }

                $data[$user->id]['user'] = $user;
                $data[$user->id]['holidays'] = $holiday;
                $data[$user->id]['leaves'] = $user->leaves->count();
                $data[$user->id]['tasks'] = $user->tasks->count();
                $data[$user->id]['events'] = $events;
            }
            event(new DailyScheduleEvent($data));
        }
    }

}
