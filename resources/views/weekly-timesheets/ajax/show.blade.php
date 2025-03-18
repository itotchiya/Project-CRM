<div class="row">
    <div class="col-sm-12">
        <x-cards.data :title="__('app.menu.weeklyTimesheets') . ' ' . __('app.details')" class=" mt-4">
                <x-slot name="action">

                    <div class="d-flex">
                        @if ($weeklyTimesheet->status == 'draft')
                            <x-forms.link-secondary :link="route('weekly-timesheets.edit', $weeklyTimesheet->id)" class="mr-2" icon="fa fa-edit">@lang('app.edit')</x-forms.link-secondary>
                        @endif


                        @if ($weeklyTimesheet->status == 'pending' && (in_array('admin', user_roles()) || $weeklyTimesheet->user->employeeDetails->reporting_to == user()->id))
                            <x-forms.button-secondary type="button" class="change-timesheet-status  mr-2" data-status="draft" data-timesheet-id="{{ $weeklyTimesheet->id }}" icon="fa fa-times" >@lang('app.reject')</x-forms.button-secondary>

                            <x-forms.button-primary type="button" class="change-timesheet-status" data-status="approved" data-timesheet-id="{{ $weeklyTimesheet->id }}" icon="fa fa-check" >@lang('app.approve')</x-forms.button-primary>
                        @else
                        <span @class([
                            'badge p-2 f-16',
                            'badge-warning' => $weeklyTimesheet && $weeklyTimesheet->status == 'pending',
                            'badge-success' => $weeklyTimesheet && $weeklyTimesheet->status == 'approved',
                            'badge-danger' => (($weeklyTimesheet && $weeklyTimesheet->status == 'draft') || !$weeklyTimesheet),
                        ])>{{ $weeklyTimesheet ? __('app.' . $weeklyTimesheet->status) : __('app.draft') }}</span>
                        @endif

                    </div>
                </x-slot>


            <div class="table-responsive">

                <div class="d-flex">
                    <div class="text-muted f-12">
                       @lang('modules.timeLogs.submittedBy') <x-employee :user="$weeklyTimesheet->user" />
                    </div>
                    @if ($weeklyTimesheet->approvedBy)
                        <div class="ml-4 text-muted f-12">
                            @lang('modules.expenses.approvedBy') <x-employee :user="$weeklyTimesheet->approvedBy" />
                        </div>
                    @endif
                </div>
                
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
        
                    @if($weeklyTimesheet)
                        @foreach($weeklyTimesheet->entries->groupBy('task_id') as $key => $entries)
                        <tr>
                            <td class="px-1 employee-td fixed-column bg-white">
                                <a href="{{ route('tasks.show', $entries->first()->task->id) }}" class="text-darkest-grey openRightModal">{{ $entries->first()->task->heading }}</a>
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
                                <td class="p-2 date-index-{{ $key2 }}" data-index="{{ $key2 }}">
                                    {{ $entry->hours }} @lang('app.hrs')
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
        
                
                    @endif
        
                    <x-slot name="tfoot">
                        <tr>
                            <td class="font-weight-semibold f-16">
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
            </div>

        </x-cards.data>
    </div>
</div>
<script>

    $('.change-timesheet-status').on('click', function() {
        let status = $(this).data('status');
        let timesheetId = $(this).data('timesheet-id');

        if(status == 'draft'){
            let searchQuery = "?status=" + status + "&timesheet_id=" + timesheetId;
            let url = "{{ route('weekly-timesheets.show_reject_modal') }}" + searchQuery;

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        }else{

            var url = "{{ route('weekly-timesheets.change_status') }}";

            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.changeWeeklyTimesheetStatusConfirmation')",
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
                        type: 'POST',
                        url: url,
                        blockUI: true,
                        data: {
                            'status': status,
                            'timesheetId': timesheetId,
                            '_token': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status == 'success') {
                                window.location.reload(); 
                            }
                        }
                    });
                }
            });
        }

    });
</script>
