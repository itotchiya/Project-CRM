@if (in_array('ticket', $activeWidgets) && $sidebarUserPermissions['view_tickets'] != 5 && $sidebarUserPermissions['view_tickets'] != 'none' && $sidebarUserPermissions['view_timelogs'] != 'none' && in_array('tickets', user_modules()))
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 b-shadow-4 mb-3 e-d-info">
                <x-cards.data :title="__('modules.module.tickets')" padding="false" otherClasses="h-200">
                    <x-table>
                        <x-slot name="thead">
                            <th>@lang('modules.module.tickets')#</th>
                            <th>@lang('modules.tickets.ticketSubject')</th>
                            <th>@lang('app.status')</th>
                            <th class="text-right pr-20">@lang('modules.tickets.requestedOn')</th>
                        </x-slot>

                        @forelse ($tickets as $ticket)
                            <tr>
                                <td class="pl-20">
                                    <a href="{{ route('tickets.show', [$ticket->ticket_number]) }}" class="text-darkest-grey">#{{ $ticket->id }}</a>
                                </td>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <h5 class="f-12 mb-1 text-darkest-grey">
                                                <a href="{{ route('tickets.show', [$ticket->ticket_number]) }}">{{ $ticket->subject }}</a>
                                            </h5>
                                        </div>
                                    </div>
                                </td>
                                <td class="pr-20">
                                    @if( $ticket->status == 'open')
                                        <i class="fa fa-circle mr-1 text-red"></i>
                                    @else
                                        <i class="fa fa-circle mr-1 text-yellow"></i>
                                    @endif
                                    {{ $ticket->status }}
                                </td>
                                <td class="pr-20" align="right">
                                    <span>{{ $ticket->updated_at->translatedFormat(company()->date_format) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="shadow-none">
                                    <x-cards.no-record icon="tasks" :message="__('messages.noRecordFound')" />
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </x-cards.data>
            </div>
        </div>
    </div>
@endif
