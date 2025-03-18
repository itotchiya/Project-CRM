<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\User;
use App\Traits\ExcelImportable;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ImportAttendanceJob implements ShouldQueue
{

    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ExcelImportable;

    private $row;
    private $columns;
    private $company;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->isColumnExists('clock_in_time') && $this->isColumnExists('email') && $this->isEmailValid($this->getColumnValue('email'))) {

            // user that have employee role
            $user = User::where('email', $this->getColumnValue('email'))->whereHas('roles', function ($q) {
                $q->where('name', 'employee');
            })->first();

            if (!$user) {
                $this->failJobWithMessage(__('messages.employeeNotFound'));
            }
            else {
                DB::beginTransaction();
                try {
                    Attendance::create([
                        'company_id' => $this->company?->id,
                        'user_id' => $user->id,
                        'clock_in_time' => Carbon::createFromFormat('Y-m-d H:i:s', $this->getColumnValue('clock_in_time'), $this->company?->timezone)->timezone('UTC')->format('Y-m-d H:i:s'),
                        'clock_in_ip' => $this->isColumnExists('clock_in_ip') ? $this->getColumnValue('clock_in_ip') : '127.0.0.1',
                        'clock_out_time' => $this->isColumnExists('clock_out_time') ? Carbon::createFromFormat('Y-m-d H:i:s', $this->getColumnValue('clock_out_time'), $this->company?->timezone)->timezone('UTC')->format('Y-m-d H:i:s') : null,
                        'clock_out_ip' => $this->isColumnExists('clock_out_ip') ? $this->getColumnValue('clock_out_ip') : null,
                        'working_from' => $this->isColumnExists('working_from') ? $this->getColumnValue('working_from') : 'office',
                        'late' => $this->isColumnExists('late') && str($this->getColumnValue('late'))->lower() == 'yes' ? 'yes' : 'no',
                        'half_day' => $this->isColumnExists('half_day') && str($this->getColumnValue('half_day'))->lower() == 'yes' ? 'yes' : 'no',
                    ]);

                    DB::commit();
                } catch (InvalidFormatException $e) {
                    DB::rollBack();
                    $this->failJob(__('messages.invalidDate'));
                } catch (Exception $e) {
                    DB::rollBack();
                    $this->failJobWithMessage($e->getMessage());
                }
            }
        }
        else {
            $this->failJob(__('messages.invalidData'));
        }
    }

}
