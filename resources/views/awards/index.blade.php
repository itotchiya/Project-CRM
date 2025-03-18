@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush
@section('filter-section')

    <x-filters.filter-box>
        <!-- DESIGNATION START -->
        <div class="select-box d-flex px-0 border-right-grey border-right-grey-sm-0 align-items-center">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-filter-status">
                <select class="form-control select-picker" name="filter_status" id="filter_status" data-container="body">
                    <option selected value="all">@lang('app.all')</option>
                    <option value="active">@lang('app.active')</option>
                    <option value="inactive">@lang('app.inactive')</option>
                </select>
            </div>
        </div>
        <!-- DESIGNATION END -->
        <!-- SEARCH BY TASK START -->
        <div class="task-search d-flex  py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                           placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>
        <!-- SEARCH BY TASK END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

        <!-- MORE FILTERS START -->
        <!-- MORE FILTERS END -->
    </x-filters.filter-box>

@endsection
@php
$manageAppreciationTypePermission = user()->permission('manage_award');
$viewAppreciationPermission = user()->permission('view_appreciation');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-grid d-lg-flex d-md-flex action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($manageAppreciationTypePermission == 'all')
                    <x-forms.link-primary :link="route('awards.create')" class="mr-3 openRightModal float-left" icon="plus">
                        @lang('modules.appreciations.addAppreciationType')
                    </x-forms.link-primary>
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        @if ($manageAppreciationTypePermission == 'all')
                            <option value="change-leave-status">@lang('app.changeStatus')</option>
                        @endif
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        <option value="active">@lang('app.active')</option>
                        <option value="inactive">@lang('app.inactive')</option>
                    </select>
                </div>
            </x-datatable.actions>


            <div class="btn-group mt-2 mt-lg-0 mt-md-0 ml-0 ml-lg-3 ml-md-3" role="group">
                @if($viewAppreciationPermission != 'none')
                <a href="{{ route('appreciations.index') }}" class="btn btn-secondary f-14 " data-toggle="tooltip"
                   data-original-title="@lang('app.menu.appreciation')"><i class="side-icon bi bi-trophy"></i></a>

                    <a href="{{ route('awards.index') }}" class="btn btn-secondary f-14 btn-active" data-toggle="tooltip"
                       data-original-title="@lang('app.menu.award')"><i class="side-icon bi bi-award"></i></a>
                @endif
            </div>

        </div>
        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#filter_status').selectpicker();
        const showTable = () => {
            window.LaravelDataTables["appreciation-type-table"].draw(true);
        }

        $('#appreciation-type-table').on('preXhr.dt', function(e, settings, data) {
            var searchText = $('#search-text-field').val();
            var status = $('#filter_status').val();
            data['searchText'] = searchText;
            data['status'] = status;
        });

        $('#search-text-field , #filter_status').on('change keyup', function() {
            if ($('#filter_status').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else {
                $('#reset-filters').addClass('d-none');
                showTable();
            }
        });

        $('body').on('change', '.change-appreciation-status', function() {
            var id = $(this).data('appreciation-id');

            var token = "{{ csrf_token() }}";
            var status = $(this).val();

            if (typeof id !== 'undefined') {
                $.easyAjax({
                    url: "{{ route('awards.change-status') }}",
                    type: "POST",
                    data: {
                        '_token': token,
                        appreciationId: id,
                        status: status
                    },

                    success: function(response) {
                        if (response.status == "success") {
                            showTable();
                            resetActionButtons();
                            deSelectAll();
                        }
                    }
                });
            }
        });

        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
            $('.select-picker').val('all');

            $('.select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');

            showTable();
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('user-id');
            var appreciationCount = $(this).data('total-appreciation');
            var status = $(this).data('status');
            var denyButtonStatus = true;
            var alertText = "@lang('messages.recoverRecord')";

            if(status == 'inactive' || appreciationCount == 0){
                denyButtonStatus = false;
            }

            if(appreciationCount != 0){
                 alertText = "@lang('messages.recoverAwardRecord')";
                 alertText = alertText.replace(':employeeCount', appreciationCount);
            }

            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: alertText,
                icon: 'warning',
                showCancelButton: true,
                showDenyButton: denyButtonStatus,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                denyButtonText: "@lang('messages.disableIt')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary mr-3',
                    denyButton: 'btn btn-warning mr-3'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false

            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('awards.destroy', ':id') }}";
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
                else if (result.isDenied) {
                    if(status == 'active'){
                        status = 'inactive'
                    }
                    else{
                        status = 'active';
                    }
                    var url = "{{ route('awards.change-status') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            'appreciationId': id,
                            'status': status,
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

        $('#quick-action-type').change(function() {
            const actionValue = $(this).val();

            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');

                if (actionValue == 'change-leave-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        const applyQuickAction = () => {
            var rowdIds = $("#appreciation-type-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('awards.apply_quick_action') }}?row_ids=" + rowdIds;

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
                        $('.quick-action-field').addClass('d-none');
                    }
                }
            })
        };

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

    </script>
@endpush
