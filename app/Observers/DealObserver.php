<?php

namespace App\Observers;

use App\Events\DealEvent;
use App\Models\Deal;
use App\Models\LeadAgent;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Notifications\LeadAgentAssigned;
use App\Models\LeadSetting;
use Illuminate\Support\Facades\Notification;
use App\Traits\EmployeeActivityTrait;
use App\Notifications\LeadImported;


use App\Traits\DealHistoryTrait;

class DealObserver
{
    use DealHistoryTrait;
    use EmployeeActivityTrait;

    public function saving(Deal $deal)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $userID = (!is_null(user())) ? user()->id : null;
            $deal->last_updated_by = $userID;
        }

        $deal->next_follow_up = 'yes';
    }

    public function creating(Deal $deal)
    {
        $deal->hash = md5(microtime());

        if (!isRunningInConsoleOrSeeding()) {


            if (request()->has('added_by')) {
                $deal->added_by = request('added_by');


            }
            else {

                $userID = (!is_null(user())) ? user()->id : null;
                $deal->added_by = $userID;
            }

            if (company()) {
                $deal->company_id = company()->id;
            }

            if (!isRunningInConsoleOrSeeding()) {
                $categoryId = request()->category_id;

                $ticketSettings = LeadSetting::select('status')->first();

                if ($ticketSettings && $ticketSettings->status == 1) {
                    $agentCategoryData = LeadAgent::where('company_id', $deal->company_id)
                    ->where('status', 'enabled')
                    ->where('lead_category_id', $categoryId)
                    ->pluck('id')
                    ->toArray();

                    $dealData = $deal->where('company_id', $deal->company_id)
                        ->where('category_id', $categoryId)
                        ->whereIn('agent_id', $agentCategoryData)
                        ->whereNotNull('agent_id')
                        ->pluck('agent_id')
                        ->toArray();

                    $diffAgent = array_diff($agentCategoryData, $dealData);

                    if (is_null(request()->agent_id)) {
                        if (!empty($diffAgent)) {
                            $deal->agent_id = current($diffAgent);
                        }
                        else {
                            $agentDuplicateCount = array_count_values($dealData);

                            if (!empty($agentDuplicateCount)) {
                                $minVal = min($agentDuplicateCount);
                                $agent_id = array_search($minVal, $agentDuplicateCount);
                                $deal->agent_id = $agent_id;
                            }
                        }
                    }
                    else {
                        $leadAgent = LeadAgent::where('user_id', request()->agent_id)->where('lead_category_id', $categoryId)->first();
                        if(!is_null($leadAgent))
                        {
                            $deal->agent_id = $leadAgent->id;
                        }
                    }
                }
            }
        }
    }

    public function updating(Deal $deal)
    {

        if ($deal->isDirty('pipeline_stage_id')){
            self::createDealHistory($deal->id, 'stage-updated', agentId: $deal->agent_id, stageFromId: $deal->getOriginal('pipeline_stage_id'), stageToId: $deal->pipeline_stage_id);
        }

    }

    public function updated(Deal $deal)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'deal-updated', $deal->id, 'deal');
            }

            if (user() && !$deal->isDirty('pipeline_stage_id') && !$deal->isDirty('lead_pipeline_id') && !$deal->isDirty('agent_id')) {
                self::createDealHistory($deal->id, 'deal-updated', agentId: $deal->agent_id);
            }

            if ($deal->isDirty('lead_pipeline_id')){
                self::createDealHistory($deal->id, 'pipeline-updated', agentId: $deal->agent_id);
            }

            if ($deal->isDirty('agent_id')) {
                event(new DealEvent($deal, $deal->leadAgent, 'LeadAgentAssigned'));
            }



            if ($deal->isDirty('pipeline_stage_id') || $deal->isDirty('lead_pipeline_id')) {
                event(new DealEvent($deal, $deal->leadAgent, 'StageUpdated'));
            }
        }
    }

    public function created(Deal $deal)
    {

        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'deal-created', $deal->id, 'deal');
            }

            if(!session()->has('is_deal')){

                if (!session()->has('is_imported') && !session()->has('create_deal_with_lead')) {

                    if (request('agent_id') != '') {

                        event(new DealEvent($deal, $deal->leadAgent, 'LeadAgentAssigned'));
                        self::createDealHistory($deal->id, 'agent-assigned', agentId: $deal->agent_id);

                    }
                    else {

                        Notification::send(User::allAdmins($deal->company->id), new LeadAgentAssigned($deal));
                    }
                }else if(session()->has('is_imported')){

                    if (session('leads_count') == session('total_leads')) {

                        info('testing');

                        $admins = User::allAdmins(company()->id);
                        Notification::send($admins, new LeadImported());
                    }

                }
            }
        }
    }

    public function deleting(Deal $deal)
    {
        $notifyData = ['App\Notifications\LeadAgentAssigned'];
        \App\Models\Notification::deleteNotification($notifyData, $deal->id);

    }

    public function deleted(Deal $deal)
    {
        UniversalSearch::where('searchable_id', $deal->id)->where('module_type', 'lead')->delete();

        if (user()) {
            self::createEmployeeActivity(user()->id, 'deal-deleted');
        }
    }

}

