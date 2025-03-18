<?php

namespace App\DataTables;

use App\Models\ExpensesCategory;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;

class ExpenseCategoryReportDataTable extends BaseDataTable
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
            ->addColumn('category_name', function ($row) {
                return html_entity_decode($row->category_name);
            })
            ->addColumn('converted_price', function ($row) {
                return currency_format($row->expenses->sum('default_currency_price'), company()->currency_id);
            })
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->addIndexColumn();
    }

    /**
     * Get query source of dataTable.
     *
     * @param ExpensesCategory $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ExpensesCategory $model)
    {
        $request = $this->request();

        if ($request->categoryID != 'all' && !is_null($request->categoryID)) {
            $model = $model->where('id', '=', $request->categoryID);
        }

        $model = $model->with(['expenses' => function ($query) use ($request) {
            if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
                $startDate = companyToDateString($request->startDate);
                $query->where(DB::raw('DATE(`purchase_date`)'), '>=', $startDate);
            }

            if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
                $endDate = companyToDateString($request->endDate);
                $query->where(DB::raw('DATE(`purchase_date`)'), '<=', $endDate);
            }

            $query->where('status', 'approved');
        }]);

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('expense-category-report-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["expense-category-report-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("#expense-category-report-table .select-picker").selectpicker();
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
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => true, 'title' => '#'],
            __('app.category') => ['data' => 'category_name', 'name' => 'category_name', 'title' => __('app.category')],
            __('app.total').' '.__('app.price') => ['data' => 'converted_price', 'name' => 'converted_price', 'orderable' => false, 'title' => __('app.total').' '.__('app.price')],
        ];
    }

}
