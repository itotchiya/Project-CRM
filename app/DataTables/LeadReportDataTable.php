<?php

namespace App\DataTables;

use App\Models\LeadAgent;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;

class LeadReportDataTable extends BaseDataTable
{

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('employee_name', function ($row) {
                return $row->agent_name;
            })
            ->addColumn('total_deals', function ($row) {
                return $row->count_total_leads;
            })
            ->addColumn('won_deals', function ($row) {
                return $row->count_won_leads;
            })
            ->addColumn('lost_deals', function ($row) {
                return $row->total_lost_deals;
            })
            ->addColumn('total_amount', function ($row) {
                return currency_format($row->total_value, company()->currency_id);
            })
            ->addColumn('converted_amount', function ($row) {
                return $row->total_converted_value ? currency_format($row->total_converted_value, company()->currency_id) : 0;
            })
            ->addColumn('total_follow_up', function ($row) {
                return $row->count_total_follow_up;
            })
            ->addColumn('total_pending_follow_up', function ($row) {
                return $row->count_total_pending_follow_up;
            })
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['total_leads', 'action', 'converted_lead', 'total_amount', 'converted_amount']);
    }

    /**
     * @param LeadAgent $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(LeadAgent $model)
    {
        $request = $this->request();

        $agent = $request->agent;

        $model = $model
            ->select(
            'deals.id',
            'users.name as agent_name',
            DB::raw("( select count('leadTotal.agent_id') from deals as leadTotal where leadTotal.agent_id = lead_agents.id) as count_total_leads"),
            DB::raw("( select count('wonDeals.id') from deals as wonDeals INNER JOIN pipeline_stages on wonDeals.pipeline_stage_id = pipeline_stages.id where wonDeals.agent_id = lead_agents.id and pipeline_stages.slug = 'win')  as count_won_leads"),
            DB::raw('( select sum(totalAmount.value) from deals as totalAmount where totalAmount.agent_id = lead_agents.id) as total_value'),
            DB::raw("( select sum(convertedAmount.value) from deals as convertedAmount INNER JOIN pipeline_stages as stage on convertedAmount.pipeline_stage_id = stage.id where convertedAmount.agent_id = lead_agents.id and stage.slug = 'win')  as total_converted_value"),
            DB::raw("( select count(lostDeals.id) from deals as lostDeals INNER JOIN pipeline_stages as stageLost on lostDeals.pipeline_stage_id = stageLost.id where lostDeals.agent_id = lead_agents.id  and stageLost.slug = 'lost')  as total_lost_deals"),
            DB::raw('( select count("total_followup.deal_id") from lead_follow_up as total_followup INNER JOIN deals as lead_totals ON lead_totals.id=total_followup.deal_id where total_followup.deal_id = lead_totals.id and lead_totals.agent_id = lead_agents.id) as count_total_follow_up'),
            DB::raw("( select count('total_pending_followup.id') from lead_follow_up as total_pending_followup INNER JOIN deals as lead_status_totals ON lead_status_totals.id=total_pending_followup.deal_id where total_pending_followup.deal_id = lead_status_totals.id and lead_status_totals.agent_id = lead_agents.id and total_pending_followup.status = 'pending') as count_total_pending_follow_up"),
        )
            ->leftJoin('deals', 'deals.agent_id', 'lead_agents.id')
            ->join('users', 'users.id', 'lead_agents.user_id')
            ->leftjoin('lead_follow_up', 'lead_follow_up.deal_id', 'deals.id');

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);

            if (!is_null($startDate)) {
                $model = $model->where(DB::raw('DATE(deals.`created_at`)'), '>=', $startDate);
            }
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);

            if (!is_null($endDate)) {
                $model = $model->where(function ($query) use ($endDate) {
                    $query->where(DB::raw('DATE(deals.`created_at`)'), '<=', $endDate);
                });
            }
        }
     

        if (!is_null($agent) && $agent !== 'all') {
            $model->where('users.id', $agent);
        }

        $model->groupBy('users.id');

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('lead-report-table', 5)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["lead-report-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                //
                $(".select-picker").selectpicker();
                }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        }

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
             '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'title' => __('app.id')],
            __('modules.deal.dealAgent') => ['data' => 'employee_name', 'name' => 'users.name', 'title' => __('modules.deal.dealAgent')],
            __('modules.deal.totalDeals') => ['data' => 'total_deals', 'name' => 'count_total_leads', 'title' => __('modules.deal.totalDeals')],
            __('modules.deal.wonDeals') => ['data' => 'won_deals', 'name' => 'count_won_leads', 'title' => __('modules.deal.wonDeals')],
            __('modules.deal.lostDeals') => ['data' => 'lost_deals', 'name' => 'total_lost_deals', 'title' => __('modules.deal.lostDeals')],
            __('app.totalAmount') => ['data' => 'total_amount', 'name' => 'total_value', 'title' => __('app.totalAmount')],
            __('modules.lead.convertedAmount') => ['data' => 'converted_amount', 'name' => 'total_converted_value', 'title' => __('modules.lead.convertedAmount')],
            __('app.total').' '.__('app.followUp') => ['data' => 'total_follow_up', 'name' => 'count_total_follow_up', 'title' => __('app.total').' '.__('app.followUp')],
            __('app.total').' '.__('app.pending').' '.__('app.followUp') => ['data' => 'total_pending_follow_up', 'name' => 'count_total_pending_follow_up', 'title' => __('app.total').' '.__('app.pending').' '.__('app.followUp')],
        ];
    }

    public function pdf()
    {
        set_time_limit(0);

        if ('snappy' == config('datatables-buttons.pdf_generator', 'snappy')) {
            return $this->snappyPdf();
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('datatables::print', ['data' => $this->getDataForPrint()]);

        return $pdf->download($this->getFilename() . '.pdf');
    }

}
