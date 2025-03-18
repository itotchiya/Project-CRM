<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Deal;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class DealReportExport implements FromCollection, WithMapping, WithHeadings, WithEvents
{
    public $year;
    public $pipeline;
    public $category;


    public function __construct($year, $pipeline, $category)
    {

        $this->year = $year;
        $this->pipeline = $pipeline;
        $this->category = $category;

    }

    public function collection()
    {
    // Fetch data from the database
        $query = DB::table('deals')
        ->select(
            DB::raw('MONTH(close_date) as month'),
            DB::raw('COUNT(*) as deals_closed'),
            DB::raw('SUM(value) as total_deal_amount'),
            DB::raw('AVG(value) as average_deal_amount'),
            DB::raw("(SELECT COUNT(wonDeals.id)
                      FROM deals as wonDeals
                      INNER JOIN pipeline_stages on wonDeals.pipeline_stage_id = pipeline_stages.id
                      WHERE wonDeals.close_date IS NOT NULL
                      AND pipeline_stages.slug = 'win'
                      AND wonDeals.lead_pipeline_id = $this->pipeline
                      AND MONTH(wonDeals.close_date) = MONTH(deals.close_date)
                      AND YEAR(wonDeals.close_date) = YEAR(deals.close_date)) as won_deals"),
            DB::raw("(SELECT SUM(wonDealsAmount.value)
                      FROM deals as wonDealsAmount
                      INNER JOIN pipeline_stages as pipelineStag on wonDealsAmount.pipeline_stage_id = pipelineStag.id
                      WHERE wonDealsAmount.close_date IS NOT NULL
                      AND pipelineStag.slug = 'win'
                      AND wonDealsAmount.lead_pipeline_id = $this->pipeline
                      AND MONTH(wonDealsAmount.close_date) = MONTH(deals.close_date)
                      AND YEAR(wonDealsAmount.close_date) = YEAR(deals.close_date)) as deals_won_amount"),
            DB::raw("(SELECT COUNT(lostDeals.id)
                      FROM deals as lostDeals
                      INNER JOIN pipeline_stages on lostDeals.pipeline_stage_id = pipeline_stages.id
                      WHERE lostDeals.close_date IS NOT NULL
                      AND pipeline_stages.slug = 'lost'
                      AND lostDeals.lead_pipeline_id = $this->pipeline
                      AND MONTH(lostDeals.close_date) = MONTH(deals.close_date)
                      AND YEAR(lostDeals.close_date) = YEAR(deals.close_date)) as lost_deals"),
            DB::raw("(SELECT SUM(lostDeal_amount.value)
                      FROM deals as lostDeal_amount
                      INNER JOIN pipeline_stages on lostDeal_amount.pipeline_stage_id = pipeline_stages.id
                      WHERE lostDeal_amount.close_date IS NOT NULL
                      AND pipeline_stages.slug = 'lost'
                      AND lostDeal_amount.lead_pipeline_id = $this->pipeline
                      AND MONTH(lostDeal_amount.close_date) = MONTH(deals.close_date)
                      AND YEAR(lostDeal_amount.close_date) = YEAR(deals.close_date)) as deals_lost_amount"),
            DB::raw("(SELECT COUNT(other_stages.id)
                      FROM deals as other_stages
                      INNER JOIN pipeline_stages on other_stages.pipeline_stage_id = pipeline_stages.id
                      WHERE other_stages.close_date IS NOT NULL
                      AND pipeline_stages.slug != 'lost'
                      AND pipeline_stages.slug != 'win'
                      AND other_stages.lead_pipeline_id = $this->pipeline
                      AND MONTH(other_stages.close_date) = MONTH(deals.close_date)
                      AND YEAR(other_stages.close_date) = YEAR(deals.close_date)) as deals_other_stages"),
            DB::raw("(SELECT SUM(other_stages_value.value)
                      FROM deals as other_stages_value
                      INNER JOIN pipeline_stages on other_stages_value.pipeline_stage_id = pipeline_stages.id
                      WHERE other_stages_value.close_date IS NOT NULL
                      AND pipeline_stages.slug != 'lost'
                      AND pipeline_stages.slug != 'win'
                      AND other_stages_value.lead_pipeline_id = $this->pipeline
                      AND MONTH(other_stages_value.close_date) = MONTH(deals.close_date)
                      AND YEAR(other_stages_value.close_date) = YEAR(deals.close_date)) as deals_other_stages_value")
        )
        ->whereYear('close_date', $this->year)
        ->where('lead_pipeline_id', $this->pipeline)
        ->whereNotNull('close_date');
        
        // Conditionally add the category filter
        if ($this->category !== "null") {
            $query->where('category_id', $this->category);
        }
        $deals = $query->groupBy(DB::raw('MONTH(close_date)'))->get();

        $deals = collect($deals)->map(function ($item) {
        return (object) $item;
        });

        $months = collect(range(1, 12))->map(function ($month) use ($deals) {
        $deal = $deals->firstWhere('month', $month);
        return [
            'month' => $month,
            'deals_closed' => $deal ? $deal->deals_closed : 0,
            'total_deal_amount' => $deal ? round($deal->total_deal_amount, 2) : 0,
            'average_deal_amount' => $deal ? round($deal->average_deal_amount, 2) : 0,
            'won_deals' => $deal ? $deal->won_deals : 0,
            'deals_won_amount' => $deal ? round($deal->deals_won_amount, 2) : 0,
            'lost_deals' => $deal ? $deal->lost_deals : 0,
            'deals_lost_amount' => $deal ? round($deal->deals_lost_amount, 2) : 0,
            'deals_other_stages' => $deal ? $deal->deals_other_stages : 0,
            'deals_other_stages_value' => $deal ? round($deal->deals_other_stages_value, 2) : 0,
            ];
        });
        return $months;
    }

    public function map($deal): array
    {
        if (is_array($deal)) {
            $deal = (object)$deal;
        }
        $currencySymbol = '$';

        return  [
            Carbon::createFromDate(null, $deal->month, 1)->format('F'),
            $deal->deals_closed != 0 ? $deal->deals_closed : '0',
            $deal->total_deal_amount != 0 ? $currencySymbol.round($deal->total_deal_amount, 2) : '0',
            $deal->average_deal_amount != 0 ? $currencySymbol.round($deal->average_deal_amount, 2) : '0',
            $deal->won_deals != 0 ? $deal->won_deals : '0',
            $deal->deals_won_amount != 0 ? $currencySymbol.round($deal->deals_won_amount, 2) : '0',
            $deal->lost_deals != 0 ? $deal->lost_deals : '0',
            $deal->deals_lost_amount != 0 ? $currencySymbol.round($deal->deals_lost_amount, 2) : '0',
            $deal->deals_other_stages != 0 ? $deal->deals_other_stages : '0',
            $deal->deals_other_stages_value != 0 ? $currencySymbol.round($deal->deals_other_stages_value, 2) : '0',
        ];


    }


    public function headings(): array
    {
        return [
            __('app.month'),
            __('modules.deal.dealsToBeClosed'),
            __('modules.deal.totalDealAmount'),
            __('modules.deal.averageDealValue'),
            __('modules.deal.wonDeals'),
            __('modules.deal.dealsWonValue'),
            __('modules.deal.lostDeals'),
            __('modules.deal.dealsLostValue'),
            __('modules.deal.otherDealStages'),
            __('modules.deal.otherDealStagesValue'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:J1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                // Set format for each cell in the range A2:J<last_row>
                $lastRow = $event->sheet->getHighestRow();
                $range = 'A2:J' . $lastRow;
                $event->sheet->getStyle($range)->getNumberFormat()->setFormatCode('0');
            },
        ];
    }
}
