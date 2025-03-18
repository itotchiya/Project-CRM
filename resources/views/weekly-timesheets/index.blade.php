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

        #week-end-date, #week-start-date {
            z-index: 0;
        }

        .hours-td div {
            width: 70px;
        }

        .hours-td input {
            width: 70px;
            text-align: center;
        }

        .employee-td select {
            width: 240px !important;
        }

        .employee-td:hover > .work-setting-icon {
            display: inline-block;
        }

        .week-task {
            padding: 0.375rem 2.25rem 0.375rem 0.75rem;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            appearance: none;
        }

        #weekly-timesheet-table .form-group {
            margin-bottom: 0;
        }

        @media screen and (min-width: 1200px) {
            .fixed-column {
                position: sticky;
                left: 0;
                /* z-index: 1; Ensures the sticky column is above horizontally scrolled content */
                box-shadow: 4px 0 5px -2px rgba(0,0,0,0.2); /* Adds shadow to the right side */
                z-index: 10;
            }
        }

    </style>


@endpush


@section('content')
    <!-- CONTENT WRAPPER START -->


    <div class="px-4 content-wrapper">


        <div class="d-lg-flex d-md-flex d-block my-3 justify-content-between action-bar">
            
            <div class="d-flex align-items-center">
                <h4 class="mb-0">@lang('modules.timeLogs.addWeeklyTimesheet')</h4>

                @if ((user()->reportingTeam->count() > 0 && !in_array('admin', user_roles())) || in_array('admin', user_roles()))
                    <x-forms.link-secondary :link="route('weekly-timesheets.index').'?view=pending_approval'" class="ml-3" >
                        @lang('modules.timeLogs.approveTimesheet')
                        <span class="badge badge-warning ml-1">{{ $pendingApproval }}</span>
                    </x-forms.link-secondary>
                @endif
            </div>


            <div class="btn-group ml-3" role="group">
                @include('timelogs.timelog-menu')
            </div>
        </div>

        <!-- Task Box Start -->
        <x-cards.data class="mt-3">
            <div class="row">

                <input type="hidden" name="month" id="month" value="{{ $month }}">
                <input type="hidden" name="year" id="year" value="{{ $year }}">
                <input type="hidden" name="week_start_date" id="week_start_date" value="{{ now(company()->timezone)->toDateString() }}">


                <div class="col-md-12" id="attendance-data"></div>
            </div>
        </x-cards.data>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    <script>

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
            var url = "{{ route('weekly-timesheets.index') }}";

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
                   // Call functions to fetch data for all employees and departments
                    $('#attendance-data').html(response.data);
                    $('#attendance-data #change-year').selectpicker("refresh");
                    $('#attendance-data #change-month').selectpicker("refresh");

                }
            });

        }

        showTable(false);


        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');

        });



    </script>
@endpush
