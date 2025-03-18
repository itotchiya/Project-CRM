<?php

namespace App\DataTables;

use App\Models\Contract;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\GlobalSetting;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class ContractsDataTable extends BaseDataTable
{

    private $editContractPermission;
    private $deleteContractPermission;
    private $addContractPermission;
    private $viewContractPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editContractPermission = user()->permission('edit_contract');
        $this->deleteContractPermission = user()->permission('delete_contract');
        $this->addContractPermission = user()->permission('add_contract');
        $this->viewContractPermission = user()->permission('view_contract');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatables, Contract::CUSTOM_FIELD_MODEL);

        return $datatables
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">

                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= ' <a href="' . route('contracts.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if (!in_array('client', user_roles()) && !$row->company_sign && user()->company_id == $row->company_id) {
                    $action .= '<a class="dropdown-item sign-modal" href="javascript:;" data-contract-id="' . $row->id . '">
                    <i class="fa fa-check mr-2"></i>
                    ' . trans('modules.estimates.companysignature') . '
                    </a>';
                }

                if (!$row->signature) {
                    $action .= '<a class="dropdown-item" href="' . url()->temporarySignedRoute('front.contract.show', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), [$row->hash]) . '" target="_blank"><i class="fa fa-link mr-2"></i>' . __('modules.proposal.publicLink') . '</a>';
                }

                if (in_array('clients', user_modules()) && ($this->addContractPermission == 'all' || $this->addContractPermission == 'added')) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('contracts.create') . '?id=' . $row->id . '">
                            <i class="fa fa-copy mr-2"></i>
                            ' . __('app.copy') . ' ' . __('app.menu.contract') . '
                        </a>';
                }

                if (in_array('clients', user_modules()) &&
                    ($this->editContractPermission == 'all'
                    || ($this->editContractPermission == 'added' && user()->id == $row->added_by)
                    || ($this->editContractPermission == 'owned' && user()->id == $row->client_id)
                    || ($this->editContractPermission == 'both' && (user()->id == $row->client_id || user()->id == $row->added_by)))
                ) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('contracts.edit', [$row->id]) . '">
                            <i class="fa fa-edit mr-2"></i>
                            ' . trans('app.edit') . '
                        </a>';
                }

                if (
                    $this->deleteContractPermission == 'all'
                    || ($this->deleteContractPermission == 'added' && user()->id == $row->added_by)
                    || ($this->deleteContractPermission == 'owned' && user()->id == $row->client_id)
                    || ($this->deleteContractPermission == 'both' && (user()->id == $row->client_id || user()->id == $row->added_by))
                ) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-contract-id="' . $row->id . '">
                            <i class="fa fa-trash mr-2"></i>
                            ' . trans('app.delete') . '
                        </a>';
                }

                $action .= '<a class="dropdown-item" href="' . route('contracts.download', $row->id) . '">
                                <i class="fa fa-download mr-2"></i>
                                ' . trans('app.download') . '
                            </a>';


                $action .= '</div>
                </div>
            </div>';

                return $action;
            })
            ->editColumn('project_name', function ($row) {
                if ($row->project_id != null) {
                    return '<a href="' . route('projects.show', $row->project_id) . '" class="text-darkest-grey">' . str($row->project->project_name)->limit(30) . '</a>';
                }

                return '--';
            })
            ->addColumn('contract_subject', function ($row) {
                return str($row->subject)->limit(50);
            })
            ->editColumn('subject', function ($row) {
                $signed = '';

                if ($row->signature) {
                    $signed = '<span class="badge badge-secondary"><i class="fa fa-signature"></i> ' . __('app.signed') . '</span>';
                }

                return '<div class="media align-items-center">
                        <div class="media-body">
                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('contracts.show', [$row->id]) . '">' . str($row->subject)->limit(40) . '</a></h5>
                    <p class="mb-0">' . $signed . '</p>
                    </div>
                  </div>';
            })
            ->editColumn('start_date', function ($row) {
                return $row->start_date->translatedFormat($this->company->date_format);
            })
            ->editColumn('end_date', function ($row) {
                if (is_null($row->end_date)) {
                    return '--';
                }

                return $row->end_date == null ? $row->end_date : $row->end_date->translatedFormat($this->company->date_format);
            })
            ->editColumn('amount', function ($row) {
                return currency_format($row->amount, $row->currency->id);
            })
            ->addColumn('client_name', function ($row) {
                if ($row->client) {
                    $client = $row->client;

                    return view('components.client', [
                        'user' => $client
                    ]);

                }

                return '--';

            })
            ->editColumn('client.name', function ($row) {
                return '<div class="media align-items-center">
                    <a href="' . route('clients.show', [$row->client_id]) . '">
                    <img src="' . $row->client->image_url . '" class="mr-2 taskEmployeeImg rounded-circle" alt="' . $row->client->name . '" title="' . $row->client->name . '"></a>
                    <div class="media-body">
                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('clients.show', [$row->client_id]) . '">' . $row->client->name_salutation . '</a></h5>
                    <p class="mb-0 f-13 text-dark-grey">' . $row->client->clientDetails->company_name . '</p>
                    </div>
                  </div>';
            })
            ->editColumn('signature', function ($row) {
                if ($row->signature) {
                    return __('app.signed');
                }
            })
            ->editColumn('contract_number', function ($row) {
                return '<a href="' . route('contracts.show', [$row->id]) . '" class="text-darkest-grey">' . $row->contract_number . '</a>';
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(array_merge(['project_name', 'action', 'client.name', 'check', 'subject', 'contract_number'], $customFieldColumns));
    }

    /**
     * @param Contract $model
     * @return \Illuminate\Database\Eloquent\Builder
     * @property-read \App\Models\Award $title
     */
    public function query(Contract $model)
    {
        $request = $this->request();
        $startDate = null;
        $endDate = null;

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
        }

        $model = $model->with(
            [
                'company',
                'project' => function ($q) {
                    $q->withTrashed();
                    $q->select('id', 'project_name', 'project_short_code', 'client_id');
                },
                'client.clientDetails.company:id,company_name',
                'currency:id,currency_symbol,currency_code',
                'project.client',
                'project.client.clientDetails.company',
                'client',
                'project.clientdetails'
            ]
        )->with('contractType', 'client', 'signature', 'client.clientDetails')
            ->join('users', 'users.id', '=', 'contracts.client_id')
            ->join('client_details', 'users.id', '=', 'client_details.user_id')
            ->select('contracts.*');

        if ($startDate !== null && $endDate !== null) {
            $model->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween(DB::raw('DATE(contracts.`end_date`)'), [$startDate, $endDate]);

                $q->orWhereBetween(DB::raw('DATE(contracts.`start_date`)'), [$startDate, $endDate]);
            });
        }

        if ($request->client != 'all' && !is_null($request->client)) {
            $model = $model->where('contracts.client_id', '=', $request->client);
        }

        if ($request->contract_type != 'all' && !is_null($request->contract_type)) {
            $model = $model->where('contracts.contract_type_id', '=', $request->contract_type);
        }

        if (request('signed') == 'yes') {
            $model = $model->has('signature');
        }

        if ($request->searchText != '') {
            $model = $model->where(function ($query) {
                $query->where('contracts.subject', 'like', '%' . request('searchText') . '%')
                    ->orWhere('contracts.amount', 'like', '%' . request('searchText') . '%')
                    ->orWhere('client_details.company_name', 'like', '%' . request('searchText') . '%');
            })
                ->orWhere(function ($query) {
                    $query->whereHas('project', function ($q) {
                        $q->where('project_name', 'like', '%' . request('searchText') . '%')
                            ->orWhere('project_short_code', 'like', '%' . request('searchText') . '%'); // project short code
                    });
                });
        }

        if ($this->viewContractPermission == 'added') {
            $model = $model->where('contracts.added_by', '=', user()->id);
        }

        if ($this->viewContractPermission == 'owned') {
            $model = $model->where('contracts.client_id', '=', user()->id);
        }

        if ($this->viewContractPermission == 'both') {
            $model = $model->where(function ($query) {
                $query->where('contracts.added_by', '=', user()->id)
                    ->orWhere('contracts.client_id', '=', user()->id);
            });
        }

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     * @property-read \App\Models\Award $title
     */
    public function html()
    {
        $dataTable = $this->setBuilder('contracts-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["contracts-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                  //
                }',
                /* 'buttons'      => ['excel'] */
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
        $data = [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
                'visible' => !in_array('client', user_roles())
            ],
            __('modules.contracts.contractNumber') => ['data' => 'contract_number', 'name' => 'contract_number', 'title' => '#'],
            __('app.subject') => ['data' => 'subject', 'name' => 'subject', 'exportable' => false, 'title' => __('app.subject')],
            __('app.menu.contract') . ' ' . __('app.subject') => ['data' => 'contract_subject', 'name' => 'subject', 'visible' => false, 'title' => __('app.menu.contract')],
            __('app.client') => ['data' => 'client.name', 'name' => 'client.name', 'exportable' => false, 'title' => __('app.client'), 'visible' => !in_array('client', user_roles())],
            __('app.customers') => ['data' => 'client_name', 'name' => 'client.name', 'visible' => false, 'title' => __('app.customers')],
            __('app.project') => ['data' => 'project_name', 'name' => 'project.project_name', 'title' => __('app.project')],
            __('app.amount') => ['data' => 'amount', 'name' => 'amount', 'title' => __('app.amount')],
            __('app.startDate') => ['data' => 'start_date', 'name' => 'start_date', 'title' => __('app.startDate')],
            __('app.endDate') => ['data' => 'end_date', 'name' => 'end_date', 'title' => __('app.endDate')],
            __('app.signature') => ['data' => 'signature', 'name' => 'signature', 'visible' => false, 'title' => __('app.signature')]
        ];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

        return array_merge($data, CustomFieldGroup::customFieldsDataMerge(new Contract()), $action);
    }

}
