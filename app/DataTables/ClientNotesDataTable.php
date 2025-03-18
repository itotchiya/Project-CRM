<?php

namespace App\DataTables;

use App\Models\ClientNote;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ClientNotesDataTable extends BaseDataTable
{

    private $viewClientNotePermission;
    private $editClientNotePermission;
    private $deleteClientNotePermission;

    public function __construct()
    {
        parent::__construct();
        $this->editClientNotePermission = user()->permission('edit_client_note');
        $this->deleteClientNotePermission = user()->permission('delete_client_note');
        $this->viewClientNotePermission = user()->permission('view_client_note');
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

                $action = '<div class="task_view">';

                $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if ($row->ask_password == 1) {
                    $action .= '<a href="javascript:;" class="dropdown-item ask-for-password" data-client-note-id="' . $row->id . '"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';
                }
                else {
                    $action .= '<a href="' . route('client-notes.show', $row->id) . '" class="openRightModal dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';
                }


                if ($this->editClientNotePermission == 'all' || ($this->editClientNotePermission == 'added' && user()->id == $row->added_by) || ($this->editClientNotePermission == 'both' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('client-notes.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($this->deleteClientNotePermission == 'all' || ($this->deleteClientNotePermission == 'added' && user()->id == $row->added_by) || ($this->deleteClientNotePermission == 'both' && user()->id == $row->added_by)) {
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
            ->editColumn('title', function ($row) {
                if ($row->ask_password == 1) {
                    return '<a href="javascript:;" class="ask-for-password" data-client-note-id="' . $row->id . '" style="color:black;">' . $row->title . '</a>';
                }

                return '<a href="' . route('client-notes.show', $row->id) . '" class="openRightModal " style="color:black;">' . $row->title . '</a>';

            })
            ->editColumn('type', function ($row) {
                if ($row->type == '0') {
                    return '<span class="badge badge-secondary"><i class="fa fa-globe"></i> ' . __('app.public') . '</span>';
                }

                return '<span class="badge badge-primary"><i class="fa fa-lock"></i> ' . __('app.private') . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->timezone(company()->timezone)->translatedFormat($this->company->date_format . ' ' . $this->company->time_format);
            })
            ->editColumn('id', fn($row) => $row->id)
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check', 'title', 'type']);
    }

    /**
     * @param ClientNote $model
     * @return ClientNote|\Illuminate\Database\Eloquent\Builder
     */
    public function query(ClientNote $model)
    {
        $request = $this->request();

        $notes = $model->where('client_notes.client_id', $request->clientID);
        $notes->leftJoin('client_user_notes', 'client_user_notes.client_note_id', '=', 'client_notes.id');

        if (in_array('client', user_roles())) {
            $notes->where(function ($query) {
                return $query->where('client_notes.type', 0)
                    ->orWhere('client_notes.is_client_show', 1);
            });

        }
        elseif (!in_array('admin', user_roles())) {

            if ($this->viewClientNotePermission == 'added') {
                $notes->where(function ($query) {
                    return $query->where('client_notes.added_by', user()->id)
                        ->orWhere('client_notes.type', 0);
                });

            }
            elseif ($this->viewClientNotePermission == 'owned') {
                $notes->where(function ($query) {
                    return $query->where('client_user_notes.user_id', user()->id)
                        ->orWhere('client_notes.type', 0);
                });

            }
            elseif ($this->viewClientNotePermission == 'both') {
                $notes->where(function ($query) {
                    return $query->where('client_user_notes.user_id', user()->id)
                        ->orWhere('client_notes.type', 0)
                        ->orWhere('client_notes.added_by', user()->id);
                });

            }
        }


        if (!is_null($request->searchText)) {
            $notes->where('client_notes.title', 'like', '%' . request('searchText') . '%');
        }

        $notes->select('client_notes.*')->groupBy('client_notes.id');

        return $notes;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('client-notes-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["client-notes-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                  //
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
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'title' => __('app.id')],
            __('modules.client.noteTitle') => ['data' => 'title', 'name' => 'title', 'title' => __('modules.client.noteTitle')],
            __('modules.client.noteType') => ['data' => 'type', 'name' => 'type', 'title' => __('modules.client.noteType')],
            __('app.createdOn') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('app.createdOn')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
