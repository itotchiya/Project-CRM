<?php

namespace App\Console\Commands;

use App\Models\EmployeeDetails;
use Carbon\Carbon;
use Illuminate\Console\Command;

class InActiveEmployee extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inactive-employee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The employee is set to inactive if he exit the company';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $todayDate = Carbon::today();
            
            EmployeeDetails::with('user')
                ->where(function ($query) use ($todayDate) {
                    $query->whereDate('last_date', '<=', $todayDate)
                        ->orWhereDate('notice_period_end_date', '<=', $todayDate);
                })
                ->whereHas('user', function ($query) {
                    $query->where('status', 'active');
                })
                ->chunk(50, function ($employees) use ($todayDate) {
                    foreach ($employees as $employee) {

                        $employee->user->status = 'deactive';
                        $employee->user->inactive_date = now();

                        if (empty($employee->last_date) && !empty($employee->notice_period_end_date)) {
                            $employee->last_date = $employee->notice_period_end_date;
                            $employee->save();
                        }

                        $employee->user->save();
                    }
                });
    }

}
