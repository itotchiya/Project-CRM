
<div class="row gap-4">
    <div class="col-md-4">
        <div class='input-group w-auto'>
            <div class="input-group-prepend">
                <button id="week-start-date" data-date="{{ $weekStartDate->copy()->subDay()->toDateString() }}" type="button"
                class="btn btn-outline-secondary border-grey height-35"><i class="fa fa-chevron-left"></i>
            </button>
        </div>

        <input type="text" disabled class="form-control height-35 f-14 bg-white text-center" value="{{ $weekStartDate->translatedFormat('d M') . ' - ' . $weekEndDate->translatedFormat('d M') }}">

        <div class="input-group-append">
            <button id="week-end-date" data-date="{{ $weekEndDate->copy()->addDay()->toDateString() }}" type="button"
                    class="btn btn-outline-secondary border-grey height-35"><i class="fa fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-8 text-right">
        <span @class([
            'badge p-2 f-16',
            'badge-warning' => $weekTimesheet && $weekTimesheet->status == 'pending',
            'badge-success' => $weekTimesheet && $weekTimesheet->status == 'approved',
            'badge-danger' => (($weekTimesheet && $weekTimesheet->status == 'draft') || !$weekTimesheet),
        ])>{{ $weekTimesheet ? __('app.' . $weekTimesheet->status) : __('app.draft') }}</span>
    </div>

</div>


<form action="{{ route('weekly-timesheets.store') }}" method="post" id="weekly-timesheet-form">
    <input type="hidden" name="status" id="status" value="{{ $weekTimesheet ? $weekTimesheet->status : 'draft' }}">
    @csrf
    <div class="table-responsive">
        <x-table class="table-bordered mt-3 table-hover" headType="thead-light" id="weekly-timesheet-table" >
            <x-slot name="thead">
                <th class="px-2 fixed-column font-weight-semibold f-16" style="vertical-align: middle;"><span class="f-16">@lang('app.task')</span></th>
                @foreach ($weekPeriod->toArray() as $date)
                    <th class="px-1">
                        <div class="d-flex">
                            <div class="f-27 align-self-center mr-2">{{ $date->day }}</div>
                            <div class="text-lightest f-11 text-uppercase">{{ $date->translatedFormat('l') }} <br>{{ $date->translatedFormat('M') }}</div>
                        </div>
                    </th>
                @endforeach
            </x-slot>

            @php
                $totalHours = [];
            @endphp

            @if($weekTimesheet)
                @foreach($weekTimesheet->entries->groupBy('task_id') as $key => $entries)
                <tr>
                    <td class="px-1 employee-td fixed-column bg-white z-10">
                        <div class="form-group d-flex justify-content-between">
                        <select class="form-control week-task" name="task_ids[{{ $key }}]">
                            @foreach ($tasksForWeek as $task)
                                <option value="{{ $task->id }}" {{ $key == $task->id ? 'selected' : '' }}>
                                    {{ $task->heading }}
                                </option>
                            @endforeach
                        </select>

                        @if($weekTimesheet->status == 'draft')
                            <button class="btn btn-light btn-sm remove-task" type="button">
                                <i class="fa fa-trash"></i>
                            </button>
                        @endif
                        </div>
                    </td>

                    @foreach ($entries as $key2 => $entry)
                        @php
                            $day = $entry->date->format('Y-m-d');
                        @endphp

                        @php
                            $hours = $entry->hours ?? 0;
                            if (isset($totalHours[$day])) {
                                $totalHours[$day] = $totalHours[$day] + $hours;
                            } else {
                                $totalHours[$day] = $hours;
                            }

                        @endphp
                        <td class="p-1 hours-td date-index-{{ $key2 }}" data-index="{{ $key2 }}">
                            <input type="hidden" class="week-date" name="dates[{{ $key }}][]" value="{{ $entry->date->format('Y-m-d') }}">
                            <input type="number" 
                                class="form-control week-hours height-35" 
                                name="hours[{{ $key }}][]" 
                                min="0" 
                                max="24" 
                                value="{{ $entry->hours }}" />
                        </td>
                    @endforeach
                </tr>
                @endforeach

            @else
                @php
                    $key = 0;
                @endphp

                    
                <tr>
                    <td class="px-1 employee-td fixed-column bg-white">
                        <div class="form-group d-flex justify-content-between">
                            <select class="form-control week-task" name="task_ids[{{ $key }}]">
                                @foreach ($tasksForWeek as $task)
                                    <option value="{{ $task->id }}">{{ $task->heading }}</option>
                                @endforeach
                            </select>

                            
                            <button class="btn btn-light btn-sm remove-task" type="button">
                                <i class="fa fa-trash"></i>
                            </button>

                        </div>
                    </td>
                    @foreach ($weekDates as $key2 => $day)
                        <td class="p-1 hours-td date-index-{{ $key2 }}" data-index="{{ $key2 }}">
                            <input type="hidden" class="week-date" name="dates[{{ $key }}][]" value="{{ $day }}">
                            <input type="number" class="form-control week-hours height-35" name="hours[{{ $key }}][]" min="0" max="24" value="0" />
                        </td>
                    @endforeach
                </tr>

            @endif

            <x-slot name="tfoot">
                <tr>
                    <td class="font-weight-semibold fixed-column f-16">
                        <span class="f-16">@lang('app.total')</span>
                    </td>
                    @foreach ($weekDates as $key2 => $day)
                        <td class="p-1 hours-td font-weight-semibold f-16">
                            <span class="f-16" id="total-hours-{{ $key2 }}">{{ $totalHours[$day] ?? 0 }}</span> @lang('app.hrs')
                        </td>
                    @endforeach
                </tr>
            </x-slot>
        </x-table>

        @if ($weekTimesheet && $weekTimesheet->status == 'draft' || !$weekTimesheet)
            <div class="mt-3">
                <x-forms.button-secondary type="button" class="mr-2" id="add-more-task" >@lang('app.addMore')</x-forms.button-secondary>
                <x-forms.button-primary type="button" class="submit-timesheet mr-2" data-status="draft" >@lang('app.save')</x-forms.button-primary>
                <x-forms.button-primary type="button" class="submit-timesheet mr-2" data-status="pending" >@lang('modules.timeLogs.submitForApproval')</x-forms.button-primary>
            </div>
        @endif
        
    </div>
</form>

<script>
    $(document).ready(function () {
        let entryCount = parseInt("{{$key}}") + 1;
        $('#add-more-task').click(function (e) {
            e.preventDefault();
            let $clone = $('#weekly-timesheet-table').find('tbody tr:last').clone();
            
            $clone.find('.week-task').attr('name', 'task_ids[' + entryCount + ']');
            $clone.find('.week-date').attr('name', 'dates[' + entryCount + '][]');
            $clone.find('.week-hours').attr('name', 'hours[' + entryCount + '][]').val(0);
            
            $clone.appendTo('#weekly-timesheet-table tbody');
            entryCount++;
            
            return false;
        });

        $('#weekly-timesheet-table').on('keyup', '.week-hours', function () {
            let totalHours = 0;
            let index = $(this).closest('td').data('index');

            $('#weekly-timesheet-table .date-index-' + index).each(function () {
                let currentHours = parseFloat($(this).find('.week-hours').val());
                if (currentHours > 24) {
                    $(this).find('.week-hours').val(24);
                    currentHours = 24;
                }
                if (currentHours < 0) {
                    $(this).find('.week-hours').val(0);
                    currentHours = 0;
                }
                if (isNaN(currentHours)) {
                    $(this).find('.week-hours').val(0);
                    currentHours = 0;
                }
                totalHours += parseFloat(currentHours);

            });

            $('#total-hours-' + index).text(totalHours);
            
            if (totalHours > 24) {
                $('#total-hours-' + index).html('<span class="text-danger">'+totalHours+'</span>');
            }
        });

        $('.submit-timesheet').click(function (e) {
            $('#status').val($(this).data('status'));

            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.submitWeeklyTimesheetConfirmation')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirm')",
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
                    $.easyAjax({
                        url: "{{ route('weekly-timesheets.store') }}",
                        type: 'POST',
                        container: '#weekly-timesheet-form',
                        blockUI: true,
                        buttonSelector: '#submit-timesheet',
                        disableButton: true,
                        data: $('#weekly-timesheet-form').serialize(),
                        success: function (response) {
                            console.log(response);
                        }
                    });
                }
            });

            
        });

        $('#weekly-timesheet-table').on('click', '.remove-task', function (e) {
            e.preventDefault();
            $(this).closest('tr').remove();
        });

        if($('#status').val() == 'draft') {
            $('.week-task, .week-hours').prop('disabled', false);
        } else {
            $('.week-task, .week-hours').prop('disabled', true);
        }
    });
</script>
