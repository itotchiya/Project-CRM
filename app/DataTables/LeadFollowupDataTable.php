<?php

namespace App\DataTables;

use App\Models\DealFollowUp;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class LeadFollowupDataTable extends BaseDataTable
{
    private $editFollowupPermission;
    private $deleteFollowupermission;
    private $viewFollowupPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editFollowupPermission = user()->permission('edit_lead_follow_up'); // all added none
        $this->deleteFollowupermission = user()->permission('delete_lead_follow_up'); // all added none
        $this->viewFollowupPermission = user()->permission('view_lead_follow_up'); // all added none
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
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">
                <div class="dropdown">
                <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                    id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="icon-options-vertical icons"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if ($this->editFollowupPermission == 'all' || ($this->editFollowupPermission == 'added' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item edit-table-row-lead" href="javascript:;" data-followup-id="' . $row->id . '">
                    <i class="fa fa-edit mr-2"></i>
                    ' . trans('app.edit') . '
                    </a>';
                }

                if ($this->deleteFollowupermission == 'all' || ($this->deleteFollowupermission == 'added' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item delete-table-row-lead" href="javascript:;" data-followup-id="' . $row->id . '">
                    <i class="fa fa-trash mr-2"></i>
                    ' . trans('app.delete') . '
                    </a>';
                }
                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->addColumn('status', function ($row) {

                $status = '';

                $status .= '<select class="form-control statusChange status" data-followup-id = " ' . $row->id . '">';

                $status .= '<option value="pending"';

                if ($row->status == 'pending') {
                    $status .= 'selected';
                }

                $status .= ' data-content="<i class=\'fa fa-circle mr-2 text-warning\'></i>'
                    . trans('app.pending') . ' " > ' . trans('app.pending') . '</option>';

                $status .= '<option value="canceled"';

                if ($row->status == 'canceled') {
                    $status .= 'selected';
                }

                $status .= ' data-content="<i class=\'fa fa-circle mr-2 text-red\'></i>'
                    . trans('app.canceled') . ' " > ' . trans('app.canceled') . '</option>';

                $status .= '<option value="completed"';

                if ($row->status == 'completed') {
                    $status .= 'selected';
                }

                $status .= ' data-content="<i class=\'fa fa-circle mr-2 text-dark-green\'></i>' . trans('app.completed') . '" > ' . trans('app.completed') . '</option>';

                $status .= '</select>';

                return $status;
            })
            ->addColumn('statusChange', fn($row) => $row->status)
            ->addColumn('created_at', fn($row) => $row->created_at->timezone(company()->timezone)->format(company()->date_format . ' ' . company()->time_format))
            ->addColumn('next_follow_up', fn($row) => $row->next_follow_up_date->format(company()->date_format . ' ' . company()->time_format))
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param LeadFollowup $model
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function query(DealFollowup $model)
    {
        $lead = $model->newQuery();

        if (request()->has('leadId') && request()->leadId != '') {
            $lead = $lead->where('deal_id', request()->leadId);
        }

        if ($this->viewFollowupPermission == 'added') {
            $lead->where('added_by', user()->id);
        }

        return $lead;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('leadfollowup-table')
            ->parameters([
                'initComplete' => 'function () {
                        window.LaravelDataTables["leadfollowup-table"].buttons().container()
                        .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                $("body").tooltip({
                    selector: \'[data-toggle="tooltip"]\'
                });
                $(".statusChange").selectpicker();
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
            __('app.createdOn') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('app.createdOn')],
            __('modules.lead.nextFollowUp') => ['data' => 'next_follow_up', 'name' => 'next_follow_up', 'title' => __('modules.lead.nextFollowUp')],
            __('app.remark') => ['data' => 'remark', 'name' => 'remark', 'title' => __('app.remark')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'exportable' => false, 'title' => __('app.status')],
            __('app.Changestatus') => ['data' => 'statusChange', 'name' => 'statusChange', 'visible' => false, 'title' => __('app.Changestatus')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

    }

}
