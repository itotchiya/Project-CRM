<?php

namespace App\DataTables;

use App\Models\Ticket;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use App\Models\TicketSettingForAgents;
use App\Models\TicketGroup;
use App\Models\TicketTagList;

class   TicketDataTable extends BaseDataTable
{

    private $viewTicketPermission;
    private $ignoreTrashed;

    public function __construct($ignoreTrashed = false)
    {
        parent::__construct();
        $this->viewTicketPermission = user()->permission('view_tickets');
        $this->ignoreTrashed = $ignoreTrashed;
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
        $datatables->addColumn('check', fn($row) => $this->checkBox($row));
        $datatables->addIndexColumn();
        $datatables->addColumn('action', function ($row) {

            $action = '<div class="task_view">';

            $action .= '<div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->ticket_number . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->ticket_number . '" tabindex="0">';

            if ($row->canViewTicket() || $row->agent_id == null) {
                $action .= '<a href="' . route('tickets.show', [$row->ticket_number]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';
            }

            if ($row->canDeleteTicket()) {
                $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-ticket-id="' . $row->id . '">
                <i class="fa fa-trash mr-2"></i>
                ' . trans('app.delete') . '
            </a>';
            }

            $action .= '</div>
                        </div>
                    </div>';

            return $action;
        });
        $datatables->addColumn('others', function ($row) {
            $others = '';

            if (!is_null($row->agent)) {
                $others .= '<div class="mb-2">' . __('modules.tickets.agent') . ': ' . (is_null($row->agent_id) ? '-' : $row->agent->name) . '</div> ';
            }

            $others .= '<div>' . __('modules.tasks.priority') . ': ' . __('app.' . $row->priority) . '</div> ';

            return $others;
        });

        $datatables->addColumn('status', function ($row) {
            if ($row->canEditTicket($row)) {

                $status = '<select class="form-control select-picker change-status" data-ticket-id="' . $row->id . '">';
                $status .= '<option ';

                if ($row->status == 'open') {
                    $status .= 'selected';
                }

                $status .= '  data-content="<i class=\'fa fa-circle mr-2 text-red\'></i> ' . __('app.open') . '" value="open">' . __('app.open') . '</option>';
                $status .= '<option ';

                if ($row->status == 'pending') {
                    $status .= 'selected';
                }

                $status .= '  data-content="<i class=\'fa fa-circle mr-2 text-yellow\'></i> ' . __('app.pending') . '" value="pending">' . __('app.pending') . '</option>';
                $status .= '<option ';

                if ($row->status == 'resolved') {
                    $status .= 'selected';
                }

                $status .= '  data-content="<i class=\'fa fa-circle mr-2 text-dark-green\'></i> ' . __('app.resolved') . '" value="resolved">' . __('app.resolved') . '</option>';
                $status .= '<option ';

                if ($row->status == 'closed') {
                    $status .= 'selected';
                }

                $status .= '  data-content="<i class=\'fa fa-circle mr-2 text-blue\'></i> ' . __('app.closed') . '" value="closed">' . __('app.closed') . '</option>';

                $status .= '</select>';

                return $status;
            }

            $statuses = [
                'open' => ['red', __('app.open')],
                'pending' => ['warning', __('app.pending')],
                'resolved' => ['dark-green', __('app.resolved')],
                'closed' => ['blue', __('app.closed')],
            ];

            $status = $statuses[$row->status] ?? $statuses['closed'];

            return '<i class="fa fa-circle mr-2 text-' . $status[0] . '"></i>' . $status[1];

        });
        $datatables->editColumn('ticket_status', fn($row) => $row->status);
        $datatables->editColumn('subject', fn($row) => '<a href="' . route('tickets.show', $row->ticket_number) . '" class="text-darkest-grey">' . $row->subject . '</a>' . $row->badge());
        $datatables->addColumn('name', fn($row) => $row->requester ? $row->requester->name : $row->ticket_number);
        $datatables->editColumn('user_id', function ($row) {
            if (is_null($row->requester)) {
                return '';
            }

            $viewComponent = $row->requester->hasRole('employee') ? 'components.employee' : 'components.client';

            return view($viewComponent, ['user' => $row->requester]);
        });

        $datatables->editColumn('updated_at', fn($row) => $row->created_at?->timezone($this->company->timezone)->translatedFormat($this->company->date_format . ' ' . $this->company->time_format));
        $datatables->setRowId(fn($row) => 'row-' . $row->id);
        $datatables->orderColumn('user_id', 'name $1');
        $datatables->orderColumn('status', 'id $1');
        $datatables->removeColumn('agent_id');
        $datatables->removeColumn('channel_id');
        $datatables->removeColumn('type_id');
        $datatables->removeColumn('deleted_at');

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatables, Ticket::CUSTOM_FIELD_MODEL);

        $datatables->rawColumns(array_merge(['others', 'action', 'subject', 'check', 'user_id', 'status'], $customFieldColumns));

        return $datatables;
    }

    /**
     * @param Ticket $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Ticket $model)
    {
        $request = $this->request();

        $model = $model->with('requester', 'agent', 'latestReply.user:id,name,image', 'group.enabledAgents')
            ->select('tickets.*')
            ->leftJoin('projects', 'projects.id', 'tickets.project_id')
            ->join('users', 'users.id', '=', 'tickets.user_id');

        // filter where project is soft deleted
        if (!$this->ignoreTrashed) {
            $model->where(function ($query) {
                $query->whereNotNull('tickets.project_id')
                    ->whereHas('project', function ($q) {
                        $q->whereNull('projects.deleted_at');
                    })->orWhereNull('tickets.project_id');
            });
        }

        if (!is_null($request->startDate) && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            $model->where(DB::raw('DATE(tickets.updated_at)'), '>=', $startDate);
        }

        if (!is_null($request->endDate) && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            $model->where(DB::raw('DATE(tickets.updated_at)'), '<=', $endDate);
        }

        if (!is_null($request->agentId) && $request->agentId != 'all' && $request->ticketFilterStatus != 'unassigned') {
            $model->where('tickets.agent_id', '=', $request->agentId);
        }

        if (!is_null($request->groupId) && $request->groupId != 'all') {
            $model->where('tickets.group_id', '=', $request->groupId);
        }

        if (!is_null($request->client_id) && $request->client_id != 'all') {
            $model->where('tickets.user_id', $request->client_id);
        }

        if (!is_null($request->employee_id) && $request->employee_id != 'all') {
            $model->where('tickets.user_id', $request->employee_id);
        }

        if (!is_null($request->ticketStatus) && $request->ticketStatus != 'all' && $request->ticketFilterStatus == '') {
            $request->ticketStatus == 'unassigned' ? $model->whereNull('agent_id') : $model->where('tickets.status', '=', $request->ticketStatus);
        }

        if ($request->ticketFilterStatus != '') {
            ($request->ticketFilterStatus == 'open' || $request->ticketFilterStatus == 'unassigned') ? $model->where(function ($query) {
                $query->where('tickets.status', '=', 'open')
                    ->orWhere('tickets.status', '=', 'pending');
            }) : $model->where(function ($query) {
                $query->where('tickets.status', '=', 'resolved')
                    ->orWhere('tickets.status', '=', 'closed');
            });

            if ($request->ticketFilterStatus == 'unassigned') {
                $model->whereNull('agent_id');
            }
        }

        if (!is_null($request->priority) && $request->priority != 'all') {
            $model->where('tickets.priority', '=', $request->priority);
        }

        if (!is_null($request->channelId) && $request->channelId != 'all') {

            $model->where('tickets.channel_id', '=', $request->channelId);
        }

        if (!is_null($request->typeId) && $request->typeId != 'all') {
            $model->where('tickets.type_id', '=', $request->typeId);
        }

        $tagIds = is_array($request->tagId) ? $request->tagId : explode(',', $request->tagId);
        $totalTagLists = TicketTagList::all();
        $totaltags = ($totalTagLists->count() + 1) - count($tagIds);

        if (is_array($request->tagId) && $request->tagId[0] !== 'all') {
            $model->join('ticket_tags', 'ticket_tags.ticket_id', 'tickets.id')
              ->whereIn('ticket_tags.tag_id', $tagIds)
              ->groupBy('tickets.id');
        } elseif(is_array($request->tagId) && $request->tagId[0] !== 'all' && $totaltags > 0){
            $model->join('ticket_tags', 'ticket_tags.ticket_id', 'tickets.id')
                ->whereIn('ticket_tags.tag_id', $tagIds)
                ->groupBy('tickets.id');
        } elseif(is_array($request->tagId) && $request->tagId[0] == 'all' && $totaltags > 0 && count($tagIds) !== 1){
            $model->leftJoin('ticket_tags', 'ticket_tags.ticket_id', '=', 'tickets.id')
                ->where(function ($query) use ($tagIds) {
                    $query->whereIn('ticket_tags.tag_id', $tagIds)
                        ->orWhereNull('ticket_tags.tag_id');
                })->groupBy('tickets.id');
        }elseif(is_array($request->tagId) && $request->tagId[0] == 'all' && count($tagIds) == 1){
            $model->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('ticket_tags')
                        ->whereColumn('ticket_tags.ticket_id', 'tickets.id');
                });
        }

        if (!is_null($request->projectID) && $request->projectID != 'all') {
            $model->whereHas('project', function ($q) use ($request) {
                $q->withTrashed()->where('tickets.project_id', $request->projectID);
            });
        }

        $userAssignedInGroup = false;
        if(in_array('employee', user_roles()) && !in_array('admin', user_roles()) && !in_array('client', user_roles())){
            $userAssignedInGroup = TicketGroup::whereHas('enabledAgents', function ($query) {
                $query->where('agent_id', user()->id)->orWhereNull('agent_id');
            })->exists();
        }

        if($userAssignedInGroup == false){

            if ($this->viewTicketPermission == 'owned') {
                $model->where(function ($query) {
                    $query->where('tickets.user_id', '=', user()->id)
                        ->orWhere('agent_id', '=', user()->id);
                });
            }

            if ($this->viewTicketPermission == 'both') {
                $model->where(function ($query) {
                    $query->where('tickets.user_id', '=', user()->id)
                        ->orWhere('tickets.added_by', '=', user()->id)
                        ->orWhere('agent_id', '=', user()->id);
                });
            }

            if ($this->viewTicketPermission == 'added') {
                $model->where('tickets.added_by', '=', user()->id);
            }
        }else{

            $ticketSetting = TicketSettingForAgents::first();

            if($ticketSetting?->ticket_scope == 'group_tickets'){

                $userGroupIds = TicketGroup::whereHas('enabledAgents', function ($query) {
                    $query->where('agent_id', user()->id);
                })->pluck('id')->toArray();

                $ticketSettingGroupIds = is_array($ticketSetting?->group_id) ? $ticketSetting?->group_id : explode(',', $ticketSetting?->group_id);

                // Find the common group IDs
                $commonGroupIds = array_intersect($userGroupIds, $ticketSettingGroupIds);

                if($commonGroupIds){
                    $model->where(function ($query) use ($commonGroupIds) {
                        $query->where(function ($subQuery) use ($commonGroupIds) {
                            // Conditions related to user and agent
                            $subQuery->where('tickets.user_id', '=', user()->id)
                                ->orWhere('tickets.added_by', '=', user()->id)
                                ->orWhere('tickets.agent_id', '=', user()->id)
                                ->orWhere('tickets.agent_id', '!=', user()->id)
                                ->whereIn('tickets.group_id', $commonGroupIds);
                        })
                        // Add orWhere for tickets where agent_id is null
                        ->orWhere(function ($subQuery) use ($commonGroupIds) {
                            $subQuery->whereNull('tickets.agent_id')
                                ->whereIn('tickets.group_id', $commonGroupIds);
                        });
                    });
                }else{
                    $model->where(function ($query) use ($userGroupIds) {
                        $query->where(function ($subQuery) use ($userGroupIds) {
                            // Conditions related to user and agent
                            $subQuery->where('tickets.user_id', '=', user()->id)
                                ->orWhere('tickets.added_by', '=', user()->id)
                                ->orWhere('tickets.agent_id', '=', user()->id)
                                ->orWhere('tickets.agent_id', '!=', user()->id)
                                ->whereIn('tickets.group_id', $userGroupIds);
                        })
                        // Add orWhere for tickets where agent_id is null
                        ->orWhere(function ($subQuery) use ($userGroupIds) {
                            $subQuery->whereNull('tickets.agent_id')
                                ->whereIn('tickets.group_id', $userGroupIds);
                        });
                    });
                }
            }

            if($ticketSetting?->ticket_scope == 'assigned_tickets'){
                $model->where(function ($query) {
                    $query->where('agent_id', '=', user()->id)
                    ->orWhere('tickets.user_id', '=', user()->id)
                    ->orWhere('tickets.added_by', '=', user()->id);
                });
            }
        }

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('tickets.subject', 'like', '%' . request('searchText') . '%')
                    ->orWhere('tickets.ticket_number', 'like', '%' . request('searchText') . '%')
                    ->orWhere('tickets.status', 'like', '%' . request('searchText') . '%')
                    ->orWhere('users.name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('tickets.priority', 'like', '%' . request('searchText') . '%');
            });
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
        $dataTable = $this->setBuilder('ticket-table', 5)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["ticket-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("#ticket-table .select-picker").selectpicker();

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

        $data = [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
                'visible' => !in_array('client', user_roles())
            ],
            __('modules.tickets.ticket') . ' #' => ['data' => 'ticket_number', 'name' => 'ticket_number', 'title' => __('modules.tickets.ticket') . ' #'],
            __('modules.tickets.ticketSubject') => ['data' => 'subject', 'name' => 'subject', 'title' => __('modules.tickets.ticketSubject'), 'width' => '20%'],
            __('app.name') => ['data' => 'name', 'name' => 'user_id', 'visible' => false, 'title' => __('app.name')],
            __('modules.tickets.requesterName') => ['data' => 'user_id', 'name' => 'user_id', 'visible' => !in_array('client', user_roles()), 'exportable' => false, 'title' => __('modules.tickets.requesterName'), 'width' => '20%'],
            __('modules.tickets.requestedOn') => ['data' => 'updated_at', 'name' => 'updated_at', 'title' => __('modules.tickets.requestedOn')],
            __('app.others') => ['data' => 'others', 'name' => 'others', 'sortable' => false, 'title' => __('app.others')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'exportable' => false, 'title' => __('app.status')],
            __('modules.ticketStatus') => ['data' => 'ticket_status', 'name' => 'ticket_status', 'visible' => false, 'title' => __('modules.ticketStatus')]
        ];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

        return array_merge($data, CustomFieldGroup::customFieldsDataMerge(new Ticket()), $action);

    }

}
