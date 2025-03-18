<?php

namespace App\Traits;

use App\Models\DashboardWidget;
use App\Models\Ticket;
use App\Models\TicketChannel;
use App\Models\TicketType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 *
 */
trait TicketDashboard
{

    /**
     *
     * @return void
     */
    public function ticketDashboard()
    {
        $this->viewTicketDashboard = user()->permission('view_ticket_dashboard');
        abort_403($this->viewTicketDashboard !== 'all');

        $this->pageTitle = 'app.ticketDashboard';
        $this->startDate = (request('startDate') != '') ? Carbon::createFromFormat($this->company->date_format, request('startDate')) : now($this->company->timezone)->startOfMonth();
        $this->endDate = (request('endDate') != '') ? Carbon::createFromFormat($this->company->date_format, request('endDate')) : now($this->company->timezone);
        $startDate = $this->startDate->startOfDay()->toDateTimeString();
        $endDate = $this->endDate->endOfDay()->toDateTimeString();

        $this->widgets = DashboardWidget::where('dashboard_type', 'admin-ticket-dashboard')->get();
        $this->activeWidgets = $this->widgets->filter(function ($value, $key) {
            return $value->status == '1';
        })->pluck('widget_name')->toArray();

        $ticketCounts = Ticket::select('id')->whereBetween('updated_at', [$startDate, $endDate])
            ->selectRaw(
                'SUM(CASE WHEN status IN ("open", "pending") THEN 1 ELSE 0 END) as totalUnresolvedTickets,
         SUM(CASE WHEN status IN ("resolved", "closed") THEN 1 ELSE 0 END) as totalResolvedTickets,
         SUM(CASE WHEN status IN ("open", "pending") AND agent_id IS NULL THEN 1 ELSE 0 END) as totalUnassignedTicket'
            )
            ->first();

        $this->totalUnresolvedTickets = $ticketCounts->totalUnresolvedTickets;
        $this->totalResolvedTickets = $ticketCounts->totalResolvedTickets;
        $this->totalUnassignedTicket = $ticketCounts->totalUnassignedTicket;


        $this->ticketTypeChart = $this->ticketTypeChart($startDate, $endDate);
        $this->ticketStatusChart = $this->ticketStatusChart($startDate, $endDate);
        $this->ticketChannelChart = $this->ticketChannelChart($startDate, $endDate);

        $this->newTickets = Ticket::with('requester')
            ->where('status', 'open')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->orderByDesc('updated_at')
            ->get();

        $this->view = 'dashboard.ajax.ticket';
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function ticketTypeChart($startDate, $endDate)
    {
        $tickets = TicketType::withCount(['tickets as tickets_within_date_range' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('updated_at', [$startDate, $endDate]);
        }])->get();

        $data['labels'] = $tickets->pluck('type')->toArray();

        if ($data['labels']) {
            foreach ($data['labels'] as $key => $value) {
                $data['colors'][] = '#' . substr(md5($value), 0, 6);
            }
        }
        else {
            $data['colors'] = [];
        }

        $data['values'] = $tickets->pluck('tickets_within_date_range')->toArray();

        return $data;
    }

    public function ticketStatusChart($startDate, $endDate)
    {
        $statusCounts = Ticket::whereBetween('updated_at', [$startDate, $endDate])
            ->select(DB::raw('count(id) as totalTicket'), 'status')
            ->groupBy('status')
            ->pluck('totalTicket', 'status'); // Use pluck for efficient data retrieval

        $data['colors'] = [
            'closed' => '#1d82f5', // Predefined color mapping
            'pending' => '#FCBD01',
            'resolved' => '#2CB100',
            'open' => '#D30000',
        ];

        $data['labels'] = $statusCounts->keys()->map(function ($status) {
            return __('app.' . $status); // Map key with translation
        })->toArray();

        $data['values'] = $statusCounts->pluck('totalTicket')->toArray();

        return $data;
    }

    public function ticketChannelChart($startDate, $endDate)
    {
        $tickets = TicketChannel::withCount(['tickets' => function ($query) use ($startDate, $endDate) {
            return $query->whereBetween('updated_at', [$startDate, $endDate]);
        }])->get();

        $data['labels'] = $tickets->pluck('channel_name')->toArray();

        foreach ($data['labels'] as $key => $value) {
            $data['colors'][] = '#' . substr(md5($value), 0, 6);
        }

        $data['values'] = $tickets->pluck('tickets_count')->toArray();

        return $data;
    }

}
