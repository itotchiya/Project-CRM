<?php

namespace App\DataTables;

use App\Models\Appreciation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class AppreciationsDataTable extends BaseDataTable
{

    private $editAppreciationPermission;
    private $deleteAppreciationPermission;
    private $viewAppreciationPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editAppreciationPermission = user()->permission('edit_appreciation');
        $this->deleteAppreciationPermission = user()->permission('delete_appreciation');
        $this->viewAppreciationPermission = user()->permission('view_appreciation');
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
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">
                    <a href="' . route('appreciations.show', [$row->id]) . '" class="taskView text-darkest-grey f-w-500 openRightModal">' . __('app.view') . '</a>';

                if (($this->editAppreciationPermission == 'all' || ($this->editAppreciationPermission == 'added' && user()->id == $row->added_by) || ($this->editAppreciationPermission == 'owned' && user()->id == $row->award_to) || ($this->editAppreciationPermission == 'both' && ($row->added_by == user()->id || user()->id == $row->award_to))) || ($this->deleteAppreciationPermission == 'all' || ($this->deleteAppreciationPermission == 'added' && user()->id == $row->added_by) || ($this->deleteAppreciationPermission == 'owned' && user()->id == $row->award_to) || ($this->deleteAppreciationPermission == 'both' && ($row->added_by == user()->id || user()->id == $row->award_to)))) {
                    $action .= '<div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';
                }

                if ($this->editAppreciationPermission == 'all' ||
                    ($this->editAppreciationPermission == 'added' && user()->id == $row->added_by) ||
                    ($this->editAppreciationPermission == 'owned' && user()->id == $row->award_to) ||
                    ($this->editAppreciationPermission == 'both' && ($row->added_by == user()->id || user()->id == $row->award_to))
                ) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('appreciations.edit', [$row->id]) . '"> <i class="fa fa-edit mr-2"></i>' . trans('app.edit') . '</a>';
                }

                if ($this->deleteAppreciationPermission == 'all' || ($this->deleteAppreciationPermission == 'added' && user()->id == $row->added_by) || ($this->deleteAppreciationPermission == 'owned' && user()->id == $row->award_to) || ($this->deleteAppreciationPermission == 'both' && ($row->added_by == user()->id || user()->id == $row->award_to))) {
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
            ->editColumn('award_id', fn($row) => isset($row->award?->awardIcon) ? view('components.award-icon', ['award' => $row->award]) . ' <span class="align-self-center ml-2">' . $row->award->title . '</span>' : '-')
            ->addColumn('appreciation_type', fn($row) => $row->award ? $row->award->title : '-')
            ->addColumn('award_date', fn($row) => $row->award_date->translatedFormat($this->company->date_format))
            ->addColumn('award_to', fn($row) => view('components.employee', ['user' => $row->awardTo]))
            ->addColumn('award_employee', fn($row) => $row->awardTo->name)
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['check', 'action', 'award_id', 'award_to']);
    }

    /***
     * @param Notice $model
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(Appreciation $model)
    {
        $request = $this->request();
        $model = $model->with([
            'award',
            'award.awardIcon',
            'awardTo',
            'awardTo.employeeDetail' => function ($query) {
                $query->select('user_id', 'added_by', 'designation_id', 'employee_id', 'joining_date')
                    ->with('reportingTo:id,name,image', 'designation:id,name');
            },
            'awardTo.session',
        ])
            ->select('id', 'award_id', 'award_to', 'award_date', 'image', 'summary', 'created_at')
            ->join('awards', 'awards.id', '=', 'appreciations.award_id')
            ->join('users', 'users.id', '=', 'appreciations.award_to');

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = Carbon::createFromFormat(company()->date_format, $request->startDate)->toDateString();
            $model = $model->where(DB::raw('DATE(appreciations.`award_date`)'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = Carbon::createFromFormat(company()->date_format, $request->endDate)->toDateString();
            $model = $model->where(DB::raw('DATE(appreciations.`award_date`)'), '<=', $endDate);
        }

        if ($this->viewAppreciationPermission == 'added') {
            $model->where('appreciations.added_by', user()->id);
        }

        if ($this->viewAppreciationPermission == 'owned') {
            $model->where('appreciations.award_to', user()->id);
        }

        if ($this->viewAppreciationPermission == 'both') {
            $model->where(function ($q) {
                $q->where('appreciations.added_by', '=', user()->id);
                $q->orWhere('appreciations.award_to', '=', user()->id);
            });
        }

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('awards.title', 'like', '%' . request('searchText') . '%');
            });
        }

        if ($request->appreciationType != 'all') {
            $model->where(function ($query) use ($request) {
                $query->where('appreciations.award_id', $request->appreciationType);
            });
        }

        if ($request->awardTo != 'all') {
            $model->where(function ($query) use ($request) {
                $query->where('appreciations.award_to', $request->awardTo);
            });
        }

        $model->select('appreciations.*');

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('user-appreciation-table', 3)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["user-appreciation-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
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
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('modules.appreciations.awardToEmployee') => ['data' => 'award_to', 'exportable' => false, 'name' => 'award_to', 'title' => __('modules.appreciations.awardToEmployee')],
            __('modules.appreciations.awardTo') => ['data' => 'award_employee', 'name' => 'award_employee', 'visible' => false, 'title' => __('modules.appreciations.awardTo')],
            __('modules.appreciations.appreciationTypeName') => ['data' => 'award_id', 'exportable' => false, 'name' => 'award_id', 'title' => __('modules.appreciations.appreciationTypeName')],
            __('modules.appreciations.appreciationType') => ['data' => 'appreciation_type', 'name' => 'award_id', 'visible' => false, 'title' => __('modules.appreciations.appreciationType')],
            __('app.date') => ['data' => 'award_date', 'name' => 'award_date', 'title' => __('modules.appreciations.awardDate')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
