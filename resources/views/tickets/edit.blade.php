@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

    <style>
        .message-action {
            visibility: hidden;
        }

        .ticket-left .card:hover .message-action {
            visibility: visible;
        }

        .file-action {
            visibility: hidden;
        }

        .file-card:hover .file-action {
            visibility: visible;
        }

        .frappe-chart .chart-legend {
            display: none;
        }

        .ticket-overflow {
            overflow-y: auto;
        }

        .ticket-activity .recent-ticket-inner:before {
            background-color: #99a5b5;
            content: "";
            height: 100%;
            left: 10.9px;
            position: absolute;
            top: 4px;
            width: 1px;
        }

        .update-buttons {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
            border-radius: 0.2rem !important;
            margin-bottom: 3px !important;
        }

    </style>
    <script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
    <script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>

@endpush

@php
$editTicketPermission = user()->permission('edit_tickets');
$deleteTicketPermission = user()->permission('delete_tickets');
$manageTypePermission = user()->permission('manage_ticket_type');
$manageAgentPermission = user()->permission('manage_ticket_agent');
$manageChannelPermission = user()->permission('manage_ticket_channel');
$manageGroupPermission = user()->permission('manage_ticket_groups');
$canEditTicket = ($editTicketPermission == 'all' || ($editTicketPermission == 'owned' && $ticket->agent_id == user()->id));
@endphp

@section('filter-section')
    <!-- FILTER START -->
    <!-- TICKET HEADER START -->
    <div class="d-flex px-4 filter-box bg-white">

        <a href="javascript:;"
            class="d-flex align-items-center height-44 text-dark-grey  border-right-grey pr-3 reply-button"><i
                class="fa fa-reply mr-0 mr-lg-2 mr-md-2"></i><span
                class="d-none d-lg-block d-md-block">@lang('app.reply')</span></a>

        @if(!in_array('client', user_roles()))
            <a href="javascript:;" class="d-flex align-items-center height-44 text-dark-grey  border-right-grey px-3 note-button"><i
                    class="fa fa-clipboard-list mr-0 mr-lg-2 mr-md-2"></i><span class="d-none d-lg-block d-md-block">@lang('app.addNote')</span></a>
        @endif
        <div id="ticket-closed" @if ($ticket->status == 'closed') style="display:none" @endif>
            <a href="javascript:;" data-status="closed"
                class="d-flex align-items-center height-44 text-dark-grey  border-right-grey px-3 submit-ticket"><i
                    class="fa fa-times-circle mr-0 mr-lg-2 mr-md-2"></i><span
                    class="d-none d-lg-block d-md-block">@lang('app.close')</span></a>
        </div>

        @if ($deleteTicketPermission == 'all' || ($deleteTicketPermission == 'owned' && $ticket->agent_id == user()->id))
            <a href="javascript:;"
                class="d-flex align-items-center height-44 text-dark-grey  border-right-grey px-3 delete-ticket"><i
                    class="fa fa-trash mr-0 mr-lg-2 mr-md-2"></i><span
                    class="d-none d-lg-block d-md-block">@lang('app.delete')</span>
            </a>
        @endif

        <a onclick="openTicketsSidebar()"
            class="d-flex d-lg-none ml-auto align-items-center justify-content-center height-44 text-dark-grey  border-left-grey pl-3"><i
                class="fa fa-ellipsis-v"></i></a>

    </div>
    <!-- FILTER END -->
    <!-- TICKET HEADER END -->
@endsection

@section('content')

    <!-- TICKET START -->
    <div class="ticket-wrapper bg-white border-top-0 d-lg-flex">

        <!-- TICKET LEFT START -->
        <div class="ticket-left w-100">
            <x-form id="updateTicket2" method="PUT">
                <input type="hidden" name="status" id="status" value="{{ $ticket->status }}">
                <input type="hidden" name="type" id="type" value="{{ $ticket->type }}">
                <input type="hidden" id="ticket_reply_id" value="">

                <!-- START -->
                <div id="ticket-info-bar" class="d-flex justify-content-between align-items-center p-3 border-right-grey border-bottom-grey">
                    <span>
                        <p class="f-15 f-w-500 mb-0">{{ $ticket->subject }}</p>
                        <p class="f-11 text-lightest mb-0">@lang('modules.tickets.requestedOn')
                            {{ $ticket->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                        </p>
                    </span>
                    <span id="ticketStatusBadge">
                        @if ($ticket->status == 'open')
                            @php
                                $statusColor = 'red';
                            @endphp
                        @elseif($ticket->status == 'pending')
                            @php
                                $statusColor = 'yellow';
                            @endphp
                        @elseif($ticket->status == 'resolved')
                            @php
                                $statusColor = 'dark-green';
                            @endphp
                        @elseif($ticket->status == 'closed')
                            @php
                                $statusColor = 'blue';
                            @endphp
                        @endif
                        <p class="mb-0  ticket-status">
                           {!! $ticket->badge('span') !!}
                            <x-status :color="$statusColor" :value="__('app.'. $ticket->status)" />
                        </p>
                    </span>
                </div>
                <!-- END -->
                <!-- TICKET MESSAGE START -->
                <div class="ticket-msg border-right-grey" data-menu-vertical="1" data-menu-scroll="1"
                    data-menu-dropdown-timeout="500" id="ticketMsg">
                    @foreach ($ticket->reply as $reply)
                    @if (!in_array('client', user_roles()) || $reply->type != 'note')
                        <x-cards.ticket :message="$reply" :user="$reply->user" />
                    @endif
                    @endforeach

                </div>
                <!-- TICKET MESSAGE END -->
                <div class="border-right-grey" id="ticketMsgBottom">
                    <div class="col-md-12 border-top d-none mb-5" id="reply-section">
                        <div class="form-group my-3">
                            @if ($ticket->requester->id != user()->id || (!is_null($ticket->agent_id) && $ticket->agent_id != user()->id))
                            <p class="f-w-500">
                                @lang('app.to'): {{ ($ticket->requester->id != user()->id) ? $ticket->requester->name : $ticket->agent->name }}
                            </p>
                            @endif
                            <div id="description"></div>
                            <textarea name="message" id="description-text" class="d-none"></textarea>
                        </div>
                        <div class="my-3">
                            <a class="f-15 f-w-500" href="javascript:;" id="add-file"><i
                                    class="fa fa-paperclip font-weight-bold mr-1"></i>@lang('modules.projects.uploadFile')</a>
                        </div>
                        <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2 upload-section d-none"
                            fieldLabel=""
                            fieldName="file[]" fieldId="ticket-file-upload-dropzone" />
                    </div>

                    <div class="col-md-12 border-top d-none mb-5" id="note-section">
                        <div class="my-3">
                            {{-- @if ($ticket->requester->id != user()->id || (!is_null($ticket->agent_id) && $ticket->agent_id != user()->id)) --}}
                            <div class="d-flex align-items-center mb-3">
                                <p class="f-w-500 m-0">@lang('app.to'):</p>
                                <div class="col-md-9 mb-0 form-group">
                                    <select class="multiple-users form-control" multiple name="user_id[]"
                                        id="user_id" data-live-search="true" data-size="8">
                                        @foreach ($ticketAgents as $item)
                                            <x-user-option :user="$item" :pill="true"/>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- @endif --}}
                            <div class="form-group">

                                <div id="description2"></div>

                                <textarea name="message2" id="description-text2" class="d-none"></textarea>
                            </div>

                        </div>
                        <div class="form-group">
                        <div class="my-3 form-group">
                            <a class="f-15 f-w-500" href="javascript:;" id="add-note-file"><i
                                    class="fa fa-paperclip font-weight-bold mr-1"></i>@lang('modules.projects.uploadFile')</a>
                        </div>

                            <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2 upload-note-section d-none"
                                fieldLabel=""
                                fieldName="file[]" fieldId="ticket-note-file-upload-dropzone"/>
                        </div>
                    </div>

                    <div class="ticket-reply-back justify-content-start px-lg-4 px-md-4 px-3 py-3  d-flex bg-white border-top-grey"
                        id="reply-section-action">

                        <x-forms.button-primary class="reply-button mr-3" icon="reply">@lang('app.reply')
                        </x-forms.button-primary>

                        @if (!in_array('client', user_roles()))
                            <x-forms.button-secondary class="note-button mr-3" icon="edit">@lang('app.addNote')
                            </x-forms.button-secondary>
                        @endif

                        <x-forms.link-secondary :link="route('tickets.index')" icon="arrow-left">@lang('app.back')
                        </x-forms.link-secondary>

                    </div>
                    <div class="ticket-reply-back flex-row justify-content-start px-lg-4 px-md-4 px-3 py-3 c-inv-btns bg-white border-top-grey d-none"
                        id="reply-section-action-2">
                        @if ($editTicketPermission == 'all'
                        || ($editTicketPermission == 'added' && user()->id == $ticket->added_by)
                        || ($editTicketPermission == 'owned' && (user()->id == $ticket->user_id || $ticket->agent_id == user()->id))
                        || ($editTicketPermission == 'both' && (user()->id == $ticket->user_id || $ticket->agent_id == user()->id || $ticket->added_by == user()->id)))
                            <div class="inv-action dropup mr-3">
                                <button class="btn-primary dropdown-toggle" type="button" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    @lang('app.submit')
                                    <span><i class="fa fa-chevron-up f-15 text-white"></i></span>
                                </button>
                                <!-- DROPDOWN - INFORMATION -->
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuBtn" tabindex="0">
                                    <li>
                                        <a class="dropdown-item f-14 text-dark submit-ticket" href="javascript:;"
                                            data-status="open" data-type="reply">
                                            <x-status color="red" :value="__('modules.tickets.submitOpen')" />
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item f-14 text-dark submit-ticket" href="javascript:;"
                                            data-status="pending" data-type="reply">
                                            <x-status color="yellow" :value="__('modules.tickets.submitPending')" />
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item f-14 text-dark submit-ticket" href="javascript:;"
                                            data-status="resolved" data-type="reply">
                                            <x-status color="dark-green" :value="__('modules.tickets.submitResolved')" />
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item f-14 text-dark submit-ticket" href="javascript:;"
                                            data-status="closed" data-type="reply">
                                            <x-status color="blue" :value="__('modules.tickets.submitClosed')" />
                                        </a>
                                    </li>

                                </ul>
                            </div>
                        @else
                            <x-forms.button-primary icon="check" data-status="open" class="submit-ticket mr-3" data-type="reply">
                                @lang('app.submit')
                            </x-forms.button-primary>
                        @endif

                        @if (!in_array('client', user_roles()))
                            <div class="inv-action dropup mr-3">
                                <button class="btn-secondary dropdown-toggle" type="button" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-bolt f-15 mr-1"></i>
                                    @lang('modules.tickets.applyTemplate')
                                    <span><i class="fa fa-chevron-up f-15"></i></span>
                                </button>
                                <!-- DROPDOWN - INFORMATION -->
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuBtn" tabindex="0">
                                    @forelse($templates as $template)
                                        <li><a href="javascript:;" data-template-id="{{ $template->id }}"
                                                class="dropdown-item f-14 text-dark apply-template">{{ $template->reply_heading }}</a>
                                        </li>
                                    @empty
                                        <li><a class="dropdown-item f-14 text-dark">@lang('messages.noTemplateFound')</a></li>
                                    @endforelse
                                </ul>
                            </div>
                        @endif

                        <x-forms.link-secondary id="cancel-reply" class="border-0" link="javascript:;">@lang('app.cancel')
                        </x-forms.link-secondary>

                    </div>

                    <div class="ticket-reply-back flex-row justify-content-start px-lg-4 px-md-4 px-3 py-3 c-inv-btns bg-white border-top-grey d-none"
                        id="note-section-action-2">
                        @if ($editTicketPermission == 'all'
                        || ($editTicketPermission == 'added' && user()->id == $ticket->added_by)
                        || ($editTicketPermission == 'owned' && (user()->id == $ticket->user_id || $ticket->agent_id == user()->id))
                        || ($editTicketPermission == 'both' && (user()->id == $ticket->user_id || $ticket->agent_id == user()->id || $ticket->added_by == user()->id)))
                            <div class="inv-action dropup mr-3">
                                <button class="btn-primary dropdown-toggle" type="button" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    @lang('app.addNote')
                                    <span><i class="fa fa-chevron-up f-15 text-white"></i></span>
                                </button>
                                <!-- DROPDOWN - INFORMATION -->
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuBtn" tabindex="0">
                                    <li>
                                        <a class="dropdown-item f-14 text-dark submit-ticket" href="javascript:;"
                                            data-status="open" data-type="note">
                                            <x-status color="red" :value="__('modules.tickets.noteOpen')" />
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item f-14 text-dark submit-ticket" href="javascript:;"
                                            data-status="pending" data-type="note">
                                            <x-status color="yellow" :value="__('modules.tickets.notePending')" />
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item f-14 text-dark submit-ticket" href="javascript:;"
                                            data-status="resolved" data-type="note">
                                            <x-status color="dark-green" :value="__('modules.tickets.noteResolved')" />
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item f-14 text-dark submit-ticket" href="javascript:;"
                                            data-status="closed" data-type="note">
                                            <x-status color="blue" :value="__('modules.tickets.noteClosed')" />
                                        </a>
                                    </li>

                                </ul>
                            </div>
                        @else
                            <x-forms.button-primary icon="edit" data-status="open" class="submit-ticket mr-3">
                                @lang('app.addNote')
                            </x-forms.button-primary>
                        @endif

                        <x-forms.link-secondary id="cancel-note" class="border-0" link="javascript:;">@lang('app.cancel')
                        </x-forms.link-secondary>

                    </div>
            </div>
            </x-form>
        </div>
        <!-- TICKET LEFT END -->

        <!-- TICKET RIGHT START -->
        <div class="mobile-close-overlay w-100 h-100" id="close-tickets-overlay"></div>
        <div class="ticket-right bg-white ticket-sidebar" id="ticket-detail-contact">
            <a class="d-none" id="close-tickets"><i class="fa fa-times"></i></a>
            <div id="tabs">
                <nav class="tabs px-2 border-bottom-grey">
                    <div class="nav" id="nav-tab" role="tablist">
                        @if ($canEditTicket)
                        <a class="nav-item nav-link f-14 active" id="nav-detail-tab" data-toggle="tab"
                            href="#nav-details" role="tab" aria-controls="nav-email"
                            aria-selected="false">@lang('app.details')</a>
                        @endif
                        <a @class([
                            'nav-item nav-link f-14',
                            'active' => !$canEditTicket
                        ])
                        id="nav-contact-tab" data-toggle="tab" href="#nav-contact"
                            role="tab" aria-controls="nav-slack" aria-selected="true">@lang('app.contact')</a>
                        <a class="nav-item nav-link f-14" id="nav-other-tab" data-toggle="tab" href="#nav-other"
                            role="tab">@lang('app.other')</a>
                        <a class="nav-item nav-link f-14" id="nav-activity-tab" data-toggle="tab" href="#nav-activity"
                            role="tab">@lang('app.activity')</a>
                    </div>
                </nav>
            </div>
            <div class="tab-content h-100" id="nav-tabContent">
                <!-- DETAILS START -->
                @if ($canEditTicket)
                <div class="tab-pane fade h-100 show active" id="nav-details" role="tabpanel"
                    aria-labelledby="nav-detail-tab">
                    <x-form id="updateTicket1">
                        <!-- TICKET FILTERS START -->
                        <div id="updateTicketForm" class="ticket-overflow p-4 w-100 position-relative border-bottom">
                            <div class="more-filter-items mb-4">
                                @foreach ($groups as $group)

                                        @endforeach
                                <x-forms.label class="my-3" fieldId="group_id"
                                    :fieldLabel="__('modules.tickets.assignGroup')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker " name="group_id" id="group_id"
                                        data-live-search="true" data-container="body" data-size="8">
                                        @foreach ($groups as $group)
                                            <option @selected($group->id == $ticket->group_id) value="{{ $group->id }}">{{ $group->group_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($manageGroupPermission == 'all')
                                        <x-slot name="append">
                                            <button id="manage-groups" type="button"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>
                            <div class="more-filter-items mb-4">
                                <x-forms.label class="my-3" fieldId="agent_id"
                                    :fieldLabel="__('modules.tickets.agent')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker " name="agent_id" id="agent_id"
                                        data-live-search="true" data-container="body" data-size="8">
                                        <option value="">--</option>
                                    </select>
                                    @if ($manageAgentPermission == 'all')
                                        <x-slot name="append">
                                            <button id="addAgent" type="button"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>
                            <div class="more-filter-items">
                                <x-forms.select fieldId="priority" :fieldLabel="__('modules.tasks.priority')"
                                                fieldName="priority" data-container="body">
                                    <option @selected($ticket->priority == 'low') value="low"
                                            data-content="<i class='fa fa-circle mr-2 text-dark-green'></i> {{ __('app.low')}}"
                                    >@lang('app.low')</option>
                                    <option @selected($ticket->priority == 'medium') value="medium"
                                            data-content="<i class='fa fa-circle mr-2 text-blue'></i> {{ __('app.medium')}}"
                                    >@lang('app.medium')</option>
                                    <option @selected($ticket->priority == 'high') value="high"
                                            data-content="<i class='fa fa-circle mr-2 text-warning'></i> {{ __('app.high')}}"
                                    >@lang('app.high')</option>
                                    <option @selected($ticket->priority == 'urgent') value="urgent"
                                            data-content="<i class='fa fa-circle mr-2 text-red'></i> {{ __('app.urgent')}}"
                                    >@lang('app.urgent')</option>
                                </x-forms.select>
                            </div>
                            <div class="more-filter-items mb-4">
                                <x-forms.label class="my-3" fieldId="ticket_type_id"
                                    :fieldLabel="__('modules.invoices.type')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="type_id" id="ticket_type_id"
                                        data-container="body" data-live-search="true" data-size="8">
                                        <option value="">--</option>
                                        @foreach ($types as $type)
                                            <option @selected($type->id == $ticket->type_id) value="{{ $type->id }}">
                                                {{ $type->type }}</option>
                                        @endforeach
                                    </select>
                                    @if ($manageTypePermission == 'all')
                                        <x-slot name="append">
                                            <button id="addTicketType" type="button"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>
                            <div class="more-filter-items mb-4">
                                <x-forms.label class="my-3" fieldId="ticket_channel_id"
                                    :fieldLabel="__('modules.tickets.channelName')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="channel_id" id="ticket_channel_id"
                                        data-container="body" data-live-search="true" data-size="8">
                                        <option value="">--</option>
                                        @foreach ($channels as $channel)
                                            <option @selected($channel->id == $ticket->channel_id) value="{{ $channel->id }}">
                                                {{ $channel->channel_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($manageChannelPermission == 'all')
                                        <x-slot name="append">
                                            <button id="addChannel" type="button"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>
                            <div class="more-filter-items">
                                <x-forms.select fieldId="ticket-status" :fieldLabel="__('app.status')"
                                    fieldName="status" data-container="body">
                                    <option @selected($ticket->status == 'open') value="open"
                                        data-content="<i class='fa fa-circle mr-2 text-red'></i>{{ __('app.open') }}">
                                        @lang('app.open')
                                    </option>
                                    <option @selected($ticket->status == 'pending') value="pending"
                                        data-content="<i class='fa fa-circle mr-2 text-yellow'></i>{{ __('app.pending') }}">
                                        @lang('app.pending')</option>
                                    <option @selected($ticket->status == 'resolved') value="resolved"
                                        data-content="<i class='fa fa-circle mr-2 text-dark-green'></i>{{ __('app.resolved') }}">
                                        @lang("app.resolved")</option>
                                    <option @selected($ticket->status == 'closed') value="closed"
                                        data-content="<i class='fa fa-circle mr-2 text-blue'></i>{{ __('app.closed') }}">
                                        @lang('app.closed')</option>
                                </x-forms.select>
                            </div>
                            <div class="more-filter-items">
                                <x-forms.label class="my-3" fieldId="tags"
                                    :fieldLabel="__('modules.tickets.tags')">
                                </x-forms.label>
                                <input type="text" name="tags" id="tags" class="rounded f-14"
                                    value="{{ implode(',', $ticket->ticketTags->pluck('tag_name')->toArray()) }}">
                            </div>
                        </div>
                        <!-- TICKET FILTERS END -->
                        <!-- TICKET UPDATE START -->
                        <div id="updateTicketFormSubmit" class="ticket-update bg-white px-4 py-3">
                            <x-forms.button-primary class="ml-none d-flex submit-ticket-2 fixed-bottom">
                                @lang('app.update')
                            </x-forms.button-primary>
                        </div>
                        <!-- TICKET UPDATE END -->
                    </x-form>
                </div>
                @endif
                <!-- DETAILS END -->
                <!-- CONTACT START -->
                <div @class([
                    'tab-pane fade',
                    'show active' => !$canEditTicket
                ])
                class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                    <!-- CONTACT OWNER START  -->
                    <div class="card-horizontal bg-white-shade ticket-contact-owner p-4 rounded-0">
                        <div class="card-img mr-3">
                            <img class="___class_+?88___" src="{{ $ticket->requester->image_url }}"
                                alt="{{ $ticket->requester->name }}">
                        </div>
                        <div class="card-body border-0 p-0 w-100">
                            <h4 class="card-title f-14 font-weight-normal mb-0">
                                <a class="text-dark-grey" @if ($ticket->requester->hasRole('employee'))
                                    href="{{ route('employees.show', $ticket->requester->id) }}"
                                @else
                                    href="{{ route('clients.show', $ticket->requester->id) }}"
                                @endif>
                                {{ $ticket->requester->name }}
                                </a>
                            </h4>
                            @if ($ticket->requester->country_id)
                                <span class="card-text f-12 text-dark-grey  d-flex align-items-center">
                                    <span class='flag-icon flag-icon-{{ strtolower($ticket->requester->country->iso) }} mr-2'></span>
                                    {{ $ticket->requester->country->nicename }}
                                </span>
                            @else
                                --
                            @endif

                        </div>
                    </div>
                    <!-- CONTACT OWNER END  -->
                    <!-- TICKET CHART START  -->
                    <x-cards.data :title="__('app.menu.tickets')" padding="false" class="pb-3">
                        @if (array_sum($ticketChart['values']) > 0)
                            <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen" data-chart-id="ticket-chart" data-chart-data="{{ json_encode($ticketChart) }}"><i class="fas fa-expand float-right mr-3"></i></a>
                        @endif
                        <x-pie-chart id="ticket-chart" :labels="$ticketChart['labels']" :values="$ticketChart['values']"
                            :colors="$ticketChart['colors']" height="200" width="200" />
                    </x-cards.data>
                    <!-- TICKET CHART END  -->
                    <!-- RECENT TICKETS START -->
                    <div class="card pt-4 px-4 border-grey border-left-0 border-right-0 rounded-0">
                        <div class="card-title">
                            <h4 class="f-18 f-w-500  mb-3">@lang('modules.tickets.recentTickets')</h4>
                        </div>
                        <!-- CHART START -->
                        <div class="card-body p-0">
                            <div class="recent-ticket position-relative" data-menu-vertical="1" data-menu-scroll="1"
                                data-menu-dropdown-timeout="500" id="recentTickets">
                                <div class="recent-ticket-inner position-relative">
                                    @foreach ($ticket->requester->tickets as $item)
                                        <div class="r-t-items d-flex">
                                            <div class="r-t-items-left text-lightest f-21">
                                                <i class="fa fa-ticket-alt"></i>
                                            </div>
                                            <div class="r-t-items-right ">
                                                <h3 class="f-14 font-weight-bold">
                                                    <a class="text-dark"
                                                        href="{{ route('tickets.show', $item->ticket_number) }}">{{ $item->subject }}</a>
                                                </h3>
                                                <span class="d-flex mb-1">
                                                    <span class="mr-3 f-w-500 text-dark-grey">#{{ $item->ticket_number }}</span>
                                                    @if ($item->status == 'open')
                                                        @php
                                                            $statusColor = 'red';
                                                        @endphp
                                                    @elseif($item->status == 'pending')
                                                        @php
                                                            $statusColor = 'yellow';
                                                        @endphp
                                                    @elseif($item->status == 'resolved')
                                                        @php
                                                            $statusColor = 'dark-green';
                                                        @endphp
                                                    @elseif($item->status == 'closed')
                                                        @php
                                                            $statusColor = 'blue';
                                                        @endphp
                                                    @endif
                                                    <span class="f-13 text-darkest-grey ">
                                                        <x-status :color="$statusColor" :value="$item->status" />
                                                    </span>

                                                </span>
                                                <p class="f-12 text-dark-grey">
                                                    {{ $item->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                                                </p>
                                            </div>
                                        </div><!-- item end -->
                                    @endforeach

                                </div>
                            </div>
                        </div>
                        <!-- CHART END -->
                    </div>
                    <!-- RECENT TICKETS END -->
                </div>
            <!-- CONTACT END -->

                <div class="tab-pane fade" id="nav-other" role="tabpanel" aria-labelledby="nav-other-tab">
                    @if ($ticket->project)
                        <div class="p-4 w-100 position-relative border-bottom">
                            @lang('app.project') : <a
                            href="{{ route('projects.show', $ticket->project_id) }}">{{ $ticket->project->project_name }}</a>
                        </div>
                    @endif

                    <x-form id="updateOther">
                        <!-- TICKET FILTERS START -->
                        <div class="ticket-filters p-4 w-100 position-relative border-bottom">
                            <x-forms.custom-field-show :fields="$fields" :model="$ticket"></x-forms.custom-field-show>
                        </div>
                        <!-- TICKET FILTERS END -->
                    </x-form>
                </div>

                <div class="tab-pane fade h-100" id="nav-activity" role="tabpanel" aria-labelledby="nav-activity-tab">
                    <!-- Activity TICKETS START -->
                    <div class="card pt-4 pl-4 border-grey border-left-0 border-right-0 rounded-0 h-100">
                        <div class="card-title">
                            <h4 class="f-18 f-w-500  mb-3">@lang('app.ticketActivity')</h4>
                        </div>
                        <!-- CHART START -->
                        <div class="card-body p-0">
                            <div class="ticket-activity ticket-overflow position-relative h-100" data-menu-vertical="1" data-menu-scroll="1"
                                data-menu-dropdown-timeout="500" id="ticketActivity">
                                <div class="recent-ticket-inner position-relative">
                                    @foreach ($ticket->activities as $activity)
                                    @if (!in_array('client', user_roles()) || $activity->type != 'note')
                                        <div class="r-t-items d-flex">
                                            <div class="r-t-items-left text-lightest f-21">
                                                <i class="fa fa-ticket-alt"></i>
                                            </div>
                                            <div class="r-t-items-right pl-2 w-100">
                                                <h3 class="f-14 font-weight-bold">
                                                    {{ $activity->user->name }}
                                                </h3>
                                                <span class="d-flex mb-1">
                                                    <span class="mr-3 f-w-500 text-dark-grey">
                                                        {{ $activity->details }}
                                                    </span>
                                                </span>
                                                <p class="f-12 text-dark-grey">
                                                    {{ $activity->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                                                </p>
                                            </div>
                                        </div><!-- item end -->
                                    @endif
                                    @endforeach

                                </div>
                            </div>
                        </div>
                        <!-- CHART END -->
                    </div>
                    <!-- Activity TICKETS END -->
                </div>
            </div>
        </div>
        <!-- TICKET RIGHT END -->

    </div>
<!-- TICKET END -->

@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/tagify.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // quillImageLoad('#description');
            // quillImageLoad('#description2');

            const atValues = @json($userData);
            quillMention(atValues, '#description');
            quillMention(atValues, '#description2');
        });

        $("#user_id").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        $('.note-button').click(function() {
            $('#reply-section-action').toggleClass('d-none d-flex');
            $('#note-section-action-2').toggleClass('d-none flex-row');
            $('#note-section').removeClass('d-none');
            window.scrollTo(0, document.body.scrollHeight);
            setViewHeight();
        });

        $('.reply-button').click(function() {
            $('#reply-section-action').toggleClass('d-none d-flex');
            $('#reply-section-action-2').toggleClass('d-none flex-row');
            $('#reply-section').removeClass('d-none');
            window.scrollTo(0, document.body.scrollHeight);
            setViewHeight();
        });

        $('#cancel-reply').click(function() {
            $('#reply-section-action').toggleClass('d-none d-flex');
            $('#reply-section-action-2').toggleClass('d-none flex-row');
            $('#reply-section').addClass('d-none');
            window.scrollTo(0, document.body.scrollHeight);
            setViewHeight();
        });

        $('#cancel-note').click(function() {
            $('#reply-section-action').toggleClass('d-none d-flex');
            $('#note-section-action-2').toggleClass('d-none flex-row');
            $('#note-section').addClass('d-none');
            window.scrollTo(0, document.body.scrollHeight);
            setViewHeight();
        });

        $('#add-file').click(function() {
            $('.upload-section').removeClass('d-none');
            $('#add-file').addClass('d-none');
            window.scrollTo(0, document.body.scrollHeight);
        });

        $('#add-note-file').click(function() {
            $('.upload-note-section').removeClass('d-none');
            $(this).addClass('d-none');
            window.scrollTo(0, document.body.scrollHeight);
        });

        var input = document.querySelector('input[name=tags]'),
            // init Tagify script on the above inputs
            tagify = new Tagify(input);
            Dropzone.autoDiscover = false;

        //Dropzone class
        ticketDropzone = new Dropzone("div#ticket-file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('ticket-files.store') }}",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            paramName: "file",
            maxFilesize: DROPZONE_MAX_FILESIZE,
            maxFiles: DROPZONE_MAX_FILES,
            autoProcessQueue: false,
            uploadMultiple: true,
            addRemoveLinks: true,
            parallelUploads: DROPZONE_MAX_FILES,
            acceptedFiles: DROPZONE_FILE_ALLOW,
            init: function() {
                ticketDropzone = this;
            }
        });
        ticketDropzone.on('sending', function(file, xhr, formData) {
            var ids = $('#ticket_reply_id').val();
            formData.append('ticket_reply_id', ids);
            formData.append('ticket_id', '{{ $ticket->id }}');
            $.easyBlockUI();
        });
        ticketDropzone.on('uploadprogress', function() {
            $.easyBlockUI();
        });
        ticketDropzone.on('queuecomplete', function() {
            var msgs = "@lang('messages.addDiscussion')";
            window.location.href = "{{ route('tickets.show', $ticket->ticket_number) }}";
        });
        ticketDropzone.on('removedfile', function () {
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).removeClass("has-error");
            $(label).removeClass("is-invalid");
        });
        ticketDropzone.on('error', function (file, message) {
            ticketDropzone.removeFile(file);
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).find(".help-block").remove();
            var helpBlockContainer = $(grp);

            if (helpBlockContainer.length == 0) {
                helpBlockContainer = $(grp);
            }

            helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
            $(grp).addClass("has-error");
            $(label).addClass("is-invalid");

        });

        ticketNoteDropzone = new Dropzone("div#ticket-note-file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('ticket-files.store') }}",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            paramName: "file",
            maxFilesize: DROPZONE_MAX_FILESIZE,
            maxFiles: DROPZONE_MAX_FILES,
            autoProcessQueue: false,
            uploadMultiple: true,
            addRemoveLinks: true,
            parallelUploads: DROPZONE_MAX_FILES,
            acceptedFiles: DROPZONE_FILE_ALLOW,
            init: function() {
                ticketNoteDropzone = this;
            }
        });
        ticketNoteDropzone.on('sending', function(file, xhr, formData) {
            let ids = $('#ticket_reply_id').val();
            let type = $('#type').val();
            formData.append('ticket_reply_id', ids);
            formData.append('ticket_id', '{{ $ticket->id }}');
            formData.append('type', type);
        });
        ticketNoteDropzone.on('uploadprogress', function() {
            $.easyBlockUI();
        });
        ticketNoteDropzone.on('queuecomplete', function() {
            var msgs = "@lang('messages.addDiscussion')";
            window.location.href = "{{ route('tickets.show', $ticket->ticket_number) }}";
        });
        ticketNoteDropzone.on('removedfile', function () {
            var grp = $('div#note-file-upload-dropzone').closest(".form-group");
            var label = $('div#note-file-upload-box').siblings("label");
            $(grp).removeClass("has-error");
            $(label).removeClass("is-invalid");
        });
        ticketNoteDropzone.on('error', function (file, message) {
            ticketNoteDropzone.removeFile(file);
            var grp = $('div#note-file-upload-dropzone').closest(".form-group");
            var label = $('div#note-file-upload-box').siblings("label");
            $(grp).find(".help-block").remove();
            var helpBlockContainer = $(grp);

            if (helpBlockContainer.length == 0) {
                helpBlockContainer = $(grp);
            }

            helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
            $(grp).addClass("has-error");
            $(label).addClass("is-invalid");
        });

        $('.submit-ticket').click(function() {
            var note = document.getElementById('description').children[0].innerHTML;
            document.getElementById('description-text').value = note;

            var note2 = document.getElementById('description2').children[0].innerHTML;
            document.getElementById('description-text2').value = note2;

            var status = $(this).data('status');
            $('#status').val(status);
            var type = $(this).data('type');
            $('#type').val(type);
            var userId = $('#user_id').val();

            const url = "{{ route('tickets.update', $ticket->id) }}";

            $.easyAjax({
                url: url,
                container: '#note-section',
                type: "POST",
                blockUI: true,
                data: $('#updateTicket2').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        if (ticketDropzone.getQueuedFiles().length > 0) {
                            $('#ticket_reply_id').val(response.reply_id);
                            ticketDropzone.processQueue();
                        } else if (ticketNoteDropzone.getQueuedFiles().length > 0) {
                            $('#ticket_reply_id').val(response.reply_id);
                            ticketNoteDropzone.processQueue();
                        } else {
                            window.location.href = "{{ route('tickets.show', $ticket->ticket_number) }}";
                        }
                    }
                }
            });
        });

        $('.submit-ticket-2').click(function() {

            $.easyAjax({
                url: "{{ route('tickets.update_other_data', $ticket->id) }}",
                container: '#updateTicket1',
                type: "POST",
                blockUI: true,
                disableButton: true,
                buttonSelector: ".submit-ticket-2",
                data: $('#updateTicket1').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        var status = $('#ticket-status').val();

                        ($('#ticket-status').val() != 'closed') ? $('#ticket-closed').show() :  $('#ticket-closed').hide();

                        switch (status) {
                            case 'open':
                                var statusHtml =
                                    '<i class="fa fa-circle mr-2 text-red"></i>@lang("app.open")';
                                break;
                            case 'pending':
                                var statusHtml =
                                    '<i class="fa fa-circle mr-2 text-yellow"></i>@lang("app.pending")';
                                break;
                            case 'resolved':
                                var statusHtml =
                                    '<i class="fa fa-circle mr-2 text-dark-green"></i>@lang("app.resolved")';
                                break;
                            case 'closed':
                                var statusHtml =
                                    '<i class="fa fa-circle mr-2 text-blue"></i>@lang("app.closed")';
                                break;

                            default:
                                var statusHtml =
                                    '<i class="fa fa-circle mr-2 text-red"></i>@lang("app.open")';
                                break;
                        }
                        $('#ticketStatusBadge').html(statusHtml);
                    }
                }
            })
        });


        $('.apply-template').click(function() {
            var templateId = $(this).data('template-id');

            $.easyAjax({
                url: "{{ route('replyTemplates.fetchTemplate') }}",
                data: {
                    templateId: templateId
                },
                success: function(response) {
                    if (response.status == "success") {
                        var container = $('#description').get(0);
                        var quill = new Quill(container);
                        quill.clipboard.dangerouslyPasteHTML(0, response.replyText);
                    }
                }
            })
        })

        $('body').on('click', '#cancel-notes', function() {
            var messageId = $(this).closest('.card').attr('id');
            var id = messageId.replace('message-', '');
            $('#' + messageId + ' .note-message').removeClass('d-none');
            $('#' + messageId + ' .edit-note-message').addClass('d-none');
        });

        $('body').on('click', '.edit-message', function() {
            var messageId = $(this).closest('.card').attr('id');
            var id = messageId.replace('message-', '');
            $('#' + messageId + ' .note-message').addClass('d-none');
            $('#' + messageId + ' .edit-note-message').removeClass('d-none');

            destory_editor('#description-note'+id);
            quillImageLoad('#description-note'+id);
            var editNoteDropzoneId = 'div#edit-note-file-upload-dropzone-'+id+'';
            editNoteDropzone = new Dropzone(editNoteDropzoneId, {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('ticket-files.store') }}",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            paramName: "file",
            maxFilesize: DROPZONE_MAX_FILESIZE,
            maxFiles: DROPZONE_MAX_FILES,
            autoProcessQueue: false,
            uploadMultiple: true,
            addRemoveLinks: true,
            parallelUploads: DROPZONE_MAX_FILES,
            acceptedFiles: DROPZONE_FILE_ALLOW,
            init: function() {
                editNoteDropzone = this;
            }
        });

        editNoteDropzone.on('sending', function(file, xhr, formData) {
            let ids = $('#ticket_reply_id').val();

            let type = $('#type').val();
            formData.append('ticket_reply_id', ids);
            formData.append('ticket_id', '{{ $ticket->id }}');
            formData.append('type', type);
        });

        editNoteDropzone.on('uploadprogress', function() {
            $.easyBlockUI();
        });
        editNoteDropzone.on('queuecomplete', function() {
            var msgs = "@lang('messages.addDiscussion')";
            window.location.href = "{{ route('tickets.show', $ticket->ticket_number) }}";
        });
        editNoteDropzone.on('removedfile', function () {
            var grp = $('div#edit-note-file-upload-dropzone').closest(".form-group");
            var label = $('div#edit-note-file-upload-box').siblings("label");
            $(grp).removeClass("has-error");
            $(label).removeClass("is-invalid");
        });
        editNoteDropzone.on('error', function (file, message) {
            editNoteDropzone.removeFile(file);
            var grp = $('div#edit-note-file-upload-dropzone').closest(".form-group");
            var label = $('div#edit-note-file-upload-box').siblings("label");
            $(grp).find(".help-block").remove();
            var helpBlockContainer = $(grp);

            if (helpBlockContainer.length == 0) {
                helpBlockContainer = $(grp);
            }

            helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
            $(grp).addClass("has-error");
            $(label).addClass("is-invalid");
        });

        });

        $('body').on('click', '#edit-note-file', function() {
            $('.upload-edit-note-section').removeClass('d-none');
            $('.edit-ticket-file').addClass('d-none');
        });

        $('body').on('click', '#save-notes', function() {
            var messageId = $(this).closest('.card').attr('id');
            var id = messageId.replace('message-', '');

            url = '{{ route("ticket-replies.edit_note", ":id") }}';
            url = url.replace(':id', id);
            var formId = 'text-'+id+'';

            var editedMessage = $('#description-note' + id)[0].children[0].innerHTML;

            var token = '{{ csrf_token() }}';

            $.easyAjax({
                type: 'POST',
                container: formId,
                url: url,
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-notes",
                data: {
                    '_token': token,
                    'messageId': messageId,
                    'editedMessage': editedMessage,
                    'id': id,
                },
                success: function(response) {
                    if (response.status == "success") {
                        if (editNoteDropzone.getQueuedFiles().length > 0) {
                            $('#ticket_reply_id').val(id);
                            editNoteDropzone.processQueue();
                        }

                        $('#text-'+id+'').addClass('d-none');
                        $('#' + messageId + ' .notified-message').removeClass('d-none');
                        $('#' + messageId + ' .note-message').children('span').html(editedMessage);
                        $('#' + messageId + ' .note-message').removeClass('d-none');
                    }
                },
                error: function(xhr, status, error) {
                    // Handle errors if any
                }
            });
        });

        $('body').on('click', '.delete-file', function() {
            var id = $(this).data('row-id');
            var replyFile = $(this);
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('ticket-files.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                replyFile.closest('.card').remove();
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '.delete-ticket', function() {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('tickets.destroy', $ticket->id) }}";

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                window.location.href =
                                    "{{ route('tickets.index') }}";
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '.delete-message', function() {
            var id = $(this).data('row-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('ticket-replies.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                $('#message-' + id).remove();
                            }
                        }
                    });
                }
            });
        });

        /* open add agent modal */
        $('body').on('click', '#addAgent', function() {
            var url = "{{ route('ticket-agents.create') }}";
            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        $('body').on('click', '#addChannel', function() {
            var url = "{{ route('ticketChannels.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* open add agent modal */
        $('body').on('click', '#addTicketType', function() {
            var url = "{{ route('ticketTypes.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });


        function scrollToBottom(divId) {
            var myDiv = document.getElementById(divId);
            myDiv.scrollTop = myDiv.scrollHeight;
        }

        scrollToBottom('ticketMsg');

        getAgents($('#group_id').val());

        function getAgents(groupId,exceptThis=null ){
            var url = "{{ route('tickets.agent_group', [':id', ':exceptThis']).'?ticketNumber='.$ticket->ticket_number}}";
            url = url.replace(':id', groupId);
            url = url.replace(':exceptThis', exceptThis);
            
            $.easyAjax({
                url: url,
                type: "GET",
                // data: ticket_number,
                success: function(response)
                {
                    var options = [];
                    var rData = [];
                    if($.isArray(response.data))
                    {
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var selectData = '';
                            options.push(value);
                        });
                        $('#agent_id').html('<option value="">--</option>' + options);
                    }
                    else
                    {
                        $('#agent_id').html(response.data);
                    }
                    $('#agent_id').selectpicker('refresh');
                }
            });
        }

        $('#group_id').change(function(){
            var id = $(this).val();
            getAgents(id)
        });

        $('#manage-groups').click(function() {
            var url = "{{ route('ticket-groups.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        function setViewHeight() {
            let otherBodyHeight = $('#header').outerHeight() + $('.filter-box').outerHeight();

            // document.getElementById("ticket-detail-contact").style.height = "calc(100vh - " + (
            //     $('#tabs').outerHeight() + otherBodyHeight
            // ) + "px)";
            document.getElementById("ticketMsg").style.height = "calc(100vh - " + (
                otherBodyHeight + $('#ticket-info-bar').outerHeight() +
                $('#ticketMsgBottom').outerHeight()
            ) + "px)";

            if (document.getElementById("updateTicketForm")) {
                document.getElementById("updateTicketForm").style.height = "calc(100vh - " + (
                    $('#tabs').outerHeight() + otherBodyHeight +
                    ($('#reply-section-action').outerHeight() ? $('#reply-section-action').outerHeight() : $('#reply-section-action-2').outerHeight())
                ) + "px)";
            }
        }

        $('body').on('click', '#tabs', function() {
            setViewHeight();
        });

        setViewHeight();
        window.addEventListener('resize', function(event) {
            setViewHeight();
        }, true);

    </script>
@endpush
