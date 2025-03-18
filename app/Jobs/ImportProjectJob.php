<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Project;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\UniversalSearchTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ProjectActivity;
use App\Traits\ExcelImportable;

class ImportProjectJob implements ShouldQueue
{

    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UniversalSearchTrait;
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
        if ($this->isColumnExists('project_name') && $this->isColumnExists('start_date')) {
            $client = null;

            if (!empty($this->isColumnExists('client_email'))) {
                // user that have client role
                $client = User::where('email', $this->getColumnValue('client_email'))->whereHas('roles', function ($q) {
                    $q->where('name', 'client');
                })->first();
            }

            DB::beginTransaction();
            try {
                $project = new Project();
                $project->company_id = $this->company?->id;
                $project->project_name = $this->getColumnValue('project_name');

                $project->project_summary = $this->isColumnExists('project_summary') ? $this->getColumnValue('project_summary') : null;

                $project->start_date = Carbon::createFromFormat('Y-m-d', $this->getColumnValue('start_date'))->format('Y-m-d');
                $project->deadline = $this->isColumnExists('deadline') ? (!empty(trim($this->getColumnValue('deadline'))) ? Carbon::createFromFormat('Y-m-d', $this->getColumnValue('deadline'))->format('Y-m-d') : null) : null;

                if ($this->isColumnExists('notes')) {
                    $project->notes = $this->getColumnValue('notes');
                }

                $project->client_id = $client ? $client->id : null;

                $project->project_budget = $this->isColumnExists('project_budget') ? $this->getColumnValue('project_budget') : null;

                $project->currency_id = $this->company?->currency_id;

                $project->status = $this->isColumnExists('status') ? strtolower(trim($this->getColumnValue('status'))) : 'not started';

                $project->save();

                $this->logSearchEntry($project->id, $project->project_name, 'projects.show', 'project', $project->company_id);
                $this->logProjectActivity($project->id, 'messages.updateSuccess');
                DB::commit();
            } catch (InvalidFormatException $e) {
                DB::rollBack();
                $this->failJob(__('messages.invalidDate'));
            } catch (Exception $e) {
                DB::rollBack();
                $this->failJobWithMessage($e->getMessage());
            }

        }
        else {
            $this->failJob(__('messages.invalidData'));
        }
    }

    public function logProjectActivity($projectId, $text)
    {
        $activity = new ProjectActivity();
        $activity->project_id = $projectId;
        $activity->activity = $text;
        $activity->save();
    }

}
