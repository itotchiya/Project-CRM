<?php

namespace App\DataTables;

use App\Models\PurposeConsentUser;
use Carbon\Carbon;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ClientGDPRDataTable extends BaseDataTable
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
            ->editColumn('status', function ($row) {
                return match ($row->status) {
                    'agree' => __('modules.gdpr.optIn'),
                    'disagree' => __('modules.gdpr.optOut'),
                    default => ''
                };
            })
            ->editColumn('created_at', fn($row) => Carbon::parse($row->created_at)->translatedFormat($this->company->date_format))
            ->editColumn('action', fn($row) => $row->status)
            ->rawColumns(['status']);
    }

    /**
     * @param PurposeConsentUser $model
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(PurposeConsentUser $model)
    {
        $request = $this->request();

        return $model->select('purpose_consent.name', 'purpose_consent_users.created_at', 'purpose_consent_users.status', 'purpose_consent_users.ip', 'users.name as username', 'purpose_consent_users.additional_description')
            ->join('purpose_consent', 'purpose_consent.id', '=', 'purpose_consent_users.purpose_consent_id')
            ->leftJoin('users', 'purpose_consent_users.updated_by_id', '=', 'users.id')
            ->where('purpose_consent_users.client_id', $request->clientID);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('client-gdpr-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["client-gdpr-table"].buttons().container()
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
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('modules.gdpr.purpose') => ['data' => 'name', 'name' => 'purpose_consent.name', 'title' => __('modules.gdpr.purpose')],
            __('app.date') => ['data' => 'created_at', 'name' => 'purpose_consent.created_at', 'title' => __('app.date')],
            __('modules.gdpr.ipAddress') => ['data' => 'ip', 'name' => 'purpose_consent.ip', 'title' => __('modules.gdpr.ipAddress')],
            __('modules.gdpr.additionalDescription') => ['data' => 'additional_description', 'name' => 'purpose_consent_users.additional_description', 'title' => __('modules.gdpr.additionalDescription')],
            Column::computed('action', __('app.action'))
                ->exportable(true)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
                ->addClass('text-right pr-20')
        ];
    }

}
