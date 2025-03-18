<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadPipeline;
use App\Models\LeadSource;
use App\Models\PipelineStage;
use App\Models\User;
use App\Traits\ExcelImportable;
use App\Traits\UniversalSearchTrait;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ImportLeadJob implements ShouldQueue
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
        $leadCount = Session::get('total_leads');
        $leadCount++;
        info($leadCount);
        Session::put('total_leads', $leadCount);

        if ($this->isColumnExists('name')) {

            if ($this->isColumnExists('email') && $this->isEmailValid($this->getColumnValue('email'))) {
                $lead = Lead::where('client_email', $this->getColumnValue('email'))->where('company_id', $this->company?->id)->first();
                $user = User::where('email', $this->getColumnValue('email'))->first();

                if ($lead || $user) {

                    $this->failJobWithMessage(__('messages.duplicateEntryForEmail') . $this->getColumnValue('email'));

                    return;
                }
            }
            else {
                $this->failJob(__('messages.invalidData'));

                return;
            }

            DB::beginTransaction();
            Session::put('is_imported', true);
            Session::put('is_deal', true);
            try {

                $leadSource = null;

                if ($this->isColumnExists('source')) {
                    $leadSource = LeadSource::where('type', $this->getColumnValue('source'))->where('company_id', $this->company?->id)->first();
                }

                $lead = new Lead();
                $lead->company_id = $this->company?->id;
                $lead->client_name = $this->getColumnValue('name');
                $lead->client_email = $this->isColumnExists('email') && filter_var($this->getColumnValue('email'), FILTER_VALIDATE_EMAIL) ? $this->getColumnValue('email') : null;
                $lead->note = $this->isColumnExists('note') ? $this->getColumnValue('note') : null;
                $lead->company_name = $this->isColumnExists('company_name') ? $this->getColumnValue('company_name') : null;
                $lead->website = $this->isColumnExists('company_website') ? $this->getColumnValue('company_website') : null;
                $lead->mobile = $this->isColumnExists('mobile') ? $this->getColumnValue('mobile') : null;
                $lead->office = $this->isColumnExists('company_phone') ? $this->getColumnValue('company_phone') : null;
                $lead->country = $this->isColumnExists('country') ? $this->getColumnValue('country') : null;
                $lead->state = $this->isColumnExists('state') ? $this->getColumnValue('state') : null;
                $lead->city = $this->isColumnExists('city') ? $this->getColumnValue('city') : null;
                $lead->postal_code = $this->isColumnExists('postal_code') ? $this->getColumnValue('postal_code') : null;
                $lead->address = $this->isColumnExists('address') ? $this->getColumnValue('address') : null;
                $lead->source_id = $leadSource?->id;
                $lead->created_at = $this->isColumnExists('created_at') ? Carbon::parse($this->getColumnValue('created_at')) : now();

                $leads = Session::get('leads', []);

                $leads[] = [
                    'lead_name'  => $lead->client_name,
                    'email' => $lead->client_email,
                    'deal_name' => $lead->client_name,
                ];

                info($leads);
                Session::put('leads', $leads);

                $lead->save();

                $leadPipeline = LeadPipeline::where('default', '1')->where('company_id', $lead->company_id)->first();
                $leadStage = PipelineStage::where('default', '1')->where('lead_pipeline_id', $leadPipeline->id)->where('company_id', $lead->company_id)->first();

                $deal = new Deal();
                $deal->company_id = $lead->company_id;
                $deal->lead_id = $lead->id;
                $deal->name = $lead->client_name ?? '';
                $deal->lead_pipeline_id = $leadPipeline->id;
                $deal->pipeline_stage_id = $leadStage->id;
                $deal->value = 0;
                $deal->currency_id = $lead->company?->currency_id;
                $deal->save();

                // Log search
                $this->logSearchEntry($lead->id, $lead->client_name, 'lead-contact', 'lead', $lead->company_id);

                if (!is_null($lead->client_email)) {
                    $this->logSearchEntry($lead->id, $lead->client_email, 'lead-contact', 'lead', $lead->company_id);
                }

                if (!is_null($lead->company_name)) {
                    $this->logSearchEntry($lead->id, $lead->company_name, 'lead-contact', 'lead', $lead->company_id);
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $this->failJobWithMessage($e->getMessage());
            }
        }
        else {
            $this->failJob(__('messages.invalidData'));
        }


    }

}

