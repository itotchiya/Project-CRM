<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IncomeVsExpenseReportController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.incomeVsExpenseReport';
    }

    public function index()
    {
        abort_403(user()->permission('view_income_expense_report') != 'all');

        $this->fromDate = now($this->company->timezone)->startOfMonth();
        $this->toDate = now($this->company->timezone);

        if (request()->ajax()) {
            $this->chartData = $this->getGraphData();
            $html = view('reports.income-expense.chart', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'totalEarning' => currency_format($this->chartData['totalEarning'], company()->currency_id), 'totalExpense' => currency_format($this->chartData['totalExpense'], company()->currency_id)]);
        }

        return view('reports.income-expense.index', $this->data);
    }

    public function getGraphData()
    {
        $graphData = [];
        $incomes = [];
        $fromDate = now($this->company->timezone)->startOfMonth()->toDateString();
        $toDate = now($this->company->timezone)->toDateString();

        if (request()->startDate !== null && request()->startDate != 'null' && request()->startDate != '') {
            $fromDate = Carbon::createFromFormat($this->company->date_format, request()->startDate)->toDateString();
        }

        if (request()->endDate !== null && request()->endDate != 'null' && request()->endDate != '') {
            $toDate = Carbon::createFromFormat($this->company->date_format, request()->endDate)->toDateString();
        }

        $invoices = Payment::join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->where(DB::raw('DATE(`paid_on`)'), '>=', $fromDate)
            ->where(DB::raw('DATE(`paid_on`)'), '<=', $toDate)
            ->where('payments.status', 'complete')
            ->orderBy('paid_on', 'ASC')
            ->get([
                DB::raw('DATE_FORMAT(paid_on,"%d-%M-%y") as date'),
                DB::raw('YEAR(paid_on) year, MONTH(paid_on) month'),
                DB::raw('amount as total'),
                'currencies.id as currency_id',
                'payments.exchange_rate',
                'payments.default_currency_id'
            ]);

        foreach ($invoices as $invoice) {

            if((is_null($invoice->default_currency_id) && is_null($invoice->exchange_rate)) ||
            (!is_null($invoice->default_currency_id) && Company()->currency_id != $invoice->default_currency_id))
            {
                $currency = Currency::findOrFail($invoice->currency_id);
                $exchangeRate = $currency->exchange_rate;
            }
            else {
                $exchangeRate = $invoice->exchange_rate;
            }

            if (!isset($incomes[$invoice->date])) {
                $incomes[$invoice->date] = 0;
            }

            if ($invoice->currency_id != $this->company->currency_id && $invoice->total > 0 && $exchangeRate > 0) {
                /** @phpstan-ignore-next-line */
                $incomes[$invoice->date] += floatval($invoice->total) * floatval($exchangeRate);
            }
            else {
                $incomes[$invoice->date] += floatval($invoice->total);
            }
        }

        $expenses = [];
        $expenseResults = Expense::join('currencies', 'currencies.id', '=', 'expenses.currency_id')
            ->where(DB::raw('DATE(`purchase_date`)'), '>=', $fromDate)
            ->where(DB::raw('DATE(`purchase_date`)'), '<=', $toDate)
            ->where('expenses.status', 'approved')
            ->get([
                'expenses.price',
                'expenses.purchase_Date as date',
                DB::raw('DATE_FORMAT(purchase_date,\'%d-%M-%y\') as date'),
                'currencies.id as currency_id',
                'expenses.exchange_rate',
                'expenses.default_currency_id'
            ]);

        foreach ($expenseResults as $expenseResult) {

            if((is_null($expenseResult->default_currency_id) && is_null($expenseResult->exchange_rate)) ||
            (!is_null($expenseResult->default_currency_id) && Company()->currency_id != $expenseResult->default_currency_id))
            {
                $currency = Currency::findOrFail($expenseResult->currency_id);
                $exchangeRate = $currency->exchange_rate;
            }
            else {
                $exchangeRate = $expenseResult->exchange_rate;
            }

            if (!isset($expenses[$expenseResult->date])) {
                $expenses[$expenseResult->date] = 0;
            }

            if ($expenseResult->currency_id != $this->company->currency_id && $expenseResult->price > 0 && $exchangeRate > 0) {
                /** @phpstan-ignore-next-line */
                $expenses[$expenseResult->date] += floatval($expenseResult->price) * floatval($exchangeRate);
            }
            else {
                $expenses[$expenseResult->date] += floatval($expenseResult->price);
            }
        }


        $dates = array_keys(array_merge($incomes, $expenses));

        foreach ($dates as $date) {
            $graphData[] = [
                'y' => $date,
                'a' => isset($incomes[$date]) ? round($incomes[$date], 2) : 0,
                'b' => isset($expenses[$date]) ? round($expenses[$date], 2) : 0
            ];
        }

        usort($graphData, function ($a, $b) {
            $t1 = strtotime($a['y']);
            $t2 = strtotime($b['y']);
            return $t1 - $t2;
        });

        $graphData = collect($graphData);

        $data['labels'] = $graphData->pluck('y');
        $data['values'][] = $graphData->pluck('a');
        $data['values'][] = $graphData->pluck('b');
        $data['totalEarning'] = $graphData->sum('a');
        $data['totalExpense'] = $graphData->sum('b');
        $data['colors'] = ['#1D82F5', '#d30000'];
        $data['name'][] = __('app.income');
        $data['name'][] = __('app.expense');

        return $data;
    }

}
