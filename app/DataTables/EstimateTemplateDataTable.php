<?php

namespace App\DataTables;

use App\Models\EstimateTemplate;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Carbon;

class EstimateTemplateDataTable extends BaseDataTable
{
    private $editEstimatePermission;
    private $deleteEstimatePermission;

    public function __construct()
    {
        parent::__construct();
        $this->editEstimatePermission = user()->permission('edit_estimates');
        $this->deleteEstimatePermission = user()->permission('delete_estimates');
    }
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
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">
                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= ' <a href="' . route('estimate-template.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';


                    $action .= '<a class="dropdown-item" href="' . route('estimates.create') . '?template=' . $row->id . '">
                        <i class="fa fa-plus mr-2"></i>
                        ' . trans('app.create') . ' ' . trans('app.menu.estimate') . '
                    </a>';


                    if (
                        $this->editEstimatePermission == 'all'
                        || ($this->editEstimatePermission == 'added' && $row->added_by == user()->id)
                        || ($this->editEstimatePermission == 'owned' && $row->added_by != user()->id) || $this->editEstimatePermission == 'both'
                    ){

                        $action .= '<a class="dropdown-item" href="' . route('estimate-template.edit', [$row->id]) . '">
                            <i class="fa fa-edit mr-2"></i>
                            ' . trans('app.edit') . '
                            </a>';
                    }

                    if (
                        $this->deleteEstimatePermission == 'all'
                        || ($this->deleteEstimatePermission == 'added' && $row->added_by == user()->id)
                        || ($this->deleteEstimatePermission == 'owned' && $row->added_by != user()->id) || $this->deleteEstimatePermission == 'both'
                    ) {
                        $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-user-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                    }


                $action .= '</div>
                </div>
            </div>';

                return $action;
            })
            ->addColumn('name', function ($row) {
                return $row->name;
            })
            ->editColumn('total', function ($row) {
                return currency_format($row->total, $row->currencyId);
            })
            ->editColumn(
                'created_at',
                function ($row) {
                    return Carbon::parse($row->created_at)->translatedFormat($this->company->date_format);
                }
            )
            ->rawColumns(['name', 'action', 'client_name'])
            ->removeColumn('currency_symbol');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(EstimateTemplate $model)
    {
        $request = $this->request();
        $model = $model->select('estimate_templates.id', 'estimate_templates.name', 'estimate_templates.hash', 'total', 'currencies.currency_symbol', 'currencies.id as currencyId', 'estimate_templates.added_by', 'estimate_templates.created_at')
            ->join('currencies', 'currencies.id', '=', 'estimate_templates.currency_id');


            $model->where(function ($query) {
                $query->where('estimate_templates.name', 'like', '%' . request('searchText') . '%');
            });


        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('estimate-template-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["estimate-template-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
            ]);

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
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id')],
            __('app.name') => ['data' => 'name', 'name' => 'name', 'title' => __('app.name')],
            __('modules.invoices.total') => ['data' => 'total', 'name' => 'total', 'title' => __('modules.invoices.total')],
            __('app.date') => ['data' => 'created_at', 'name' => 'estimate_templates.created_at', 'title' => __('app.date')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
