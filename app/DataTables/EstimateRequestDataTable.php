<?php

namespace App\DataTables;

use App\Models\EstimateRequest;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Str;

class EstimateRequestDataTable extends BaseDataTable
{
    private $addEstimatePermission;
    private $editEstimateRequestPermission;
    private $deleteEstimateRequestPermission;
    private $rejectEstimateRequestPermission;
    private $viewEstimateRequestPermission;

    public function __construct()
    {
        parent::__construct();
        $this->addEstimatePermission = user()->permission('add_estimates');
        $this->editEstimateRequestPermission = user()->permission('edit_estimate_request');
        $this->deleteEstimateRequestPermission = user()->permission('delete_estimate_request');
        $this->rejectEstimateRequestPermission = user()->permission('reject_estimate_request');
        $this->viewEstimateRequestPermission = user()->permission('view_estimate_request');
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
            ->editColumn('estimate_request_number', function ($row) {
                return '<a href="' . route('estimate-request.show', $row->id) . '" class="text-darkest-grey openRightModal">' . $row->estimate_request_number . '</a>';
            })
            ->editColumn('client', function ($row) {
                return '<div class="media align-items-center">
                    <a href="' . route('clients.show', [$row->client_id]) . '">
                    <img src="' . $row->client->image_url . '" class="mr-2 taskEmployeeImg rounded-circle" alt="' . $row->client->name . '" title="' . $row->client->name . '"></a>
                    <div class="media-body">
                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('clients.show', [$row->client_id]) . '">' . $row->client->name_salutation . '</a></h5>
                    <p class="mb-0 f-13 text-dark-grey">' . $row->client->clientDetails?->company_name . '</p>
                    </div>
                  </div>';
            })
            ->editColumn('estimated_budget', function ($row) {
                return currency_format($row->estimated_budget, $row->currency_id);
            })
            ->editColumn('project', function ($row) {
                if ($row->project_id) {
                    return '<div class="media align-items-center">
                                <div class="media-body">
                            <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('projects.show', [$row->project_id]) . '">' . $row->project->project_name . '</a></h5>
                            </div>
                        </div>';
                }

                return '--';
            })
            ->editColumn('estimate_id', function ($row) {
                if ($row->estimate) {
                    return '<a class="text-darkest-grey" href="' . route('estimates.show', [$row->estimate->id]) . '">' . $row->estimate->estimate_number . '</a>';
                }
                else {
                    return '--';
                }
            })
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">

                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                    $action .= '<a href="' . route('estimate-request.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($row->status == 'pending'){
                    if (
                        $this->editEstimateRequestPermission == 'all'
                        || (($this->editEstimateRequestPermission == 'added') && $row->added_by == user()->id)
                        || (($this->editEstimateRequestPermission == 'owned') && $row->client_id == user()->id)
                        || (($this->editEstimateRequestPermission == 'both') && ($row->client_id == user()->id || $row->added_by == user()->id))
                    ) {
                        $action .= '<a class="dropdown-item openRightModal" href="' . route('estimate-request.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>' . trans('app.edit') . ' </a>';
                    }

                    if ($this->rejectEstimateRequestPermission == 'all') {
                        $action .= '<a class="dropdown-item change-status" href="javascript:;" data-estimate-request-id="' . $row->id . '">
                            <i class="fa fa-times mr-2"></i>' . trans('app.reject') . ' </a>';
                    }
                }

                if ($row->status != 'accepted'){
                    if ($this->addEstimatePermission == 'all' || $this->addEstimatePermission == 'added') {
                        $action .= '<a class="dropdown-item" href="' . route('estimates.create') . '?estimate-request=' . $row->id . '">
                            <i class="fa fa-plus mr-2"></i>
                            ' . trans('app.create') . ' ' . trans('app.menu.estimate') . '
                        </a>';
                    }
                }

                if (
                    $this->deleteEstimateRequestPermission == 'all'
                    || (($this->deleteEstimateRequestPermission == 'added') && $row->added_by == user()->id)
                    || (($this->deleteEstimateRequestPermission == 'owned') && $row->client_id == user()->id)
                    || (($this->deleteEstimateRequestPermission == 'both') && ($row->client_id == user()->id || $row->added_by == user()->id))
                ) {
                    if (!(in_array('client', user_roles()) && $row->status == 'accepted')){
                        $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-toggle="tooltip"  data-estimate-request-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>' . trans('app.delete') . '</a>';
                    }
                }

                $action .= '</div>
                </div>
            </div>';

                return $action;
            })
            ->addColumn('status1', function ($row) {

                $select = '';

                if ($row->status == 'pending' || $row->status == 'in process') {
                    $select .= '<i class="fa fa-circle mr-1 text-yellow f-10"></i>' . __('app.pending') . '</label>';
                }
                elseif ($row->status == 'rejected') {
                    $select .= '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.' . $row->status) . '</label>';
                }
                else {
                    $select .= '<i class="fa fa-circle mr-1 text-dark-green f-10"></i>' . __('app.' . $row->status) . '</label>';
                }

                return $select;
            })
            ->addColumn('status_name', function ($row) {
                return $row->status;
            })
            ->addColumn('early_requirement', function ($row) {
                return $row->early_requirement ?? '--';
            })
            ->rawColumns(['action', 'status1', 'client', 'project', 'estimate_id', 'estimate_request_number']);
    }

    /**
     * Get query source of dataTable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(EstimateRequest $model)
    {
        $searchText = request('searchText');
        $model = $model->select('estimate_requests.*')
            ->leftJoin('estimates', 'estimates.id', '=', 'estimate_requests.estimate_id')
            ->leftJoin('users', 'users.id', '=', 'estimate_requests.client_id')
            ->leftJoin('currencies', 'currencies.id', '=', 'estimate_requests.currency_id')
            ->leftJoin('projects', 'projects.id', '=', 'estimate_requests.project_id')
            ->leftJoin('client_details', 'client_details.user_id', '=', 'users.id')
            ->withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
            ->where(function ($query) use ($searchText) {
                $query->where('users.name', 'like', '%' . $searchText . '%')
                    ->orWhere('users.email', 'like', '%' . $searchText . '%');
            });

        if (request()->has('client') && request()->client != 'all') {
            $model = $model->whereHas('client', function ($query) {
                $query->where('id', request()->client);
            });
        }

        if (in_array('client', user_roles())) {
            $model = $model->where('estimate_requests.client_id', user()->id);
        }

        if (request()->has('status') && request()->status != 'all') {
            if (request()->status == 'pending') {
                $model = $model->whereIn('estimate_requests.status', ['pending', 'in process']);
            }
            else {
                $model = $model->where('estimate_requests.status', request()->status);
            }
        }

        if ($this->viewEstimateRequestPermission == 'added') {
            $model->where('estimate_requests.added_by', user()->id);
        }

        if ($this->viewEstimateRequestPermission == 'both') {
            $model->where(function ($query) {
                $query->where('estimate_requests.added_by', user()->id)
                    ->orWhere('estimate_requests.client_id', user()->id);
            });
        }

        if ($this->viewEstimateRequestPermission == 'owned') {
            $model->where('estimate_requests.client_id', user()->id);
        }

        return $model;

    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('estimate-request-table', 0)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["estimate-request-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
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
            '#' => ['data' => 'id', 'name' => 'id', 'visible' => false],
            __('modules.estimateRequest.estimateRequest') . ' ' . __('app.number') => ['data' => 'estimate_request_number', 'name' => 'estimate_request_number', 'title' => __('modules.estimateRequest.estimateRequest') . ' ' . __('app.number')],
            __('app.clientName') => ['data' => 'client', 'name' => 'users.name', 'title' => __('app.clientName')],
            __('app.project') => ['data' => 'project', 'name' => 'projects.project_name', 'title' => __('app.project')],
            __('modules.estimateRequest.estimatedBudget') => ['data' => 'estimated_budget', 'name' => 'estimated_budget', 'title' => __('modules.estimateRequest.estimatedBudget')],
            __('app.estimate') => ['data' => 'estimate_id', 'name' => 'estimate_id', 'title' => __('app.estimate')],
            __('app.status') => ['data' => 'status1', 'name' => 'status','width' => '10%', 'exportable' => false, 'visible' => true, 'title' => __('app.status')],
            __('modules.estimateRequest.earlyRequirement') => ['data' => 'early_requirement', 'name' => 'early_requirement',  'visible' => false],
            __('modules.estimateRequest.estimateRequest') . ' ' . __('app.status') => ['data' => 'status_name', 'name' => 'status', 'visible' => false, 'exportable' => true, 'title' => __('modules.estimateRequest.estimateRequest') . ' ' . __('app.status')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(100)
                ->addClass('text-right pr-20')
        ];
    }

}
