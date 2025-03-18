@extends('layouts.app')

@push('datatable-styles')
    <script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <!-- CLIENT START -->
        <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.employee')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="employee" id="employee_id" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($employees as $employee)
                        <x-user-option :user="$employee" />
                    @endforeach
                </select>
            </div>
        </div>
        <!-- CLIENT END -->
        <div class="select-box py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0 d-none">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.month')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="month" id="month" data-live-search="true"
                        data-size="8">
                    <x-forms.months :selectedMonth="$month" fieldRequired="true"/>
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

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">

        <!-- Add Task Export Buttons Start -->
        <div class="d-grid d-lg-flex d-md-flex action-bar">

            <div id="table-actions" class="flex-grow-1 align-items-center mb-2 mb-lg-0 mb-md-0">
                @if (canDataTableExport())
                    <x-forms.button-secondary id="export-all-leave-quota" class="mr-3 mb-2 mb-lg-0" icon="file-export">
                        @lang('app.exportExcel')
                    </x-forms.button-secondary>
                @endif
            </div>

            <div class="btn-group mt-2 mt-lg-0 mt-md-0 ml-0 ml-lg-3 ml-md-3" role="group">
                <a href="{{ route('leave-report.leave_quota') }}" class="btn btn-secondary f-14 btn-active show-leaves-quota" data-toggle="tooltip"
                    data-original-title="@lang('app.menu.leavesQuota')"><i class="side-icon bi bi-pie-chart-fill"></i></a>
                <a href="{{ route('leave-report.index') }}" class="btn btn-secondary f-14 leave-report" data-toggle="tooltip"
                    data-original-title="@lang('app.menu.leaveReport')"><i class="side-icon bi bi-list-ul"></i></a>
            </div>
        </div>


        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-4 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#leave-quota-report-table').on('preXhr.dt', function(e, settings, data) {
            var employeeId = $('#employee_id').val();
            var year = $('#year').val();
            var month = $('#month').val();

            if (!employeeId) {
                employeeId = 0;
            }

            data['employeeId'] = employeeId;
            data['year'] = year;
            data['month'] = month;
            // data['_token'] = '{{ csrf_token() }}';
        });

        const showTable = () => {
            window.LaravelDataTables["leave-quota-report-table"].draw(true);
        }

        $('#employee_id, #month, #year').on('change keyup', function() {
            if ($('#employee_id').val() != "all") {
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

        $('#leave-quota-report-table').on('click', '.view-leaves', function(event) {
            var id = $(this).data('user-id');
            var year = $('#year').val();
            var month = $('#month').val();
            var url = "{{ route('leave-report.employee-leave-quota', [':id', ':year', ':month']) }}";
            url = url.replace(':id', id);
            url = url.replace(':year', year);
            url = url.replace(':month', month);

            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        @if (canDataTableExport())
            $('#export-all-leave-quota').click(function () {
                var userId = $('#employee_id').val();
                var year = $('#year').val();
                var month = $('#month').val();
                var url =  "{{ route('leave_quota.export_all_leave_quota', [':userId', ':year', ':month']) }}";
                url = url.replace(':userId', userId);
                url = url.replace(':year', year);
                url = url.replace(':month', month);

                window.location.href = url;

            });
        @endif

    </script>
@endpush
