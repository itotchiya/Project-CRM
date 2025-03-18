<?php

namespace App\DataTables;

use App\Models\TaskLabelList;
use Yajra\DataTables\Html\Column;

class LabelDataTable extends BaseDataTable
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
            ->addColumn('action', function ($row) {
                $action = '<div class="btn-group dropdown m-r-10">
                <button aria-expanded="false" data-toggle="dropdown" class="btn btn-default dropdown-toggle waves-effect waves-light" type="button"><i class="fa fa-gears "></i></button>
                <ul role="menu" class="dropdown-menu pull-right">
                  <li><a href="' . route('admin.task-label.edit', [$row->id]) . '"><i class="fa fa-pencil" aria-hidden="true"></i> ' . trans('app.edit') . '</a></li>
                  <li><a href="javascript:;"   data-contract-id="' . $row->id . '"  class="sa-params"><i class="fa fa-times" aria-hidden="true"></i> ' . trans('app.delete') . '</a></li>';

                $action .= '</ul> </div>';

                return $action;
            })
            ->editColumn('label_name', function ($row) {

                if ($row->color) {
                    return '<label class="badge" style="background: ' . $row->color . ';">' . $row->label_name . '</label>';
                }

                return '<label class="badge"  style="background:#3b0ae1;">' . $row->label_name . '</label>';
            })
            ->editColumn('description', function ($row) {
                if ($row->description) {
                    return $row->description;
                }

                return '--';
            })
            ->addIndexColumn()
            ->rawColumns(['action', 'label_name', 'color']);
    }

    /**
     * @param TaskLabelList $model
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(TaskLabelList $model)
    {
        return $model->select('id', 'label_name', 'color', 'description');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('taskLabelList-table', 0)
            ->dom("<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>")
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["taskLabelList-table"].buttons().container()
                    .appendTo( ".bg-title .text-right")
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
            '#' => ['data' => 'id', 'name' => 'id', 'visible' => true],
            __('app.labelName') => ['data' => 'label_name', 'name' => 'label_name', 'title' => __('app.labelName')],
            __('app.description') => ['data' => 'description', 'name' => 'description', 'title' => __('app.description')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
                ->addClass('text-center')
        ];
    }

}
