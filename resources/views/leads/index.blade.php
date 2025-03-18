@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    @include('leads.filters')
@endsection

@php
$addLeadPermission = user()->permission('add_deals');
$addLeadCustomFormPermission = user()->permission('manage_lead_custom_forms');
@endphp

@section('content')
<!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-grid d-lg-flex d-md-flex action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addLeadPermission == 'all' || $addLeadPermission == 'added')
                    <x-forms.link-primary :link="route('deals.create')" class="mr-3 float-left mb-2 mb-lg-0 mb-md-0 openRightModal" icon="plus">
                        @lang('modules.deal.addDeal')
                    </x-forms.link-primary>
                @endif
                @if ($addLeadPermission == 'all' || $addLeadPermission == 'added')
                    <x-forms.link-secondary :link="route('deals.import')" class="mr-3 openRightModal float-left mb-2 mb-lg-0 mb-md-0 d-none d-lg-block" icon="file-upload">
                        @lang('app.importExcel')
                    </x-forms.link-secondary>
                @endif
            </div>
            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.deal.changeStage')</option>
                        <option value="change-deal-agents">@lang('modules.deal.addDealAgents')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" id="change-stage-action" class="form-control select-picker">
                        @foreach ($stages as $st)
                            <option data-content="<i class='fa fa-circle' style='color:{{ $st->label_color }}'></i> {{ $st->name }} " value="{{ $st->id}}"> {{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-agents-action">
                    <select name="agent" id="change-deal-agent-action" class="form-control select-picker">
                        @foreach ($dealAgents as $agents)
                            <option data-content="{{ $agents->user->name }}  @if($agents->user->id == user()->id)
                                    <span class='ml-1 badge badge-secondary pr-1'>@lang('app.itsYou')</span>
                                @endif" value="{{ $agents->id}}"> {{ $agents->user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </x-datatable.actions>
            <div class="btn-group mt-2 mt-lg-0 mt-md-0 ml-0 ml-lg-3 ml-md-3" role="group">
                <a href="{{ route('deals.index') }}" class="btn btn-secondary f-14 btn-active" data-toggle="tooltip"
                    data-original-title="@lang('modules.leaves.tableView')"><i class="side-icon bi bi-list-ul"></i></a>
                <a href="{{ route('leadboards.index') }}" class="btn btn-secondary f-14" data-toggle="tooltip" data-original-title="@lang('modules.lead.kanbanboard')"><i class="side-icon bi bi-kanban"></i></a>
            </div>
        </div>
        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @include('sections.datatable_js')
    <script>
        $('#leads-table').on('preXhr.dt', function(e, settings, data) {
            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var startDate = $('#datatableRange').val();

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }
            var searchText = $('#search-text-field').val();
            var min = $('#min').val();
            var max = $('#max').val();
            var type = $('#type').val();
            var followUp = $('#followUp').val();
            var agent = $('#filter_agent_id').val();
            var category_id = $('#filter_category_id').val();
            var source_id = $('#filter_source_id').val();
            var stage_id = $('#filter_status_id').val();
            var agent_id = $('#agent_id').val();
            var date_filter_on = $('#date_filter_on').val();
            var pipeline = $('#pipeline').val();
            var category = $('#category').val();
            var product = $('#product').val();
            var deal_watcher_id = $('#deal_watcher_agent_id').val();
            var lead_agent_id = $('#lead_agent_id').val();
            data['deal_watcher_id'] = deal_watcher_id;
            data['lead_agent_id'] = lead_agent_id;
            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['searchText'] = searchText;
            data['type'] = type;
            data['followUp'] = followUp;
            data['agent'] = agent;
            data['min'] = min;
            data['max'] = max;
            data['category_id'] = category_id;
            data['source_id'] = source_id;
            data['stage_id'] = stage_id;
            data['agent'] = agent_id;
            data['date_filter_on'] = date_filter_on;
            data['pipeline'] = pipeline;
            data['category'] = category;
            data['product'] = product;
        });
        const showTable = () => {
            window.LaravelDataTables["leads-table"].draw(true);
        }
        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
            $('.filter-box #status').val('not finished');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });
        $('#reset-filters-2').click(function() {
            $('#filter-form')[0].reset();
            $('.filter-box #status').val('all');
            $('.filter-box #leave_type').val('all');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });
        $('#quick-action-type').change(function() {
            const actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else if (actionValue == 'change-agent') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-agent-action').removeClass('d-none');
                } else if (actionValue == 'change-deal-agents') {
                   $('.quick-action-field').addClass('d-none');
                   $('#change-agents-action').removeClass('d-none');
                }else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });
        $('#quick-action-apply').click(function() {
            const actionValue = $('#quick-action-type').val();
            if (actionValue == 'delete') {
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
                        applyQuickAction();
                    }
                });
            } else {
                applyQuickAction();
            }
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('id');
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
                    var url = "{{ route('deals.destroy', ':id') }}";
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
                                showTable();
                            }
                        }
                    });
                }
            });
        });
        const applyQuickAction = () => {
            var rowdIds = $("#leads-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();
            var url = "{{ route('deals.apply_quick_action') }}?row_ids=" + rowdIds;
            $.easyAjax({
                url: url,
                container: '#quick-action-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#quick-action-apply",
                data: $('#quick-action-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        showTable();
                        resetActionButtons();
                        deSelectAll();
                        $('#quick-action-form').hide();
                    }
                }
            })
        };
       function changeStage(leadID, elem) {
            var statusID = $(elem).find(':selected').attr('data-id');
            var statusSlug = $(elem).find(':selected').attr('data-slug');

            var url = "{{ route('deals.change_stage') }}";
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                type: 'POST',
                url: url,
                data: {
                    '_token': token,
                    'leadID': leadID,
                    'statusID': statusID
                },
                success: function(response) {
                    if (response.status == "success") {

                        if (statusSlug === 'win' || statusSlug === 'lost') {
                            var modalUrl = "{{ route('deals.stage_change', ':id')}}?via=deal&leadID=" + leadID + "&statusID=" + statusID;
                            modalUrl = modalUrl.replace(':id', leadID);
                            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                            $.ajaxModal(MODAL_LG, modalUrl);
                            return;
                        }
                        $.easyBlockUI('#leads-table');
                        $.easyUnblockUI('#leads-table');
                        showTable();
                        resetActionButtons();
                        deSelectAll();
                    }
                }
            });
        }
        function followUp(leadID) {
            var url = '{{ route('deals.follow_up', ':id') }}';
            url = url.replace(':id', leadID);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        }
        $('body').on('click', '#add-lead', function() {
            window.location.href = "{{ route('lead-form.index') }}";
        });
        $( document ).ready(function() {
            @if (!is_null(request('start')) && !is_null(request('end')))
            $('#datatableRange').val('{{ request('start') }}' +
            ' @lang("app.to") ' + '{{ request('end') }}');
            $('#datatableRange').data('daterangepicker').setStartDate("{{ request('start') }}");
            $('#datatableRange').data('daterangepicker').setEndDate("{{ request('end') }}");
                showTable();
            @endif
        });
    </script>
@endpush
