@extends('layouts.app')

@push('styles')
    <style>
        .table .thead-light th,
        .table tr td,
        .table h5 {
            font-size: 12px;
        }
        .shift-request-change-count {
            left: 28px;
            top: -9px !important;
        }

        .change-shift {
            padding: 1rem 0.25rem !important;
        }

        .change-shiftno {
            padding: 1rem 0.25rem !important;
        }

        #week-end-date, #week-start-date {
            z-index: 0;
        }

        #attendance-data .form-control:focus, .form-control:hover {
            border-color: #e9ecef;
        }
</style>

    @if ($manageEmployeeShifts != 'all')
        <style>
            .change-shift, .change-shift-week {
                cursor: unset !important;
            }
        </style>
    @endif
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <div class="select-box d-flex py-2 pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.employee')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="user_id" id="user_id" data-live-search="true"
                        data-size="8">
                    @if ($employees->count() > 1 || in_array('admin', user_roles()))
                        <option value="all">@lang('app.all')</option>
                    @endif
                    @forelse ($employees as $item)
                        <x-user-option :user="$item" :selected="request('employee_id') == $item->id"/>
                    @empty
                        <x-user-option :user="user()"/>
                    @endforelse
                </select>
            </div>
        </div>

        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.menu.department')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="department" id="department" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->team_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <div class="select-status">
                <select class="form-control select-picker" name="view_type" id="view_type" data-live-search="true"
                    data-size="8">
                    <option value="week">@lang('app.weeklyView')</option>
                    <option value="month">@lang('app.monthlyView')</option>
                </select>
            </div>
        </div>

        <input type="hidden" name="month" id="month" value="{{ $month }}">
        <input type="hidden" name="year" id="year" value="{{ $year }}">
        <input type="hidden" name="week_start_date" id="week_start_date" value="{{ now(company()->timezone)->toDateString() }}">

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

    </x-filters.filter-box>
@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper px-4">

        <div class="d-grid d-lg-flex d-md-flex action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($manageEmployeeShifts == 'all')
                    <x-forms.link-primary :link="route('shifts.create')" class="mr-3 openRightModal float-left"
                    icon="plus">
                        @lang('modules.attendance.bulkShiftAssign')
                    </x-forms.link-primary>
                @endif
                @if (canDataTableExport())
                    <x-forms.button-secondary id="export-all" class="mr-3 mb-2 mb-lg-0" icon="file-export">
                        @lang('app.exportExcel')
                    </x-forms.button-secondary>
                @endif
            </div>

            <div class="btn-group mt-2 mt-lg-0 mt-md-0 ml-0 ml-lg-3 ml-md-3" role="group">
                <a href="{{ route('shifts.index') }}" class="btn btn-secondary f-14 btn-active" data-toggle="tooltip"
                    data-original-title="@lang('app.summary')"><i class="side-icon bi bi-list-ul"></i></a>
                @if ($manageEmployeeShifts == 'all')
                    <a href="{{ route('shifts-change.index') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                        data-original-title="@lang('modules.attendance.shiftChangeRequests')"><i
                            class="side-icon bi bi-hourglass-split"></i>
                        @if ($employeeShiftChangeRequest->request_count > 0)
                            <span
                                class="badge badge-primary shift-request-change-count position-absolute">{{ $employeeShiftChangeRequest->request_count }}</span>
                        @endif
                    </a>
                @endif

            </div>

        </div>

        <!-- Task Box Start -->
        <x-cards.data class="mt-3">
            <div class="row">
                <div class="col-md-12" id="attendance-data"></div>
            </div>
        </x-cards.data>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    <script>
        var manageEmployeeShiftPermission = "{{ $manageEmployeeShifts }}";

        $('#user_id, #department, #view_type').on('change', function() {
            if ($('#user_id').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else if ($('#department').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else {
                $('#reset-filters').addClass('d-none');
                showTable();
            }
        });

        $('#attendance-data').on('click', '.change-month', function() {
            $("#month").val($(this).data('month'));
            showTable();
        });

        $('#attendance-data').on('change', '#change-month', function() {
            $("#month").val($(this).val());
            showTable();
        });

        $('#attendance-data').on('change', '#change-year', function() {
            $("#year").val($(this).val());
            showTable();
        });

        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });


        $('#attendance-data').on('click', '#week-start-date', function() {
            $("#week_start_date").val($(this).data('date'));
            showTable();
        });

        $('#attendance-data').on('click', '#week-end-date', function() {
            $("#week_start_date").val($(this).data('date'));
            showTable();
        });

        function showTable(loading = true) {

            var year = $('#year').val();
            var month = $('#month').val();
            var weekStartDate = $('#week_start_date').val();

            var userId = $('#user_id').val();
            var department = $('#department').val();
            var viewType = $('#view_type').val();

            //refresh counts
            var url = "{{ route('shifts.index') }}";

            var token = "{{ csrf_token() }}";

            $.easyAjax({
                data: {
                    '_token': token,
                    year: year,
                    month: month,
                    department: department,
                    userId: userId,
                    view_type: viewType,
                    week_start_date: weekStartDate,
                },
                url: url,
                blockUI: loading,
                container: '.content-wrapper',
                success: function(response) {
                    $('#attendance-data').html(response.data);
                    $('#attendance-data #change-year').selectpicker("refresh");
                    $('#attendance-data #change-month').selectpicker("refresh");
                }
            });

        }

        $('#attendance-data').on('click', '.view-attendance', function() {
            var attendanceID = $(this).data('attendance-id');
            var url = "{{ route('attendances.show', ':attendanceID') }}";
            url = url.replace(':attendanceID', attendanceID);

            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        if (manageEmployeeShiftPermission == 'all') {
            $('#attendance-data').on('click', '.change-shift', function(event) {
                var attendanceDate = $(this).data('attendance-date');
                var userData = $(this).closest('tr').children('td:first');
                var userID = $(this).data('user-id');
                var year = $('#year').val();
                var month = $('#month').val();

                var url = "{{ route('shifts.mark', [':userid', ':day', ':month', ':year']) }}";
                url = url.replace(':userid', userID);
                url = url.replace(':day', attendanceDate);
                url = url.replace(':month', month);
                url = url.replace(':year', year);

                $(MODAL_DEFAULT + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_DEFAULT, url);
            });

            $('#attendance-data').on('click', '.change-shift-week', function(event) {
                var attendanceDate = $(this).data('attendance-date');
                var splitAttendance = attendanceDate.split('-');
                attendanceDate = splitAttendance[2];
                var userData = $(this).closest('tr').children('td:first');
                var userID = $(this).data('user-id');
                var year = splitAttendance[0];
                var month = splitAttendance[1];

                var url = "{{ route('shifts.mark', [':userid', ':day', ':month', ':year']) }}";
                url = url.replace(':userid', userID);
                url = url.replace(':day', attendanceDate);
                url = url.replace(':month', month);
                url = url.replace(':year', year);

                $(MODAL_DEFAULT + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_DEFAULT, url);
            });
        }

        showTable(false);

        @if (canDataTableExport())
            $('#export-all').click(function() {
                var year = $('#year').val();
                var month = $('#month').val();
                var department = $('#department').val();
                var userId = $('#user_id').val();
                var startDate = $('#week_start_date').val();
                var viewType = $('#view_type').val();

                var url =
                    "{{ route('shifts.export_all', [':year', ':month', ':userId', ':department', ':startDate', ':viewType']) }}";
                url = url.replace(':year', year).replace(':month', month).replace(':userId', userId).replace(':department', department).replace(':startDate', startDate).replace(':viewType', viewType);
                window.location.href = url;

            });
        @endif

        $('body').on('click', '.approve-request', function() {
            var id = $(this).data('request-id');
            var url = "{{ route('shifts-change.approve_request', ':id') }}";
            url = url.replace(':id', id);
            var token = '{{ csrf_token() }}';
            $.easyAjax({
                url: url,
                type: "POST",
                blockUI: true,
                container: '.content-wrapper',
                data: {
                    id: id,
                    _token: token
                },
                success: function(data) {
                    showTable();
                    $(MODAL_DEFAULT).modal('hide');
                }
            })

        });

        $('body').on('click', '.decline-request', function() {
            var id = $(this).data('request-id');
            var url = "{{ route('shifts-change.decline_request', ':id') }}";
            url = url.replace(':id', id);
            var token = '{{ csrf_token() }}';
            $.easyAjax({
                url: url,
                type: "POST",
                blockUI: true,
                container: '.content-wrapper',
                data: {
                    id: id,
                    _token: token
                },
                success: function(data) {
                    showTable();
                    $(MODAL_DEFAULT).modal('hide');
                }
            })

        });
    </script>
@endpush
