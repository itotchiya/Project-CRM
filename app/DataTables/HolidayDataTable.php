<?php

namespace App\DataTables;

use App\Models\Holiday;
use App\Models\Designation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class HolidayDataTable extends BaseDataTable
{

    private $editPermission;
    private $deletePermission;
    private $viewPermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission = user()->permission('view_holiday');
        $this->editPermission = user()->permission('edit_holiday');
        $this->deletePermission = user()->permission('delete_holiday');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return (new EloquentDataTable($query))

            ->addIndexColumn()
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->editColumn('holiday_date', function ($row) {

                return Carbon::parse($row->date)->translatedFormat($this->company->date_format);
            })

            ->addColumn('occasion', function ($row) {
                return $row->occassion;
            })
            ->addColumn('day', function ($row) {
                return $row->date->translatedFormat('l');
            })




            ->addColumn('department', function ($row) {
                $value = (!empty($row->department_id_json) && $row->department_id_json != 'null') ? collect (Holiday::department(json_decode($row->department_id_json)))

                    ->map(function($val){
                        return '<ul>' . $val  . '</ul>';

                    })
                      ->implode('') : '--';

                         return $value !== '' ? $value : '--';

            })
            ->addColumn('designation', function ($row) {
                $value = (!empty($row->designation_id_json) && $row->designation_id_json != 'null') ? collect( Holiday::designation(json_decode($row->designation_id_json)))
                  ->map(function($val){
                    return '<ul>' . $val  . '</ul>';

                  })
                  ->implode('') : '--';

                     return $value !== '' ? $value : '--';

            })





            ->addColumn('employment_type', function ($row) {
                $value = !empty($row->employment_type_json) ? collect(json_decode($row->employment_type_json))
                        ->map(function ($employmentType) {
                            return '<ul>' . __('modules.employees.' . $employmentType) . '</ul>';
                        })
                        ->implode('') : '--';
                return $value !== '' ? $value : '--';
            })



            ->addColumn('action', function ($row) {

                $actions = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-41" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-41" tabindex="0" x-placement="bottom-end" style="position: absolute; transform: translate3d(-137px, 26px, 0px); top: 0px; left: 0px; will-change: transform;">';

                $actions .= '<a href="' . route('holidays.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="mr-2 fa fa-eye"></i>' . __('app.view') . '</a>';

                if ($this->editPermission == 'all' || ($this->editPermission == 'added' && user()->id == $row->added_by)) {
                    $actions .= '<a class="dropdown-item openRightModal" href="' . route('holidays.edit', [$row->id]) . '">
                                    <i class="mr-2 fa fa-edit"></i>
                                    ' . __('app.edit') . '
                            </a>';
                }

                if ($this->deletePermission == 'all' || ($this->deletePermission == 'added' && user()->id == $row->added_by)) {
                    $actions .= '<a data-holiday-id=' . $row->id . '
                            class="dropdown-item delete-table-row" href="javascript:;">
                               <i class="mr-2 fa fa-trash"></i>
                                ' . __('app.delete') . '
                        </a>';
                }

                $actions .= '</div> </div> </div>';

                return $actions;
            })
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->orderColumn('holiday_date', 'date $1')
            ->orderColumn('day', 'day_name $1')
            ->rawColumns(['check', 'action', 'employment_type','department','designation']);
    }

    /**
     * @param Holiday $model
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(Holiday $model)
    {
        $user = user();
        $holidays = $model->select('holidays.*', DB::raw('DAYNAME(date) as day_name'));

        if (!is_null(request()->year)) {
            $holidays->where(DB::raw('Year(holidays.date)'), request()->year);
        }

        if (!is_null(request()->month)) {
            $holidays->where(DB::raw('Month(holidays.date)'), request()->month);
        }

        if (request()->searchText != '') {
            $holidays->where('holidays.occassion', 'like', '%' . request()->searchText . '%');
        }

        if ($this->viewPermission == 'added') {
            $holidays->where('holidays.added_by', user()->id);
        }

        if ($this->viewPermission == 'owned') {
            $holidays->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->orWhere('department_id_json', 'like', '%"' . $user->employeeDetail->department_id . '"%')
                        ->orWhereNull('department_id_json');
                });
                $query->where(function ($q) use ($user) {
                    $q->orWhere('designation_id_json', 'like', '%"' . $user->employeeDetail->designation_id . '"%')
                        ->orWhereNull('designation_id_json');
                });
                $query->where(function ($q) use ($user) {
                    $q->orWhere('employment_type_json', 'like', '%"' . $user->employeeDetail->employment_type . '"%')
                        ->orWhereNull('employment_type_json');
                });

            });
        }

        if ($this->viewPermission == 'both') {
            $holidays->where(function ($query) use ($user) {
                $query->where('holidays.added_by', $user->id)

                    ->orWhere(function ($subquery) use ($user) {
                        $subquery->where(function ($q) use ($user) {
                            $q->where('department_id_json', 'like', '%"' . $user->employeeDetail->department_id . '"%')
                                ->orWhereNull('department_id_json');
                        });
                        $subquery->where(function ($q) use ($user) {
                            $q->where('designation_id_json', 'like', '%"' . $user->employeeDetail->designation_id . '"%')
                                ->orWhereNull('designation_id_json');
                        });
                        $subquery->where(function ($q) use ($user) {
                            $q->where('employment_type_json', 'like', '%"' . $user->employeeDetail->employment_type . '"%')
                                ->orWhereNull('employment_type_json');
                        });

                    });

            });
        }





        return $holidays;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('holiday-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["holiday-table"].buttons().container()
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
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('modules.holiday.date') => ['data' => 'holiday_date', 'name' => 'date', 'title' => __('modules.holiday.date')],
            __('modules.holiday.occasion') => ['data' => 'occasion', 'name' => 'occasion', 'title' => __('modules.holiday.occasion')],
            __('modules.holiday.day') => ['data' => 'day', 'name' => 'day', 'title' => __('modules.holiday.day')],
            __('app.department') => ['data' => 'department', 'name' => 'department', 'title' => __('app.department')],
             __('app.designation') => ['data' => 'designation', 'name' => 'designation', 'title' => __('app.designation')],
             __('modules.employees.employmentType') => ['data' => 'employment_type', 'name' => 'employment_type', 'title' => __('modules.employees.employmentType')],

            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
