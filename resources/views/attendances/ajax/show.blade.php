@php
$addAttendancePermission = user()->permission('add_attendance');
$editAttendancePermission = user()->permission('edit_attendance');
$deleteAttendancePermission = user()->permission('delete_attendance');
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.attendanceDetails')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>

<div class="modal-body bg-grey">
    <div class="row">
        <div class="col-md-12 mb-4">
            <x-cards.user :image="$attendance->user->image_url">
                <div class="row">
                    <div class="col-12">
                        <h4 class="card-title f-15 f-w-500 text-darkest-grey mb-0">
                            <a href="{{ route('employees.show', [$attendance->user->id]) }}"
                                class="text-darkest-grey">{{ $attendance->user->name }}  @if(user() && user()->id == $attendance->user->id) <span class='ml-2 badge badge-secondary'> @lang('app.itsYou')</span> @endif </a>

                            @isset($attendance->user->country)
                                <x-flag :country="$attendance->user->country" />
                            @endisset
                        </h4>
                        <p class="mb-0 f-13 text-dark-grey">
                            {{ (!is_null($attendance->user->employeeDetail) && !is_null($attendance->user->employeeDetail->designation)) ? $attendance->user->employeeDetail->designation->name : ' ' }}
                        </p>
                    </div>
                </div>
            </x-cards.user>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <x-cards.data :title="__('app.date').' - '.$attendanceDate->translatedFormat(company()->date_format) .' ('.$attendanceDate->translatedFormat('l').')'">
                <div class="punch-status">
                    <div class="border rounded p-3 mb-3 bg-light">
                        <h6 class="f-13">@lang('modules.attendance.clock_in')</h6>
                        <p class="mb-0">{{ $startTime->translatedFormat(company()->time_format) }}</p>
                    </div>
                    <div class="punch-info">
                        <div class="punch-hours f-13">
                            <span>{{ $totalTime }}</span>
                        </div>
                    </div>
                    <div class="border rounded p-3 bg-light">
                        <h6 class="f-13">@lang('modules.attendance.clock_out')</h6>
                        <p class="mb-0">{{ $endTime != '' ? $endTime->translatedFormat(company()->time_format) : '' }}
                            @if (isset($notClockedOut))
                                (@lang('modules.attendance.notClockOut'))
                            @endif
                        </p>
                    </div>
                    <input type="hidden" id="date" value="{{ $attendanceDate }}">

                </div>
            </x-cards.data>
        </div>
        <div class="col-md-6">

            <x-cards.data :title="__('modules.employees.activity')">
                @if ($addAttendancePermission == 'all' && $maxClockIn)
                    <x-slot name="action">
                        <a class="btn-primary rounded f-12 py-1 px-2" href="javascript:;" onclick="addAttendance({{ $attendance->user->id }})" data-attendance-id="{{ $attendance->user->id }}">@lang('app.add')</a>
                    </x-slot>
                @endif

                <div class="recent-activity">
                    @foreach ($attendanceActivity->reverse() as $item)
                        <div class="row res-activity-box" id="timelogBox{{ $item->aId }}">
                            <ul class="res-activity-list col-md-9">
                                <li>
                                    <p class="mb-0">@lang('modules.attendance.clock_in')
                                        @if (!is_null($item->employee_shift_id))
                                            @if ($item->shift->shift_name != 'Day Off')
                                                <span class="badge badge-info ml-2" style="background-color: {{ $item->shift->color }}">{{ $item->shift->shift_name }}</span>
                                            @else
                                                <span class="badge badge-secondary ml-2" >{{ __('modules.attendance.' . str($attendanceSettings->shift_name)->camel()) }}</span>
                                            @endif
                                        @endif
                                    </p>
                                    <p class="res-activity-time">
                                        <i class="fa fa-clock"></i>
                                        {{ $item->clock_in_time->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}

                                        @if ($item->work_from_type != '')
                                            @if ($item->work_from_type == 'other')
                                                <i class="fa fa-map-marker-alt ml-2"></i>
                                                {{ $item->location }} {{ $item->working_from != '' ? '(' . $item->working_from . ')' : ''  }}
                                            @else
                                                <i class="fa fa-map-marker-alt ml-2"></i>
                                                {{ $item->location }} ({{$item->work_from_type}})
                                            @endif
                                        @endif

                                        @if ($item->late == 'yes')
                                            <i class="fa fa-exclamation-triangle ml-2"></i>
                                            @lang('modules.attendance.late')
                                        @endif

                                        @if ($item->half_day == 'yes')
                                            <i class="fa fa-sign-out-alt ml-2"></i>
                                            @lang('modules.attendance.halfDay')
                                            <span>
                                                @if($item->half_day_type == 'first_half')
                                                    ( @lang('modules.leaves.1stHalf') )
                                                @elseif ($item->half_day_type == 'second_half')
                                                    ( @lang('modules.leaves.2ndHalf') )
                                                @else

                                                @endif
                                            </span>
                                        @endif


                                        @if ($item->latitude != '' && $item->longitude != '')

                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }}%2C{{ $item->longitude }}" target="_blank">
                                            <i class="fa fa-map-marked-alt ml-2"></i> @lang('modules.attendance.showOnMap')</a>
                                        @endif
                                    </p>
                                </li>
                                <li>
                                    <p class="mb-0">@lang('modules.attendance.clock_out')</p>
                                    <p class="res-activity-time">
                                        <i class="fa fa-clock"></i>
                                        @if (!is_null($item->clock_out_time))
                                            {{ $item->clock_out_time->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                                            @if($item->auto_clock_out)
                                                <i class="fa fa-sign-out-alt ml-2"></i>
                                                @lang('modules.attendance.autoClockOut')
                                            @endif
                                        @else
                                            @lang('modules.attendance.notClockOut')
                                        @endif
                                    </p>
                                </li>
                            </ul>

                            <div class="col-md-3 text-right">
                                <div class="dropdown ml-auto comment-action">
                                    @if ($editAttendancePermission == 'all'
                                        || ($addAttendancePermission == 'all')
                                        || ($editAttendancePermission == 'added' && $item->added_by == user()->id)
                                        || ($editAttendancePermission == 'owned' && $attendance->user->id == user()->id)
                                        || ($editAttendancePermission == 'both' && ($item->added_by == user()->id || $attendance->user->id == user()->id))
                                        || $deleteAttendancePermission == 'all'
                                        || ($deleteAttendancePermission == 'added' && $item->added_by == user()->id)
                                        || ($deleteAttendancePermission == 'owned' && $attendance->user->id == user()->id)
                                        || ($deleteAttendancePermission == 'both' && ($item->added_by == user()->id || $attendance->user->id == user()->id))
                                    )
                                    <button
                                        class="btn btn-lg f-14 py-0 text-lightest  rounded  dropdown-toggle"
                                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0 mr-2"
                                        aria-labelledby="dropdownMenuLink" tabindex="0">

                                        @if ($editAttendancePermission == 'all'
                                            || ($editAttendancePermission == 'added' && $item->added_by == user()->id)
                                            || ($editAttendancePermission == 'owned' && $attendance->user->id == user()->id)
                                            || ($editAttendancePermission == 'both' && ($item->added_by == user()->id || $attendance->user->id == user()->id))
                                            )
                                            <a class="dropdown-item d-block text-dark-grey f-13 py-1 px-3"
                                                href="javascript:;" onclick="editAttendance({{ $item->aId }})"
                                                data-attendance-id="{{ $item->aId }}">@lang('app.edit')</a>
                                        @endif

                                        @if ($deleteAttendancePermission == 'all'
                                            || ($deleteAttendancePermission == 'added' && $item->added_by == user()->id)
                                            || ($deleteAttendancePermission == 'owned' && $attendance->user->id == user()->id)
                                            || ($deleteAttendancePermission == 'both' && ($item->added_by == user()->id || $attendance->user->id == user()->id))
                                            )
                                            <a class="cursor-pointer dropdown-item d-block text-dark-grey f-13 pb-1 px-3"
                                                onclick="deleteAttendance({{ $item->aId }})"
                                                data-attendance-id="{{ $item->aId }}"
                                                href="javascript:;">@lang('app.delete')</a>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @endforeach

                </div>
            </x-cards.data>
        </div>
    </div>

</div>
<script>

    function addAttendance(userID) {
            var date = $('#date').val();
            const attendanceDate = date.split("-");
            let dayTime = attendanceDate[2];
            dayTime = dayTime.split(' ');
            let day = dayTime[0];
            let month = attendanceDate[1];
            let year = attendanceDate[0];

            var url = "{{ route('attendances.add-user-attendance', [':userid', ':day', ':month', ':year']) }}";
            url = url.replace(':userid', userID);
            url = url.replace(':day', day);
            url = url.replace(':month', month);
            url = url.replace(':year', year);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        }

    function deleteAttendance(id) {
        var url = "{{ route('attendances.destroy', ':id') }}";
        url = url.replace(':id', id);
        var token = "{{ csrf_token() }}";

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
                            $(MODAL_XL).modal('hide');
                        }
                    }
                });
            }
        });

    }

</script>
