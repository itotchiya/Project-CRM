<?php

namespace App\DataTables;

use App\Models\PurposeConsent;
use Carbon\Carbon;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ConsentDataTable extends BaseDataTable
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
            ->addIndexColumn()
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view mr-1">
                        <a href="javascript:;" data-consent-id="' . $row->id . '"
                            class="edit-consent task_view_more d-flex align-items-center justify-content-center">
                            <i class="fa fa-edit icons mr-2"></i> ' . __('app.edit') . '
                        </a>
                    </div>';

                $action .= '<div class="task_view">
                        <a href="javascript:;" data-consent-id="' . $row->id . '"
                            class="delete-table-row task_view_more d-flex align-items-center justify-content-center">
                            <i class="fa fa-trash icons mr-2"></i> ' . __('app.delete') . '
                        </a>
                    </div>';

                return $action;
            })
            ->editColumn('created_at', fn($row) => Carbon::parse($row->created_at)->translatedFormat($this->company->date_format))
            ->rawColumns(['status', 'action', 'check']);
    }

    /**
     * @param PurposeConsent $model
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(PurposeConsent $model)
    {
        return $model->select('id', 'name', 'description', 'created_at');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('consent-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["consent-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {

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
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'exportable' => false, 'title' => __('app.id')],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.name') => ['data' => 'name', 'name' => 'name', 'title' => __('app.name')],
            __('app.description') => ['data' => 'description', 'name' => 'description', 'title' => __('app.description')],
            __('app.createdOn') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('app.createdOn')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(210)
                ->addClass('text-right')
        ];
    }

}
