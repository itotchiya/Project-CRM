<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use App\Helper\Reply;
use App\Models\Company;
use Carbon\CarbonPeriod;
use App\Models\LeadAgent;
use App\Models\LeadSource;
use App\Models\LeadCategory;
use App\Models\LeadPipeline;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use App\Models\PipelineStage;
use App\Exports\DealReportExport;
use Illuminate\Support\Facades\DB;
use App\DataTables\DealReportDataTable;
use App\DataTables\LeadReportDataTable;
use App\DataTables\LeadContactDataTable;

class LeadReportController extends AccountBaseController
{
    private $excel;

    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
        parent::__construct();


    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {

        $tab = request('tab');
        $this->pageTitle = 'app.menu.deal';
        $this->view = 'reports.lead.profile';

        switch ($tab) {
        case 'lead':
            return $this->leadContact();
        case 'chart':
            return $this->averageDealSizeReport();
            break;
        default:
            return $this->profile();
            break;
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'profile';

        return view('reports.lead.index', $this->data);
    }

    public function profile()
    {
        $this->pageTitle = 'modules.lead.profile';

        if (!request()->ajax()) {
              $this->fromDate = now($this->company->timezone)->startOfMonth();
              $this->toDate = now($this->company->timezone);

              $this->agents = LeadAgent::with('user')
                  ->join('users', 'users.id', 'lead_agents.user_id')->get();
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';

        $this->view = 'reports.lead.profile';

        $dataTable = new LeadReportDataTable();

        return $dataTable->render('reports.lead.index', $this->data);
    }

    public function leadContact( )
    {
        $this->employees = User::allEmployees();
        $this->pageTitle = 'modules.leadContact.title';

        $this->viewLeadPermission = $viewPermission = user()->permission('view_lead');

        abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

        if (!request()->ajax()) {
            $this->categories = LeadCategory::get();
            $this->sources = LeadSource::get();

        }

        $getTotal = 'withDatatable';

        $dataTable = new LeadContactDataTable();
        $tab = request('tab');
        $this->activeTab = $tab ?: 'lead';

        return $dataTable->render('reports.lead.lead-report', $this->data);

    }

    public function totalContact()
    {
        $request = request();
        $this->viewLeadPermission = $viewPermission = user()->permission('view_lead');
        $this->startDate = (request('start_date') != '') ? Carbon::createFromFormat($this->company->date_format, request('start_date')) : now($this->company->timezone)->startOfMonth();
        $this->endDate = (request('end_date') != '') ? Carbon::createFromFormat($this->company->date_format, request('end_date')) : now($this->company->timezone);
        $startDate = $this->startDate->toDateString();
        $endDate = $this->endDate->toDateString();
        $totalCount = Lead::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->count();

        return Reply::dataOnly(['totalCount' => $totalCount]);
    }

    public function averageDealSizeReport()
    {
        $this->pageTitle = 'app.menu.dealReport';
        $request = request();
        $companyId = $this->company->id;

        $this->currentYear = now()->format('Y');
        $this->currentMonth = now()->month;
        $this->pipelines = LeadPipeline::all();
        $this->categories = LeadCategory::all();

        $defaultPipelineId = $this->pipelines->filter(function ($value) {
            return $value->default == 1;
        })->first()->id;


        $selectedYear = $request->year ? $request->year : now()->format('Y');
        $pipelineId = $request->pipeline ? $request->pipeline : $defaultPipelineId;
        $categoryId = $request->category ? $request->category : null;

        $startDate = Carbon::createFromFormat('Y-m-d', $selectedYear.'-01-'.'01')->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', $selectedYear.'-12-'.'31')->endOfYear();

        $deals = Deal::select('pipeline_stages.name as stage_name', 'deals.value', 'deals.close_date')
            ->join('pipeline_stages', 'deals.pipeline_stage_id', '=', 'pipeline_stages.id')
            ->join('lead_pipelines', 'lead_pipelines.id', '=', 'pipeline_stages.lead_pipeline_id')
            ->where('lead_pipelines.id', $pipelineId)
            ->when($categoryId, function ($query) use ($categoryId) {
                return $query->where('deals.category_id', $categoryId);
            })
            ->whereBetween('deals.close_date', [$startDate, $endDate])
            ->get();

            $dealsByStageAndMonth = $deals->groupBy(function ($deal) {
                return Carbon::parse($deal->close_date)->format('m');
            });

            $pipelineStages = PipelineStage::where('lead_pipeline_id', $pipelineId)->get();

            $monthlyTotals = [];
            $stageColors = [];

        // Fetch stage colors from the database
        foreach ($pipelineStages as $stage) {
            $stageColors[$stage->name] = $stage->label_color;
        }

        // Loop through each month of the year
        for ($month = 1; $month <= 12; $month++) {
            $monthKey = str_pad($month, 2, '0', STR_PAD_LEFT);

            foreach ($pipelineStages as $stage) {
                $monthlyTotals[$monthKey][$stage->name] = 0;

            }


            if (isset($dealsByStageAndMonth[$monthKey])) {
                foreach ($dealsByStageAndMonth[$monthKey] as $deal) {
                    $monthlyTotals[$monthKey][$deal->stage_name] += $deal->value;
                }
            }
        }

        $lastYear = Carbon::now()->subYear()->format('Y');

        $this->years = range(Carbon::now()->year, $lastYear);

        $dealsByMonth = $deals->groupBy(function($deal) {
            return $deal->close_date->format('m');
        });

        $monthlyDealCounts = [];

        foreach ($dealsByMonth as $month => $dealsInMonth) {
            $monthlyDealCounts[$month] = $dealsInMonth->count();
        }

        $monthRange = CarbonPeriod::create($startDate, '1 month', $endDate);

        foreach ($monthRange as $month) {

            $formattedMonth = Carbon::parse($month)->format('M');
            $numMonth = Carbon::parse($month)->format('m');

            $totalValue = $deals->filter(function ($value) use($numMonth, $selectedYear) {
                return $value->close_date->format('m') == $numMonth && $value->close_date->format('Y') == $selectedYear;
            })->sum('value');
            $count = $monthlyDealCounts[$numMonth] ?? 0;
            $averageValue = $count > 0 ? $totalValue / $count : 0;
            $averageValue1 = round($averageValue, 1);

            $monthlyData[] = [
                'label' => $formattedMonth,
                'value' => $averageValue1,

            ];
            $value[] = $averageValue1;

            $lineChartDataset = [
                'name' => 'Average',
                'chartType' => 'line',
                'values' => array_column($monthlyData, 'value'),
                'color' => 'black',
            ];
        }

        $datasets = [];

        foreach ($pipelineStages as $stage) {
            $dataset = [
                'name' => $stage->name,
                'chartType' => 'bar',
                'values' => [],
                'color' => $stageColors[$stage->name] ?? '#d4f542'
            ];


            for ($month = 1; $month <= 12; $month++) {
                $monthKey = str_pad($month, 2, '0', STR_PAD_LEFT);
                $dataset['values'][] = $monthlyTotals[$monthKey][$stage->name] ?? 0;
            }


            $datasets[] = $dataset;
        }

        $datasets[] = $lineChartDataset;

        $this->data['datasets'] = $datasets;

        $dealReports = Deal::select(
            DB::raw('MONTH(close_date) as month'),
            DB::raw('COUNT(*) as deals_closed'),
            DB::raw('SUM(value) as total_deal_amount'),
            DB::raw('AVG(value) as average_deal_amount'),
            DB::raw("(SELECT COUNT(wonDeals.id)
                  FROM deals as wonDeals
                  INNER JOIN pipeline_stages on wonDeals.pipeline_stage_id = pipeline_stages.id
                  WHERE wonDeals.close_date IS NOT NULL
                  AND wonDeals.company_id = $companyId
                  AND pipeline_stages.slug = 'win'
                  AND wonDeals.lead_pipeline_id = $pipelineId
                  AND MONTH(wonDeals.close_date) = MONTH(deals.close_date)
                  AND YEAR(wonDeals.close_date) = YEAR(deals.close_date)) as won_deals"),
            DB::raw("(SELECT SUM(wonDealsAmount.value)
                  FROM deals as wonDealsAmount
                  INNER JOIN pipeline_stages as pipelineStag on wonDealsAmount.pipeline_stage_id = pipelineStag.id
                  WHERE wonDealsAmount.close_date IS NOT NULL
                  AND wonDealsAmount.company_id = $companyId
                  AND pipelineStag.slug = 'win'
                  AND wonDealsAmount.lead_pipeline_id = $pipelineId
                  AND MONTH(wonDealsAmount.close_date) = MONTH(deals.close_date)
                  AND YEAR(wonDealsAmount.close_date) = YEAR(deals.close_date)) as deals_won_amount"),
            DB::raw("(SELECT COUNT(lostDeals.id)
                  FROM deals as lostDeals
                  INNER JOIN pipeline_stages on lostDeals.pipeline_stage_id = pipeline_stages.id
                  WHERE lostDeals.close_date IS NOT NULL
                  AND lostDeals.company_id = $companyId
                  AND pipeline_stages.slug = 'lost'
                  AND lostDeals.lead_pipeline_id = $pipelineId
                  AND MONTH(lostDeals.close_date) = MONTH(deals.close_date)
                  AND YEAR(lostDeals.close_date) = YEAR(deals.close_date)) as lost_deals"),
            DB::raw("(SELECT SUM(lostDeal_amount.value)
                  FROM deals as lostDeal_amount
                  INNER JOIN pipeline_stages on lostDeal_amount.pipeline_stage_id = pipeline_stages.id
                  WHERE lostDeal_amount.close_date IS NOT NULL
                  AND pipeline_stages.slug = 'lost'
                  AND lostDeal_amount.company_id = $companyId
                  AND lostDeal_amount.lead_pipeline_id = $pipelineId
                  AND MONTH(lostDeal_amount.close_date) = MONTH(deals.close_date)
                  AND YEAR(lostDeal_amount.close_date) = YEAR(deals.close_date)) as deals_lost_amount"),
            DB::raw("(SELECT COUNT(other_stages.id)
                  FROM deals as other_stages
                  INNER JOIN pipeline_stages on other_stages.pipeline_stage_id = pipeline_stages.id
                  WHERE other_stages.close_date IS NOT NULL
                  AND pipeline_stages.slug != 'lost'
                  AND pipeline_stages.slug != 'win'
                  AND other_stages.company_id = $companyId
                  AND other_stages.lead_pipeline_id = $pipelineId
                  AND MONTH(other_stages.close_date) = MONTH(deals.close_date)
                  AND YEAR(other_stages.close_date) = YEAR(deals.close_date)) as deals_other_stages"),
            DB::raw("(SELECT SUM(other_stages_value.value)
                FROM deals as other_stages_value
                INNER JOIN pipeline_stages on other_stages_value.pipeline_stage_id = pipeline_stages.id
                WHERE other_stages_value.close_date IS NOT NULL
                AND pipeline_stages.slug != 'lost'
                AND pipeline_stages.slug != 'win'
                AND other_stages_value.company_id = $companyId
                AND other_stages_value.lead_pipeline_id = $pipelineId
                AND MONTH(other_stages_value.close_date) = MONTH(deals.close_date)
                AND YEAR(other_stages_value.close_date) = YEAR(deals.close_date)) as deals_other_stages_value"),
        )->where(DB::raw('YEAR(close_date)'), $selectedYear)
            ->where('lead_pipeline_id', $pipelineId)
            ->when($categoryId, function ($query) use ($categoryId) {
                return $query->where('deals.category_id', $categoryId); 
            })
            ->whereNotNull('close_date')
            ->groupBy(DB::raw('MONTH(close_date)'))
            ->get();


        $this->dealDatas = collect(range(1, 12))->map(function ($month) use ($dealReports) {
            $deal = $dealReports->firstWhere('month', $month);
            return [
            'month' => Carbon::createFromDate(null, $month, 1)->format('F'),
            'deals_closed' => $deal && $deal->deals_closed ? $deal->deals_closed : 0,
            'total_deal_amount' => $deal && $deal->total_deal_amount ? round($deal->total_deal_amount, 2) : 0,
            'average_deal_amount' => $deal && $deal->average_deal_amount ? round($deal->average_deal_amount, 2) : 0,
            'won_deals' => $deal && $deal->won_deals ? $deal->won_deals : 0,
            'deals_won_amount' => $deal && $deal->deals_won_amount ? round($deal->deals_won_amount, 2) : 0,
            'lost_deals' => $deal && $deal->lost_deals ? $deal->lost_deals : 0,
            'deals_lost_amount' => $deal && $deal->deals_lost_amount ? round($deal->deals_lost_amount, 2) : 0,
            'other_stages' => $deal && $deal->deals_other_stages ? $deal->deals_other_stages : 0,
            'other_stages_value' => $deal && $deal->deals_other_stages_value ? round($deal->deals_other_stages_value, 2) : 0,
            ];
        });

        $tab = request('tab');
        $this->activeTab = $tab ?: 'chart';

        if ($request->ajax()) {
            $html = view('reports.lead.deal-report', ['dealDatas' => $this->dealDatas])->render();
            return response()->json(['datasets' => $datasets, 'html' => $html]);

        } else {
            return view('reports.lead.report-chart', $this->data);
        }
    }

    
    public function exportDealReport($year, $pipelineId, $categoryId = null)
    {
        $pipeline = LeadPipeline::find($pipelineId);
        $category = $categoryId ? LeadCategory::find($categoryId) : null;
        abort_403(!canDataTableExport());
        $exportedData = new DealReportExport($year, $pipelineId, $categoryId);
        $pipelineName = strtolower(str_replace(' ', '-', $pipeline->name));
        $categoryName = $category ? strtolower(str_replace(' ', '-', $category->category_name)) : 'all-categories';
        $fileName = 'deal-report-' . $pipelineName . '-' . $categoryName . '-' . $year . '.xlsx';
        // Return the Excel file download response
        return $this->excel->download($exportedData, $fileName);
    }
}
