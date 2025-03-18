<script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>
<style>
    .card-img {
        width: 120px;
        height: 120px;
    }

    .card-img img {
        width: 120px;
        height: 120px;
        object-fit: cover;
    }
    .appreciation-count {
        top: -6px;
        right: 10px;
    }

</style>
@php

$showFullProfile = false;
$employeeDetail = $employee->employeeDetail;
if ($viewPermission == 'all'
    || ($viewPermission == 'added' && $employeeDetail->added_by == user()->id)
    || ($viewPermission == 'owned' && $employeeDetail->user_id == user()->id)
    || ($viewPermission == 'both' && ($employeeDetail->user_id == user()->id || $employeeDetail->added_by == user()->id))
) {
    $showFullProfile = true;
}

@endphp

@php
$editEmployeePermission = user()->permission('edit_employees');
$viewAppreciationPermission = user()->permission('view_appreciation');
@endphp

<div class="d-lg-flex">

    <div class="w-100 py-0 py-lg-3 py-md-0">
        <!-- ROW START -->
        <div class="row">
            <!--  USER CARDS START -->
            <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">
                <div class="row">
                    <div class="col-xl-7 col-md-6 mb-4 mb-lg-0">

                        <x-cards.user :image="$employee->image_url">
                            <div class="row">
                                <div class="col-10">
                                    <h4 class="card-title f-15 f-w-500 text-darkest-grey mb-0">
                                        {{ $employee->name_salutation }}
                                        @isset($employee->country)
                                            <x-flag :country="$employee->country" />
                                        @endisset
                                    </h4>
                                </div>
                                @if ($editEmployeePermission == 'all' || ($editEmployeePermission == 'added' && $employee->employeeDetail->added_by == user()->id))
                                    <div class="col-2 text-right">
                                        <div class="dropdown">
                                            <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle"
                                                type="button" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                                <i class="fa fa-ellipsis-h"></i>
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                                aria-labelledby="dropdownMenuLink" tabindex="0">
                                                <a class="dropdown-item openRightModal"
                                                    href="{{ route('employees.edit', $employee->id) }}">@lang('app.edit')</a>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            </div>

                            <p class="f-12 font-weight-normal text-dark-grey mb-0">
                                {{ !is_null($employee->employeeDetail) && !is_null($employee->employeeDetail->designation) ? $employee->employeeDetail->designation->name : '' }}
                                &bull;
                                {{ isset($employee->employeeDetail) && !is_null($employee->employeeDetail->department) && !is_null($employee->employeeDetail->department) ? $employee->employeeDetail->department->team_name : '' }}
                                 <span class="card-text f-12 text-dark-grey m-lg-2">| {{__('app.role')}}: {{$employee->roles()->withoutGlobalScopes()->latest()->first()->display_name}}</span>
                            </p>


                                <p class="card-text f-11 text-lightest mb-1">@lang('app.lastLogin')

                                    @if (!is_null($employee->last_login))
                                        {{ $employee->last_login->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                                    @else
                                        --
                                    @endif
                                </p>

                            @if ($employee->status != 'active')

                                <p class="card-text f-12 text-dark-grey">
                                    <x-status :value="__('app.inactive')" color="red" />
                                </p>
                            @endif

                            @if ($showFullProfile)
                                <div class="card-footer bg-white border-top-grey pl-0">
                                    <div class="d-flex flex-wrap justify-content-between">
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 "
                                                for="usr">@lang('app.openTasks')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $employee->open_tasks_count }}</p>
                                        </span>
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 "
                                                for="usr">@lang('app.menu.projects')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $employee->member_count }}</p>
                                        </span>
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 "
                                                for="usr">@lang('modules.employees.hoursLogged')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $hoursLogged }}</p>
                                        </span>
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 "
                                                for="usr">@lang('app.menu.tickets')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $employee->agents_count }}</p>
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </x-cards.user>

                        @if ($employee->employeeDetail->about_me != '')
                            <x-cards.data :title="__('app.about')" class="mt-4">
                                <div>{{ $employee->employeeDetail->about_me }}</div>
                            </x-cards.data>
                        @endif


                        <x-cards.data :title="__('modules.client.profileInfo')" class=" mt-4">
                            <x-cards.data-row :label="__('modules.employees.employeeId')"
                                :value="(!is_null($employee->employeeDetail) && !is_null($employee->employeeDetail->employee_id)) ? ($employee->employeeDetail->employee_id) : '--'" />

                            <x-cards.data-row :label="__('modules.employees.fullName')"
                                :value="$employee->name" />

                            <x-cards.data-row :label="__('app.designation')"
                                :value="(!is_null($employee->employeeDetail) && !is_null($employee->employeeDetail->designation)) ? ($employee->employeeDetail->designation->name) : '--'" />

                            <x-cards.data-row :label="__('app.department')"
                                :value="(isset($employee->employeeDetail) && !is_null($employee->employeeDetail->department) && !is_null($employee->employeeDetail->department)) ? ($employee->employeeDetail->department->team_name) : '--'" />

                            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                    @lang('modules.employees.gender')</p>
                                <p class="mb-0 text-dark-grey f-14 w-70">
                                    <x-gender :gender='$employee->gender' />
                                </p>
                            </div>

                            @php
                                $currentyearJoiningDate = \Carbon\Carbon::parse(now(company()->timezone)->year.'-'.$employee->employeeDetail->joining_date->translatedFormat('m-d'));
                                if ($currentyearJoiningDate->copy()->endOfDay()->isPast()) {
                                    $currentyearJoiningDate = $currentyearJoiningDate->addYear();
                                }
                                $diffInHoursJoiningDate = now(company()->timezone)->floatDiffInHours($currentyearJoiningDate, false);

                                $currentDay = \Carbon\Carbon::parse(now(company()->timezone)->toDateTimeString())->startOfDay()->setTimezone('UTC');
                                $joiningDay = $employee->employeeDetail->joining_date;

                                $totalWorkYears = $joiningDay->copy()->diffInYears($currentDay);
                                $totalWorkMonths = $joiningDay->copy()->diffInMonths($currentDay);
                            @endphp

                        <x-cards.data-row
                            :label="__('modules.employees.workAnniversary')"
                            :value="!is_null($employee->employeeDetail) && !is_null($employee->employeeDetail->joining_date)
                                ? (
                                    ($diffInHoursJoiningDate > -23 && $diffInHoursJoiningDate <= 0 && $totalWorkYears == 0 )
                                    ? __('modules.dashboard.joinedToday')
                                    : (
                                        ($totalWorkYears > 0 && $totalWorkMonths % 12 == 0)
                                        ? __('app.completed') . ' ' . $totalWorkYears . ' ' . __('app.year')
                                        : $currentyearJoiningDate->longRelativeToNowDiffForHumans()
                                    )
                                )
                                : '--'"
                        />

                            <x-cards.data-row :label="__('modules.employees.dateOfBirth')"
                                              :value="(!is_null($employee->employeeDetail) && !is_null($employee->employeeDetail->date_of_birth)) ? $employee->employeeDetail->date_of_birth->translatedFormat('d F') : '--'" />

                            @if ($showFullProfile)
                                <x-cards.data-row :label="__('app.email')" :value="$employee->email" />

                                <x-cards.data-row :label="__('app.mobile')"
                                :value="$employee->mobile_with_phonecode" />

                                <x-cards.data-row :label="__('modules.employees.slackUsername')"
                                    :value="(isset($employee->employeeDetail) && !is_null($employee->employeeDetail->slack_username)) ? '@'.$employee->employeeDetail->slack_username : '--'" />

                                <x-cards.data-row :label="__('modules.employees.hourlyRate')"
                                    :value="(!is_null($employee->employeeDetail)) ? company()->currency->currency_symbol.$employee->employeeDetail->hourly_rate : '--'" />

                                <x-cards.data-row :label="__('app.address')"
                                    :value="$employee->employeeDetail->address ?? '--'" />

                                <x-cards.data-row :label="__('app.skills')"
                                    :value="$employee->skills() ? implode(', ', $employee->skills()) : '--'" />

                                <x-cards.data-row :label="__('app.language')"
                                    :value="$employeeLanguage->language_name ?? '--'" />

                                <x-cards.data-row :label="__('modules.employees.probationEndDate')"
                                :value="$employee->employeeDetail->probation_end_date ? Carbon\Carbon::parse($employee->employeeDetail->probation_end_date)->translatedFormat(company()->date_format) : '--'" />

                                <x-cards.data-row :label="__('modules.employees.noticePeriodStartDate')"
                                :value="$employee->employeeDetail->notice_period_start_date ? Carbon\Carbon::parse($employee->employeeDetail->notice_period_start_date)->translatedFormat(company()->date_format) : '--'" />

                                <x-cards.data-row :label="__('modules.employees.noticePeriodEndDate')"
                                :value="$employee->employeeDetail->notice_period_end_date ? Carbon\Carbon::parse($employee->employeeDetail->notice_period_end_date)->translatedFormat(company()->date_format) : '--'" />

                                <x-cards.data-row :label="__('modules.employees.maritalStatus')"
                                :value="$employee?->employeeDetail?->marital_status ? $employee->employeeDetail->marital_status->label() : '--'" />

                                <x-cards.data-row :label="__('app.menu.businessAddresses')" :value="$companyAddress ? $companyAddress->location : '--'" />

                                <x-cards.data-row :label="__('modules.employees.marriageAnniversaryDate')"
                                :value="$employee->employeeDetail->marriage_anniversary_date ? Carbon\Carbon::parse($employee->employeeDetail->marriage_anniversary_date)->translatedFormat('d F') : '--'" />

                                <x-cards.data-row :label="__('modules.employees.employmentType')"
                                :value="$employee?->employeeDetail?->employment_type ? __('modules.employees.' . $employee?->employeeDetail?->employment_type) : '--'" />

                                @if($employee->employeeDetail->employment_type == 'internship')
                                    <x-cards.data-row :label="__('modules.employees.internshipEndDate')"
                                    :value="$employee->employeeDetail->internship_end_date ? Carbon\Carbon::parse($employee->employeeDetail->internship_end_date)->translatedFormat(company()->date_format) : '--'" />
                                @endif

                                @if($employee->employeeDetail->employment_type == 'on_contract')
                                    <x-cards.data-row :label="__('modules.employees.contractEndDate')"
                                    :value="$employee->employeeDetail->contract_end_date ? Carbon\Carbon::parse($employee->employeeDetail->contract_end_date)->translatedFormat(company()->date_format) : '--'" />
                                @endif

                                <x-cards.data-row :label="__('modules.employees.joiningDate')"
                                :value="(!is_null($employee->employeeDetail) && !is_null($employee->employeeDetail->joining_date)) ? $employee->employeeDetail->joining_date->translatedFormat(company()->date_format) : '--'" />

                                <x-cards.data-row :label="__('modules.employees.lastDate')"
                                :value="(!is_null($employee->employeeDetail) && !is_null($employee->employeeDetail->last_date)) ? $employee->employeeDetail->last_date->translatedFormat(company()->date_format) : '--'" />


                                {{-- Custom fields data --}}
                                <x-forms.custom-field-show :fields="$fields" :model="$employee->employeeDetail"></x-forms.custom-field-show>

                            @endif

                        </x-cards.data>


                    </div>

                    <div class="col-xl-5 col-lg-6 col-md-6">

                        @if ($showFullProfile)
                            <x-cards.data class="mb-4" :title="__('modules.appreciations.appreciation')">
                                @forelse ($employee->appreciationsGrouped as $item)
                                <div class="float-left position-relative mb-2" style="width: 50px" data-toggle="tooltip" data-original-title="@if(isset($item->award->title)){{  $item->award->title }} @endif">
                                    @if(isset($item->award->awardIcon->icon))
                                        <x-award-icon :award="$item->award" />
                                    @endif
                                    <span class="position-absolute badge badge-secondary rounded-circle border-additional-grey appreciation-count">{{ $item->no_of_awards }}</span>
                                </div>
                                @empty
                                    <x-cards.no-record icon="medal" :message="__('messages.noRecordFound')" />
                                @endforelse
                            </x-cards.data>
                        @endif

                        <x-cards.data class="mb-4">
                            <div class="d-flex justify-content-between">
                                    <div class="col-6">
                                        <p class="f-14 text-dark-grey">@lang('modules.employees.reportingTo')</p>
                                        @if ($employee->employeeDetail->reportingTo)
                                            <x-employee :user="$employee->employeeDetail->reportingTo" />
                                        @else
                                        --
                                        @endif
                                    </div>

                                @if ($employee->reportingTeam)
                                    <div class="col-6">
                                        <p class="f-14 text-dark-grey">@lang('modules.employees.reportingTeam')</p>
                                        @if (count($employee->reportingTeam) > 0)
                                            @if (count($employee->reportingTeam) > 1)
                                                @foreach ($employee->reportingTeam as $item)
                                                    <div class="taskEmployeeImg rounded-circle mr-1">
                                                        <a href="{{ route('employees.show', $item->user->id) }}">
                                                            <img data-toggle="tooltip" data-original-title="{{ $item->user->name }}"
                                                                src="{{ $item->user->image_url }}">
                                                        </a>
                                                    </div>
                                                @endforeach
                                            @else
                                                @foreach ($employee->reportingTeam as $item)
                                                    <x-employee :user="$item->user" />
                                                @endforeach
                                            @endif

                                        @else
                                            --
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </x-cards.data>

                        @if ($showFullProfile)
                            <div class="row">
                                @if (in_array('attendance', user_modules()))
                                    <div class="col-xl-6 col-sm-12 mb-4">
                                        <x-cards.widget :title="__('modules.dashboard.lateAttendanceMark')"
                                            :value="$lateAttendance" :info="__('modules.dashboard.thisMonth')"
                                            icon="map-marker-alt" />
                                    </div>
                                @endif
                                @if (in_array('leaves', user_modules()))
                                    <div class="col-xl-6 col-sm-12 mb-4">
                                        <x-cards.widget :title="__('modules.dashboard.leavesTaken')" :value="$leavesTaken"
                                            :info="__('modules.dashboard.thisMonth')" icon="sign-out-alt" />
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                @if (in_array('tasks', user_modules()))
                                    <div class="col-md-12 mb-4">
                                        <x-cards.data :title="__('app.menu.tasks')" padding="false" class="pb-3">
                                            @if (array_sum($taskChart['values']) > 0)
                                                <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen" data-chart-id="task-chart" data-chart-data="{{ json_encode($taskChart) }}"><i class="fas fa-expand float-right mr-3"></i></a>
                                            @endif
                                            <x-pie-chart id="task-chart" :labels="$taskChart['labels']"
                                                :values="$taskChart['values']" :colors="$taskChart['colors']" height="250"
                                                width="250" />
                                        </x-cards.data>
                                    </div>
                                @endif
                                @if (in_array('tickets', user_modules()))
                                    <div class="col-md-12 mb-4">
                                        <x-cards.data :title="__('app.menu.tickets')" padding="false" class="pb-3">
                                            @if (array_sum($ticketChart['values']) > 0)
                                                <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen" data-chart-id="ticket-chart" data-chart-data="{{ json_encode($ticketChart) }}"><i class="fas fa-expand float-right mr-3"></i></a>
                                            @endif
                                            <x-pie-chart id="ticket-chart" :labels="$ticketChart['labels']"
                                                :values="$ticketChart['values']" :colors="$ticketChart['colors']"
                                                height="250" width="250" />
                                        </x-cards.data>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </div>
            <!--  USER CARDS END -->

        </div>
        <!-- ROW END -->
    </div>
</div>
