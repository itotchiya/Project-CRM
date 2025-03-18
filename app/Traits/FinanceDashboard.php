<?php

namespace App\Traits;

use App\Models\DashboardWidget;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Proposal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 *
 */
trait FinanceDashboard
{

    use CurrencyExchange, ClientDashboard;

    /**
     *
     * @return void
     */
    public function financeDashboard()
    {
        $this->viewFinanceDashboard = user()->permission('view_finance_dashboard');
        abort_403($this->viewFinanceDashboard !== 'all');

        $this->startDate = (request('startDate') != '') ? Carbon::createFromFormat($this->company->date_format, request('startDate')) : now($this->company->timezone)->startOfMonth();
        $this->endDate = (request('endDate') != '') ? Carbon::createFromFormat($this->company->date_format, request('endDate')) : now($this->company->timezone);
        $startDate = $this->startDate->toDateString();
        $endDate = $this->endDate->toDateString();

        $this->widgets = DashboardWidget::where('dashboard_type', 'admin-finance-dashboard')->get();
        $this->activeWidgets = $this->widgets->filter(function ($value, $key) {
            return $value->status == '1';
        })->pluck('widget_name')->toArray();

        // count of paid invoices
        $this->totalPaidInvoice = Invoice::where('status', 'paid')
            ->whereBetween(DB::raw('DATE(`issue_date`)'), [$startDate, $endDate])
            ->select('id')
            ->count();

        $this->totalUnPaidInvoice = Invoice::where(function ($query) {
            return $query->where('status', 'unpaid')
                ->orWhere('status', 'partial');
        })
            ->whereBetween(DB::raw('DATE(`issue_date`)'), [$startDate, $endDate])
            ->select('id')
            ->count();

        // Total Expense
        $expenses = Expense::whereBetween(DB::raw('DATE(expenses.`purchase_date`)'), [$startDate, $endDate])
            ->join('currencies', 'currencies.id', '=', 'expenses.currency_id')
            ->select(
                'expenses.id',
                'expenses.price',
                'expenses.exchange_rate as expenseExchangeRate',
                'currencies.currency_code',
                'currencies.is_cryptocurrency',
                'currencies.usd_price',
                'currencies.exchange_rate'
            )
            ->where('expenses.status', 'approved')
            ->get();

        $totalExpenses = 0;

        foreach ($expenses as $expense) {

            $defaultPrice = floatval($expense->price) * floatval($expense->expenseExchangeRate);
            $totalExpenses += $defaultPrice;

        }

        $this->totalExpenses = round($totalExpenses, 2);

        // Total Earning
        $paymentsModal = Payment::whereBetween(DB::raw('DATE(payments.`paid_on`)'), [$startDate, $endDate]);

        $payments = clone $paymentsModal;

        $payments = $payments->join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->where('payments.status', 'complete')
            ->select(
                DB::raw('(payments.amount) as total'),
                'currencies.currency_code',
                'currencies.is_cryptocurrency',
                'currencies.usd_price',
                'currencies.exchange_rate',
                'currencies.id as currency_id',
                'payments.exchange_rate',
            )
            ->get();

        $totalEarnings = 0;

        foreach ($payments as $payment) {

            if (isset($payment->currency) && $payment->currency->currency_code != $this->company->currency->currency_code && $payment->exchange_rate != 0) {

                if ($payment->currency->is_cryptocurrency == 'yes') {
                    $usdTotal = (floatval($payment->total) * floatval($payment->currency->usd_price));
                    $totalEarnings += floor(floatval($usdTotal) * floatval($payment->currency->exchange_rate));
                }
                else {
                    $totalEarnings += floatval($payment->total) * floatval($payment->exchange_rate);
                }

            }
            else {
                $totalEarnings += $payment->total;
            }

        }

        $this->totalEarnings = round($totalEarnings, 2);

        // Total Pending amount
        $invoices = Invoice::whereBetween(DB::raw('DATE(invoices.`issue_date`)'), [$startDate, $endDate])
            ->join('currencies', 'currencies.id', '=', 'invoices.currency_id')
            ->where(function ($q) {
                $q->where('invoices.status', 'unpaid');
                $q->orWhere('invoices.status', 'partial');
            })
            ->select(
                'invoices.*',
                'currencies.currency_code',
                'currencies.is_cryptocurrency',
                'currencies.usd_price',
                'currencies.exchange_rate'
            )
            ->get();

        $totalPendingAmount = 0;

        foreach ($invoices as $invoice) {
            if ($invoice->currency->currency_code != $this->company->currency->currency_code && $invoice->currency->exchange_rate != 0) {
                if ($invoice->currency->is_cryptocurrency == 'yes') {
                    $usdTotal = ($invoice->due_amount * $invoice->currency->usd_price);
                    $totalPendingAmount += floor(floatval($usdTotal) * floatval($invoice->currency->exchange_rate));

                }
                else {
                    $totalPendingAmount += floatval($invoice->due_amount) * floatval($invoice->currency->exchange_rate);
                }
            }
            else {
                $totalPendingAmount += $invoice->due_amount;
            }
        }

        $this->totalPendingAmount = round($totalPendingAmount, 2);
        $this->invoiceOverviewChartData = $this->invoiceOverviewChartData($startDate, $endDate);
        $this->estimateOverviewChartData = $this->estimateOverviewChartData($startDate, $endDate);
        $this->proposalOverviewChartData = $this->proposalOverviewChartData($startDate, $endDate);
        $this->clientEarningChart = $this->clientEarningChart($startDate, $endDate);
        $this->projectEarningChartData = $this->projectEarningChartData($startDate, $endDate);

        $this->view = 'dashboard.ajax.finance';
    }

    public function invoiceOverviewChartData($startDate, $endDate)
    {
        $data['values'] = [];
        $data['colors'] = [];

        $allInvoice = Invoice::whereBetween(DB::raw('DATE(`issue_date`)'), [$startDate, $endDate])->get();

        $data['values'][] = $allInvoice->filter(function ($value, $key) {
            return $value->status == 'draft';
        })->count();
        $data['colors'][] = '#1d82f5';

        $data['values'][] = $allInvoice->filter(function ($value, $key) {
            return $value->send_status == 0;
        })->count();
        $data['colors'][] = '#4d4f5c';

        $data['values'][] = $allInvoice->filter(function ($value, $key) {
            return $value->status == 'unpaid';
        })->count();
        $data['colors'][] = '#D30000';

        $data['values'][] = $allInvoice->filter(function ($value, $key) {
            return ($value->status == 'unpaid' || $value->status == 'partial') && $value->due_date->lessThan(now());
        })->count();
        $data['colors'][] = '#99A5B5';

        $data['values'][] = $allInvoice->filter(function ($value, $key) {
            return $value->status == 'partial';
        })->count();
        $data['colors'][] = '#FCBD01';


        $data['values'][] = $allInvoice->filter(function ($value, $key) {
            return $value->status == 'paid';
        })->count();
        $data['colors'][] = '#2CB100';

        $data['labels'] = [__('modules.dashboard.invoiceDraft'), __('modules.dashboard.invoiceNotSent'), __('modules.dashboard.invoiceUnpaid'), __('modules.dashboard.invoiceOverdue'), __('modules.dashboard.invoicePartiallyPaid'), __('modules.dashboard.invoicePaid')];

        return $data;
    }

    public function estimateOverviewChartData($startDate, $endDate)
    {
        $data['values'] = [];
        $data['colors'] = [];

        $allEstimate = Estimate::whereBetween(DB::raw('DATE(`valid_till`)'), [$startDate, $endDate])->get();

        $data['values'][] = $allEstimate->filter(function ($value, $key) {
            return $value->status == 'draft';
        })->count();
        $data['colors'][] = '#1d82f5';

        $data['values'][] = $allEstimate->filter(function ($value, $key) {
            return $value->send_status == 0;
        })->count();
        $data['colors'][] = '#4d4f5c';

        $data['values'][] = $allEstimate->filter(function ($value, $key) {
            return $value->send_status == 1;
        })->count();
        $data['colors'][] = '#FCBD01';

        $data['values'][] = $allEstimate->filter(function ($value, $key) {
            return $value->status == 'declined';
        })->count();
        $data['colors'][] = '#99A5B5';

        $data['values'][] = $allEstimate->filter(function ($value, $key) {
            return $value->status == 'waiting' && $value->valid_till->lessThan(now());
        })->count();
        $data['colors'][] = '#D30000';


        $data['values'][] = $allEstimate->filter(function ($value, $key) {
            return $value->status == 'accepted';
        })->count();
        $data['colors'][] = '#2CB100';

        $data['labels'] = [__('modules.dashboard.estimateDraft'), __('modules.dashboard.estimateNotSent'), __('modules.dashboard.estimateSent'), __('modules.dashboard.estimateDeclined'), __('modules.dashboard.estimateExpired'), __('modules.dashboard.estimateAccepted')];

        return $data;
    }

    public function proposalOverviewChartData($startDate, $endDate)
    {
        $data['values'] = [];
        $data['colors'] = [];

        $allProposal = Proposal::whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate])->get();

        $data['values'][] = $allProposal->filter(function ($value, $key) {
            return $value->status == 'waiting';
        })->count();
        $data['colors'][] = '#FCBD01';

        $data['values'][] = $allProposal->filter(function ($value, $key) {
            return $value->status == 'declined';
        })->count();
        $data['colors'][] = '#D30000';

        $data['values'][] = $allProposal->filter(function ($value, $key) {
            return $value->status = 'waiting' && $value->valid_till->lessThan(now());
        })->count();
        $data['colors'][] = '#99A5B5';

        $data['values'][] = $allProposal->filter(function ($value, $key) {
            return $value->status == 'accepted';
        })->count();
        $data['colors'][] = '#2CB100';

        $data['values'][] = $allProposal->filter(function ($value, $key) {
            return $value->invoice_convert == 1;
        })->count();
        $data['colors'][] = '#1d82f5';

        $data['labels'] = [__('modules.dashboard.proposalWaiting'), __('modules.dashboard.proposalDeclined'), __('modules.dashboard.proposalExpired'), __('modules.dashboard.proposalAccepted'), __('modules.dashboard.proposalConverted')];

        return $data;
    }

    public function projectEarningChartData($startDate, $endDate)
    {
        // earnings By Projects
        $paymentsModal = Payment::whereBetween(DB::raw('DATE(payments.`paid_on`)'), [$startDate, $endDate]);
        $projects = clone $paymentsModal;
        $projects->join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->join('projects', 'projects.id', '=', 'payments.project_id')
            ->where('payments.status', 'complete')
            ->orderBy('payments.paid_on', 'ASC')
            ->select(
                'payments.amount as total',
                'payments.exchange_rate as exchange_rate',
                'currencies.currency_code as currency_code',
                'currencies.is_cryptocurrency as is_cryptocurrency',
                'currencies.usd_price as usd_price',
                'projects.project_name'
            );

        $invoices = clone $paymentsModal;
        $invoices = $invoices->join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->join('projects', 'projects.id', '=', 'invoices.project_id')
            ->where('payments.status', 'complete')
            ->orderBy('payments.paid_on', 'ASC')
            ->select(
                'payments.amount as total',
                'payments.exchange_rate as exchange_rate',
                'currencies.currency_code as currency_code',
                'currencies.is_cryptocurrency as is_cryptocurrency',
                'currencies.usd_price as usd_price',
                'projects.project_name as project_name'
            )
            ->union($projects)
            ->get();

        $earningsByProjects = array();

        foreach ($invoices as $invoice) {
            if (!array_key_exists($invoice->project_name, $earningsByProjects)) {
                $earningsByProjects[$invoice->project_name] = 0;
            }

            if ($invoice->currency_code != $this->company->currency->currency_code && $invoice->exchange_rate != 0) {
                if ($invoice->is_cryptocurrency == 'yes') {
                    $usdTotal = ($invoice->total * $invoice->usd_price);
                    $earningsByProjects[$invoice->project_name] = $earningsByProjects[$invoice->project_name] + round(floor(floatval($usdTotal) * floatval($invoice->exchange_rate)), 2);

                }
                else {
                    $earningsByProjects[$invoice->project_name] = $earningsByProjects[$invoice->project_name] + round((floatval($invoice->total) * floatval($invoice->exchange_rate)), 2);
                }
            }
            else {
                $earningsByProjects[$invoice->project_name] = $earningsByProjects[$invoice->project_name] + round($invoice->total, 2);
            }
        }

        $data['labels'] = array_keys($earningsByProjects);
        $data['values'] = array_values($earningsByProjects);
        $data['colors'] = [$this->appTheme->header_color];
        $data['name'] = __('app.earnings');

        return $data;
    }

}
