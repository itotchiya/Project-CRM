<?php

namespace App\Traits;

use App\Models\ContractSign;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectStatusSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 *
 */
trait ClientPanelDashboard
{

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function clientPanelDashboard()
    {
        $viewInvoicePermission = user()->permission('view_invoices');

        $this->modules = user_modules();
        $this->counts = User::select(
            DB::raw('(select count(projects.id) from `projects` where client_id = ' . $this->user->id . ' and deleted_at IS NULL and projects.company_id = ' . company()->id . ') as totalProjects'),
            DB::raw('(select count(tickets.id) from `tickets` where (status="open" or status="pending") and user_id = ' . $this->user->id . '  and tickets.company_id = ' . company()->id . ' and deleted_at IS NULL) as totalUnResolvedTickets')
        )
            ->first();

        // Invoices paid
        $this->totalPaidInvoice = Invoice::where(function ($query) {
            $query->where('invoices.status', 'paid');
        })
            ->where('invoices.client_id', user()->id)
            ->where('invoices.send_status', 1)
            ->where('invoices.credit_note', 0)
            ->select(
                'invoices.id'
            );

        if ($viewInvoicePermission == 'added') {
            $this->totalPaidInvoice = $this->totalPaidInvoice->where('invoices.added_by', user()->id);
        }

        $this->totalPaidInvoice = $this->totalPaidInvoice->count();


        // Total Pending invoices
        $this->totalUnPaidInvoice = Invoice::where(function ($query) {
            $query->where('invoices.status', 'unpaid')
                ->orWhere('invoices.status', 'partial');
        })
            ->where('invoices.client_id', user()->id)
            ->where('invoices.send_status', 1)
            ->where('invoices.credit_note', 0)
            ->select(
                'invoices.id'
            );

        if ($viewInvoicePermission == 'added') {
            $this->totalUnPaidInvoice = $this->totalUnPaidInvoice->where('invoices.added_by', user()->id);
        }

        $this->totalUnPaidInvoice = $this->totalUnPaidInvoice->count();

        $this->totalContractsSigned = ContractSign::whereHas('contract', function ($query) {
            $query->where('client_id', user()->id);
        })->count();

        $this->viewMilestonePermission = user()->permission('view_project_milestones');

        $this->pendingMilestone = ProjectMilestone::query();

        if ($this->viewMilestonePermission != 'none') {
            $this->pendingMilestone = ProjectMilestone::with('project', 'currency')
                ->whereHas('project', function ($query) {
                    $query->where('client_id', user()->id);
                })
                ->where('status', 'incomplete')
                ->get();
        }


        $this->statusWiseProject = $this->projectStatusChartData();

        return view('dashboard.client.index', $this->data);
    }

    public function projectStatusChartData()
    {
        $labels = ProjectStatusSetting::where('status', 'active')->pluck('status_name');
        $data['labels'] = ProjectStatusSetting::where('status', 'active')->pluck('status_name');
        $data['colors'] = ProjectStatusSetting::where('status', 'active')->pluck('color');
        $data['values'] = [];

        foreach ($labels as $label) {
            $data['values'][] = Project::where('client_id', user()->id)->where('status', $label)->count();
        }

        return $data;
    }

}
