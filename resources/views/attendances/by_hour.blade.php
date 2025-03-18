@extends('layouts.app')

@push('styles')
    <style>
        .attendance-total {
            width: 10%;
        }

    </style>
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <div class="select-box d-flex py-2 pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.employee')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="user_id" id="user_id" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($employees as $item)
                        <x-user-option :user="$item" :selected="request('employee_id') == $item->id"/>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($viewAttendancePermission == 'owned')
            <input type="hidden" name="department" id="department" value="all">
            <input type="hidden" name="designation" id="designation" value="all">
        @else
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.department')</p>
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
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.designation')</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="designation" id="designation" data-live-search="true"
                            data-size="8">
                        <option value="all">@lang('app.all')</option>
                        @foreach ($designations as $designation)
                            <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.month')</p>
            <div class="select-status">


                <select class="form-control select-picker" name="month" id="month" data-live-search="true" data-size="8">
                    <x-forms.months :selectedMonth="$month"  fieldRequired="true"/>
                </select>
            </div>
        </div>

        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.year')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="year" id="year" data-live-search="true" data-size="8">
                    @for ($i = $year; $i >= $year - 4; $i--)
                        <option @if ($i == $year) selected @endif value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

    </x-filters.filter-box>

@endsection

@php
$addAttendancePermission = user()->permission('add_attendance');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper px-4">

        <div class="d-grid d-lg-flex d-md-flex action-bar">

            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addAttendancePermission == 'all' || $addAttendancePermission == 'added')
                    <x-forms.link-primary :link="route('attendances.create')"
                        data-redirect-url="{{ route('attendances.by_member') }}" class="mr-3 openRightModal float-left"
                        icon="plus">
                        @lang('modules.attendance.markAttendance')
                    </x-forms.link-primary>
                    @if (canDataTableExport())
                        <x-forms.button-secondary id="export-all" class="mr-3 mb-2 mb-lg-0" icon="file-export">
                            @lang('app.exportExcel')
                        </x-forms.button-secondary>
                    @endif
                @endif

                @if ($addAttendancePermission == 'all' || $addAttendancePermission == 'added')
                    <x-forms.link-secondary :link="route('attendances.import')" class="mr-3 openRightModal float-left d-none d-lg-block"
                        icon="file-upload">
                        @lang('app.importExcel')
                    </x-forms.link-secondary>
                @endif
            </div>

            <div class="btn-group mt-2 mt-lg-0 mt-md-0 ml-0 ml-lg-3 ml-md-3" role="group">
                <a href="{{ route('attendances.index') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                    data-original-title="@lang('app.summary')"><i class="side-icon bi bi-list-ul"></i></a>

                <a href="{{ route('attendances.by_member') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                    data-original-title="@lang('modules.attendance.attendanceByMember')"><i
                        class="side-icon bi bi-person"></i></a>

                <a href="{{ route('attendances.by_hour') }}" class="btn btn-secondary f-14 btn-active"
                    data-toggle="tooltip" data-original-title="@lang('modules.attendance.attendanceByHour')"><i
                        class="fa fa-clock"></i></a>

                @if (attendance_setting()->save_current_location)
                    <a href="{{ route('attendances.by_map_location') }}" class="btn btn-secondary f-14"
                        data-toggle="tooltip" data-original-title="@lang('modules.attendance.attendanceByLocation')"><i
                            class="fa fa-map-marked-alt"></i></a>
                @endif

            </div>
        </div>

        <!-- Task Box Start -->
        <x-cards.data class="mt-2">
            <div class="row">
                <div class="col-md-12">
                    <span class="f-w-500 mr-1">@lang('app.note'):</span> <i class="fa fa-star text-warning"></i> <i
                        class="fa fa-arrow-right text-lightest f-11 mx-1"></i> @lang('app.menu.holiday') &nbsp;|&nbsp;<i
                        class="fa fa-calendar-week text-red"></i> <i class="fa fa-arrow-right text-lightest f-11 mx-1"></i>
                    @lang('modules.attendance.dayOff') &nbsp;|&nbsp; <i
                        class="fa fa-times text-lightest"></i> <i class="fa fa-arrow-right text-lightest f-11 mx-1"></i>
                    @lang('modules.attendance.absent')
                </div>
            </div>

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
        $('#user_id, #department, #month, #year, #designation').on('change', function() {
            if ($('#user_id').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else if ($('#department').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }  else if ($('#designation').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else if ($('#month').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else if ($('#year').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else {
                $('#reset-filters').addClass('d-none');
                showTable();
            }
        });

        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        function showTable(loading = true) {

            var year = $('#year').val();
            var month = $('#month').val();

            var userId = $('#user_id').val();
            var department = $('#department').val();
            var designation = $('#designation').val();

            //refresh counts
            var url = "{{ route('attendances.by_hour') }}";

            var token = "{{ csrf_token() }}";

            $.easyAjax({
                data: {
                    '_token': token,
                    year: year,
                    month: month,
                    department: department,
                    designation: designation,
                    userId: userId
                },
                url: url,
                blockUI: loading,
                container: '.content-wrapper',
                success: function(response) {
                    $('#attendance-data').html(response.data);
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

        $('#attendance-data').on('click', '.edit-attendance', function(event) {
            var attendanceDate = $(this).data('attendance-date');
            var userData = $(this).closest('tr').children('td:first');
            var userID = $(this).data('user-id');
            var year = $('#year').val();
            var month = $('#month').val();

            var url = "{{ route('attendances.mark', [':userid', ':day', ':month', ':year']) }}";
            url = url.replace(':userid', userID);
            url = url.replace(':day', attendanceDate);
            url = url.replace(':month', month);
            url = url.replace(':year', year);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        function editAttendance(id) {
            var url = "{{ route('attendances.edit', [':id']) }}";
            url = url.replace(':id', id);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        }

        showTable(false);
        @if (canDataTableExport())
            $('#export-all').click(function() {
                var year = $('#year').val();
                var month = $('#month').val();
                var department = $('#department').val();
                var designation = $('#designation').val();
                var userId = $('#user_id').val();

                var url =
                    "{{ route('attendances.export_all_attendance', [':year', ':month', ':userId', ':department', ':designation']) }}";
                url = url.replace(':year', year).replace(':month', month).replace(':userId', userId).replace(':department', department).replace(':designation', designation);
                window.location.href = url;

            });
        @endif
    </script>
@endpush
