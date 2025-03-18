<div class="d-flex justify-content-between">
    <div class="flex-lg-shrink-0">
        <div class="row">
            <div class="select-status col">
                <select class="form-control select-picker" name="year" id="change-year" data-size="8">
                    @for ($i = $year+1; $i >= $year - 4; $i--)
                        <option @if ($i == $year) selected @endif value="{{ $i }}">
                            {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="select-status col d-lg-none">
                <select class="form-control select-picker" name="month" id="change-month" data-live-search="true" data-size="8">
                    <x-forms.months :selectedMonth="$month" fieldRequired="true"/>
                </select>
            </div>

            @foreach(\App\Models\GlobalSetting::getMonthsOfYear('M') as $key=>$monthOfYear)
                <button class="f-12 p-2 px-3 bg-white col change-month d-none d-lg-block {{ $month == $key+1 ? 'btn-primary rounded' : '' }}" type="button" data-month="{{$key+1}}">{{$monthOfYear}}</button>
            @endforeach

        </div>

    </div>
    <div class="align-self-center ml-3">
        @foreach ($employeeShifts as $item)
            <span class="badge badge-info f-11 p-1" style="background-color: {{ $item->color }}">
                {{ $item->shift_short_code }} : {{ $item->shift_name }}</span>
            {{ !$loop->last ? ' | ' : '' }}
        @endforeach
       | <i class="fa fa-star text-primary"></i> : @lang('app.menu.holiday')
    </div>
</div>


<div class="table-responsive">
    <x-table class="table-bordered mt-3 table-hover" headType="thead-light">
        <x-slot name="thead">
            <th class="px-2" style="vertical-align: middle;">@lang('app.employee')</th>
            @for ($i = 1; $i <= $daysInMonth; $i++)
                <th class="px-1">{{ $i }} <br> <span class="text-lightest f-11 text-uppercase">{{ $weekMap[\Carbon\Carbon::parse(\Carbon\Carbon::parse($i . '-' . $month . '-' . $year))->dayOfWeek] }}</span></th>
            @endfor
        </x-slot>

        @foreach ($employeeAttendence as $key => $attendance)
            @php
                $userId = explode('#', $key);
                $userId = $userId[0];
                $isActive = '';

                if(in_array($userId,$employeeIdsInactive)){
                    $isActive = 'no';
                }
            @endphp
            <tr>
                <td class="w-30 px-2"> {!! end($attendance) !!} </td>
                @foreach ($attendance as $key2 => $day)
                    @if ($key2 + 1 <= count($attendance))
                        @php
                            $attendanceDate = \Carbon\Carbon::parse($year.'-'.$month.'-'.$key2);
                        @endphp
                        <td class="px-1">
                            @if ($day == 'Leave')
                            <span data-toggle="tooltip" data-original-title="@lang('modules.attendance.leave')"><i
                                    class="fa fa-plane-departure text-red"></i></span>
                        @elseif ($day == 'Half Day')
                            @if ($attendanceDate->isFuture())
                                <span data-toggle="tooltip" data-original-title="@lang('modules.attendance.halfDay')"><i
                                    class="fa fa-star-half-alt text-red"></i></span>
                            @else
                                <a href="javascript:;" class="change-shift{{$isActive}}" data-user-id="{{ $userId }}"
                                        data-attendance-date="{{ $key2 }}">
                                    <span data-toggle="tooltip" data-original-title="@lang('modules.attendance.halfDay')"><i
                                            class="fa fa-star-half-alt text-red"></i></span>
                                </a>
                            @endif
                            @elseif ($day == 'EMPTY')
                                <button type="button" class="change-shift{{$isActive}} badge badge-light f-10 p-1 border"  data-user-id="{{ $userId }}"
                                    data-attendance-date="{{ $key2 }}">
                                    @if (in_array($manageEmployeeShifts, ['all']))
                                    <i class="fa fa-plus"></i>
                                    @else
                                    <i class="fa fa-ban"></i>
                                    @endif
                                </button>
                            @elseif ($day == 'Holiday')
                                <a href="javascript:;" data-toggle="tooltip" class="change-shift{{$isActive}}"
                                    data-original-title="{{ $holidayOccasions[$key2] }}"
                                    data-user-id="{{ $userId }}" data-attendance-date="{{ $key2 }}"><i
                                        class="fa fa-star text-primary"></i></a>
                            @else
                                {!! $day !!}
                            @endif
                        </td>
                    @endif
                @endforeach
            </tr>
        @endforeach
    </x-table>
</div>
