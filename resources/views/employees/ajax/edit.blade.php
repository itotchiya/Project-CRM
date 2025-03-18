@php
$addDesignationPermission = user()->permission('add_designation');
$changeEmployeeRolePermission = user()->permission('change_employee_role');
$addDepartmentPermission = user()->permission('add_department');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">
<style>
    .tagify_tags .height-35 {
        height: auto !important;
    }

</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.employees.accountDetails')</h4>
                <div class="row p-20">
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-md-2">
                                <x-forms.text fieldId="employee_id" :fieldLabel="__('modules.employees.employeeId')"
                                    fieldName="employee_id" :fieldValue="$employee->employeeDetail->employee_id"
                                    fieldRequired="true" :fieldPlaceholder="__('modules.employees.employeeIdInfo')" :popover="__('modules.employees.employeeIdHelp')">
                                </x-forms.text>
                            </div>
                            <div class="col-md-2">
                                <x-forms.select fieldId="salutation" fieldName="salutation"
                                    :fieldLabel="__('modules.client.salutation')">
                                    <option value="">--</option>
                                    @foreach ($salutations as $salutation)
                                        <option value="{{ $salutation->value }}" @selected($employee->salutation == $salutation)>{{ $salutation->label() }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>
                            <div class="col-md-4">
                                <x-forms.text fieldId="name" :fieldLabel="__('modules.employees.employeeName')"
                                    fieldName="name" :fieldValue="$employee->name" fieldRequired="true"
                                    :fieldPlaceholder="__('placeholders.name')">
                                </x-forms.text>
                            </div>
                            <div class="col-md-4">
                                <x-forms.text fieldId="email" :fieldLabel="__('modules.employees.employeeEmail')"
                                    fieldName="email" fieldRequired="true" :fieldValue="$employee->email"
                                    :fieldPlaceholder="__('placeholders.email')">
                                </x-forms.text>
                            </div>
                            <div class="col-md-4">
                                <x-forms.label class="mt-3" fieldId="password"
                                    :fieldLabel="__('app.password')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <input type="password" name="password" id="password" autocomplete="off"
                                        class="form-control height-35 f-14">
                                    <x-slot name="preappend">
                                        <button type="button" data-toggle="tooltip"
                                            data-original-title="@lang('app.viewPassword')"
                                            class="btn btn-outline-secondary border-grey height-35 toggle-password"><i
                                                class="fa fa-eye"></i></button>
                                    </x-slot>
                                    <x-slot name="append">
                                        <button id="random_password" type="button" data-toggle="tooltip"
                                            data-original-title="@lang('modules.client.generateRandomPassword')"
                                            class="btn btn-outline-secondary border-grey height-35"><i
                                                class="fa fa-random"></i></button>
                                    </x-slot>
                                </x-forms.input-group>
                                <small class="form-text text-muted">@lang('modules.client.passwordUpdateNote')</small>
                            </div>
                            <div class="col-md-4">
                                <x-forms.label class="my-3" fieldId="designation"
                                    :fieldLabel="__('app.designation')" fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="designation"
                                        id="employee_designation" data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($designations as $designation)
                                            <option @if ($employee->employeeDetail->designation_id == $designation->id) selected @endif value="{{ $designation->id }}">
                                                {{ $designation->name }}</option>
                                        @endforeach
                                    </select>

                                    @if ($addDesignationPermission == 'all')
                                        <x-slot name="append">
                                            <button id="designation-setting-edit" type="button"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>
                            <div class="col-md-4">
                                <x-forms.label class="my-3" fieldId="department"
                                    :fieldLabel="__('app.department')" fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="department"
                                        id="employee_department" data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($teams as $team)
                                            <option @if ($employee->employeeDetail->department_id == $team->id) selected @endif value="{{ $team->id }}">
                                                {{ $team->team_name }}</option>
                                        @endforeach
                                    </select>
                                    @if ($addDepartmentPermission == 'all')
                                        <x-slot name="append">
                                            <button id="department-edit" type="button"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">

                        <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                            :fieldLabel="__('modules.profile.profilePicture')"
                            :fieldValue="($employee->image ? $employee->masked_image_url : $employee->image_url)" fieldName="image"
                            fieldId="image" fieldHeight="119" :popover="__('messages.fileFormat.ImageFile')" />
                    </div>
                    <div class="col-md-4">
                        <x-forms.select fieldId="country" :fieldLabel="__('app.country')" fieldName="country"
                            search="true">
                            <option value="">--</option>
                            @foreach ($countries as $item)
                                <option @if ($employee->country_id == $item->id) selected @endif data-mobile="{{ $employee->mobile }}" data-tokens="{{ $item->iso3 }}" data-iso="{{ $item->iso }}" data-phonecode="{{ $item->phonecode }}" data-content="<span
                                class='flag-icon flag-icon-{{ strtolower($item->iso) }} flag-icon-squared'></span>
                            {{ $item->nicename }}" value="{{ $item->id }}">{{ $item->nicename }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-4">
                        <x-forms.label class="my-3" fieldId="mobile"
                            :fieldLabel="__('app.mobile')"></x-forms.label>
                        <x-forms.input-group style="margin-top:-4px">
                            <x-forms.select fieldId="country_phonecode" fieldName="country_phonecode"
                                search="true">
                                @foreach ($countries as $item)
                                    <option @selected($employee->country_phonecode == $item->phonecode && !is_null($item->numcode))
                                            data-tokens="{{ $item->name }}" data-country-iso="{{ $item->iso }}"
                                            data-content="{{$item->flagSpanCountryCode()}}"
                                            value="{{ $item->phonecode }}">{{ $item->phonecode }}
                                    </option>
                                @endforeach
                            </x-forms.select>
                            <input type="tel" class="form-control height-35 f-14" placeholder="@lang('placeholders.mobile')"
                                name="mobile" id="mobile" value="{{ $employee->mobile }}">
                        </x-forms.input-group>
                    </div>
                    <div class="col-md-4">
                        <x-forms.select fieldId="gender" :fieldLabel="__('modules.employees.gender')"
                            fieldName="gender">
                            <option @if ($employee->gender == 'male') selected @endif value="male">@lang('app.male')</option>
                            <option @if ($employee->gender == 'female') selected @endif value="female">@lang('app.female')</option>
                            <option @if ($employee->gender == 'others') selected @endif value="others">@lang('app.others')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-4">
                        <x-forms.datepicker fieldId="joining_date" :fieldLabel="__('modules.employees.joiningDate')"
                            fieldName="joining_date" :fieldPlaceholder="__('placeholders.date')" fieldRequired="true"
                            :fieldValue="$employee->employeeDetail->joining_date->format(company()->date_format)" />
                    </div>
                    <div class="col-md-4">
                        <x-forms.datepicker fieldId="date_of_birth" :fieldLabel="__('modules.employees.dateOfBirth')"
                            fieldName="date_of_birth" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="($employee->employeeDetail->date_of_birth ? $employee->employeeDetail->date_of_birth->format(company()->date_format) : '')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="reporting_to" :fieldLabel="__('modules.employees.reportingTo')"
                            fieldName="reporting_to" :fieldPlaceholder="__('placeholders.date')" search="true">
                            <option value="">--</option>
                            @foreach ($employees as $item)
                                <x-user-option :user="$item" :selected="$employee->employeeDetail->reporting_to == $item->id"/>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-4">
                        <x-forms.select fieldId="locale" :fieldLabel="__('app.language')"
                            fieldName="locale" search="true">
                            @foreach ($languages as $language)
                                <option @if ($employee->locale == $language->language_code) selected @endif
                                data-content="<span class='flag-icon flag-icon-{{ ($language->flag_code == 'en') ? 'gb' : $language->flag_code }} flag-icon-squared'></span> {{ $language->language_name }}"
                                value="{{ $language->language_code }}">{{ $language->language_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    @if (
                    ((in_array('admin', $userRoles) && in_array('admin', user_roles()))
                    || (!in_array('admin', $userRoles)))
                    && $employee->id != user()->id
                    && $changeEmployeeRolePermission == 'all'
                    )
                        <div class="col-md-4">
                            <x-forms.select fieldId="role" :fieldLabel="__('app.role')" fieldName="role">
                                @foreach ($roles as $role)
                                    <option
                                    @if (
                                        (in_array($role->name, $userRoles) && $role->name == 'admin')
                                        || (in_array($role->name, $userRoles) && !in_array('admin', $userRoles))
                                    )
                                     selected
                                    @endif
                                    value="{{ $role->id }}">{{ $role->display_name }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                    @endif

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.address')"
                                :fieldValue="$employee->employeeDetail->address" fieldName="address" fieldId="address"
                                :fieldPlaceholder="__('placeholders.address')">
                            </x-forms.textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.about')"
                                fieldName="about_me" fieldId="about_me" fieldPlaceholder="" :fieldValue="$employee->employeeDetail->about_me">
                            </x-forms.textarea>
                        </div>
                    </div>

                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-top-grey">
                    @lang('modules.client.clientOtherDetails')</h4>
                <div class="row p-20">

                    @if ($employee->id != user()->id)
                        <div class="col-md-4">
                            <div class="form-group my-3">
                                <label class="f-14 text-dark-grey mb-12 w-100"
                                    for="usr">@lang('modules.client.clientCanLogin')</label>
                                <div class="d-flex">
                                    <x-forms.radio fieldId="login-yes" :fieldLabel="__('app.yes')" fieldName="login"
                                        fieldValue="enable" :checked="($employee->login == 'enable') ? 'checked' : ''">
                                    </x-forms.radio>
                                    <x-forms.radio fieldId="login-no" :fieldLabel="__('app.no')" fieldValue="disable"
                                        fieldName="login" :checked="($employee->login == 'disable') ? 'checked' : ''">
                                    </x-forms.radio>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100"
                                for="usr">@lang('modules.emailSettings.emailNotifications')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="notification-yes" :fieldLabel="__('app.yes')" fieldValue="1"
                                    fieldName="email_notifications"
                                    :checked="($employee->email_notifications) ? 'checked' : ''">
                                </x-forms.radio>
                                <x-forms.radio fieldId="notification-no" :fieldLabel="__('app.no')" fieldValue="0"
                                    fieldName="email_notifications"
                                    :checked="(!$employee->email_notifications) ? 'checked' : ''">
                                </x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <x-forms.label class="my-3" fieldId="hourly_rate"
                            :fieldLabel="__('modules.employees.hourlyRate')"></x-forms.label>
                        <x-forms.input-group>
                            <x-slot name="prepend">
                                <span
                                    class="input-group-text f-14 bg-white-shade">{{ company()->currency->currency_symbol }}</span>
                            </x-slot>

                            <input type="number" step=".01" min="0" class="form-control height-35 f-14"
                                value="{{ $employee->employeeDetail->hourly_rate ?? '' }}" name="hourly_rate"
                                id="hourly_rate">
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-4">
                        <x-forms.label class="my-3" fieldId="slack_username"
                            :fieldLabel="__('modules.employees.slackUsername')"></x-forms.label>
                        <x-forms.input-group>
                            <x-slot name="prepend">
                                <span class="input-group-text f-14 bg-white-shade">@</span>
                            </x-slot>

                            <input type="text" class="form-control height-35 f-14" autocomplete="off"
                                value="{{ $employee->employeeDetail->slack_username ?? '' }}" name="slack_username"
                                id="slack_username">
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-12">
                        <x-forms.text class="tagify_tags" fieldId="tags" :fieldLabel="__('app.skills')"
                            fieldName="tags" :fieldPlaceholder="__('placeholders.skills')"
                            :fieldValue="implode(',', $employee->skills())" />
                    </div>

                    @if (function_exists('sms_setting') && sms_setting()->telegram_status)
                        <div class="col-md-6">
                            <x-forms.number fieldName="telegram_user_id" fieldId="telegram_user_id"
                                fieldLabel="<i class='fab fa-telegram'></i> {{ __('sms::modules.telegramUserId') }}"
                                :fieldValue="$employee->telegram_user_id" :popover="__('sms::modules.userIdInfo')" />
                            <p class="text-bold text-danger">
                                @lang('sms::modules.telegramBotNameInfo')
                            </p>
                            <p class="text-bold"><span id="telegram-link-text">https://t.me/{{ sms_setting()->telegram_bot_name }}</span>
                                <a href="javascript:;" class="btn-copy btn-secondary f-12 rounded p-1 py-2 ml-1"
                                    data-clipboard-target="#telegram-link-text">
                                    <i class="fa fa-copy mx-1"></i>@lang('app.copy')</a>
                                <a href="https://t.me/{{ sms_setting()->telegram_bot_name }}" target="_blank" class="btn-secondary f-12 rounded p-1 py-2 ml-1">
                                    <i class="fa fa-copy mx-1"></i>@lang('app.openInNewTab')</a>
                            </p>
                        </div>
                    @endif
                    <div class="col-lg-3 col-md-6">
                        <x-forms.datepicker fieldId="probation_end_date" :fieldLabel="__('modules.employees.probationEndDate')"
                            fieldName="probation_end_date" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="$employee->employeeDetail->probation_end_date ? Carbon\Carbon::parse($employee->employeeDetail->probation_end_date)->format(company()->date_format) : '' "
                            :popover="__('messages.probationEndDate')"/>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.datepicker fieldId="notice_period_start_date" :fieldLabel="__('modules.employees.noticePeriodStartDate')"
                            fieldName="notice_period_start_date" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="$employee->employeeDetail->notice_period_start_date ? Carbon\Carbon::parse($employee->employeeDetail->notice_period_start_date)->format(company()->date_format) : '' "
                            :popover="__('messages.noticePeriodStartDate')"/>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.datepicker fieldId="notice_period_end_date" :fieldLabel="__('modules.employees.noticePeriodEndDate')"
                            fieldName="notice_period_end_date" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="$employee->employeeDetail->notice_period_end_date ? Carbon\Carbon::parse($employee->employeeDetail->notice_period_end_date)->format(company()->date_format) : '' "
                            :popover="__('messages.noticePeriodEndDate')"/>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="employment_type" :fieldLabel="__('modules.employees.employmentType')"
                            fieldName="employment_type" :fieldPlaceholder="__('placeholders.date')">
                            <option value="">--</option>
                            <option value="full_time" @if($employee->employeeDetail->employment_type == 'full_time') selected @endif>@lang('app.fullTime')</option>
                            <option value="part_time" @if($employee->employeeDetail->employment_type == 'part_time') selected @endif>@lang('app.partTime')</option>
                            <option value="on_contract" @if($employee->employeeDetail->employment_type == 'on_contract') selected @endif>@lang('app.onContract')</option>
                            <option value="internship" @if($employee->employeeDetail->employment_type == 'internship') selected @endif>@lang('app.internship')</option>
                            <option value="trainee" @if($employee->employeeDetail->employment_type == 'trainee') selected @endif>@lang('app.trainee')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-lg-3 col-md-6 d-none internship-date">
                        <x-forms.datepicker fieldId="internship_end_date" :fieldLabel="__('modules.employees.internshipEndDate')"
                            fieldName="internship_end_date" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="$employee->employeeDetail->internship_end_date ? Carbon\Carbon::parse($employee->employeeDetail->internship_end_date)->format(company()->date_format) : '' "/>
                    </div>

                    <div class="col-lg-3 col-md-6 d-none contract-date">
                        <x-forms.datepicker fieldId="contract_end_date" :fieldLabel="__('modules.employees.contractEndDate')"
                            fieldName="contract_end_date" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="$employee->employeeDetail->contract_end_date ? Carbon\Carbon::parse($employee->employeeDetail->contract_end_date)->format(company()->date_format) : '' "/>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="marital_status" :fieldLabel="__('modules.employees.maritalStatus')"
                            fieldName="marital_status" :fieldPlaceholder="__('placeholders.date')">
                            @foreach (\App\Enums\MaritalStatus::cases() as $status)
                                <option @selected($employee->employeeDetail->marital_status == $status)
                                    value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.label class="my-3" fieldId="category_id"
                            :fieldLabel="__('app.menu.businessAddresses')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="company_address"
                                id="company_address" data-live-search="true">
                                @foreach ($companyAddresses as $address)
                                    <option @selected($employee->employeeDetail->company_address_id == $address->id)
                                     value="{{ $address->id }}">{{ $address->location }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>

                    <div class="col-lg-3 col-md-6 d-none marriage_date">
                        <x-forms.datepicker fieldId="marriage_anniversary_date" :fieldLabel="__('modules.employees.marriageAnniversaryDate')"
                            fieldName="marriage_anniversary_date" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="$employee->employeeDetail->marriage_anniversary_date ? Carbon\Carbon::parse($employee->employeeDetail->marriage_anniversary_date)->format(company()->date_format) : '' " />
                    </div>


                </div>
                <div class="row p-20 border-top-grey">
                    {{-- Users cannot change their own status --}}
                    @if ($employee->id != user()->id && $employee->id != 1)
                        <div class="col-md-4">
                            <div class="form-group my-3">
                                <label class="f-14 text-dark-grey mb-12 w-100" for="usr">@lang('app.status')</label>
                                <div class="d-flex">
                                    <x-forms.radio fieldId="status-active" :fieldLabel="__('app.active')"
                                                   fieldValue="active" fieldName="status" onclick="checkExitDate()"
                                                   checked="($employee->status == 'active') ? 'checked' : ''">
                                    </x-forms.radio>
                                    <x-forms.radio fieldId="status-inactive" :fieldLabel="__('app.inactive')"
                                                   fieldValue="deactive" fieldName="status"
                                                   :checked="($employee->status == 'deactive') ? 'checked' : ''">
                                    </x-forms.radio>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($employee->id != user()->id)
                        <div @class([
                            'col-md-4',
                            // 'd-none' => $employee->status != 'deactive'
                        ]) id="employeeExitDate">
                            <x-forms.datepicker fieldId="last_date" :fieldLabel="__('modules.employees.lastDate')"
                                                fieldName="last_date" :fieldPlaceholder="__('placeholders.date')" fieldRequired="true"
                                                :fieldValue="($employee->employeeDetail->last_date ? $employee->employeeDetail->last_date->format(company()->date_format) : '')"
                                                :popover="__('messages.lastDateTooltip')" />
                        </div>
                        {{-- Display the message if the conditions are met --}}
                        <x-alert id="exitDateError" class="alert alert-danger ml-3 w-100 mr-3" type="danger" style="display: none;" icon="info-circle">
                            <span class="alert-text"></span>
                        </x-alert>
                    @endif
                </div>

                <x-forms.custom-field :fields="$fields" :model="$employeeDetail"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('employees.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script src="{{ asset('vendor/jquery/tagify.min.js') }}"></script>
@if (function_exists('sms_setting') && sms_setting()->telegram_status)
    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
@endif

<script>

    var messages = {
        exitDateErrorForPast: "@lang('messages.exitDateErrorForPast')",
        exitDateErrorForFuture: "@lang('messages.exitDateErrorForFuture')"
    };

    // Function to update the message text
    function updateMessageText(message, date) {
        var messageElement = document.getElementById('exitDateError');
        var messageText = document.querySelector('#exitDateError .alert-text');
        if (messageElement && messageText) {
            messageElement.classList.remove('alert-danger', 'alert-success'); // Clear existing classes
            messageElement.classList.add('alert-danger'); // Add danger class for errors

            // Replace the placeholder with the actual date
            var formattedMessage = message.replace(':date', date);
            messageText.innerHTML = formattedMessage;
        }
    }

    function formatDateToISO(date) {
        var year = date.getFullYear();
        var month = ('0' + (date.getMonth() + 1)).slice(-2); // Month is zero-indexed
        var day = ('0' + date.getDate()).slice(-2);
        return `${year}-${month}-${day}`;
    }

    function checkExitDate() {
        var lastDateInput = document.getElementById('last_date');
        var lastDate = lastDateInput ? lastDateInput.value : ''; // Get the new value from the date picker
        var activeRadioButton = document.getElementById('status-active');
        var isExitDate = "{{ $employee?->employeeDetail?->last_date }}" ? true : false;;
        var exitDateError = document.getElementById('exitDateError')

        if (activeRadioButton && activeRadioButton.checked && lastDate ) {

            $.ajax({
                url: '{{ route('getExitDateMessage') }}', // Your route to handle the request
                method: 'POST',
                data: {
                    last_date: lastDate,
                    is_exit_date: isExitDate,
                    _token: '{{ csrf_token() }}' // CSRF token for Laravel
                },
                success: function(response) {
                    console.log(response);
                    var message = response.message;
                    var showMessage = response.showMessage;

                    if (showMessage) {
                        exitDateError.style.display = 'block'; // Show the error message
                        updateMessageText(message, lastDate);
                    } else {
                        exitDateError.style.display = 'none'; // Hide the error message
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching message:', xhr);
                }
            });

        } else {
            if (exitDateError) {
                exitDateError.style.display = 'none'; // Hide the error message
            }
        }
    }


    // Add event listeners for radio buttons and date picker to call checkExitDate on change
    document.getElementById('status-active').addEventListener('change', checkExitDate);
    document.getElementById('status-inactive').addEventListener('change', checkExitDate);
    document.getElementById('last_date').addEventListener('change', checkExitDate);

    // Initialize the message state on page load
    window.onload = checkExitDate;

</script>

<script>
    $(document).ready(function() {

        checkExitDate();

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        datepicker('#joining_date', {
            position: 'bl',
            @if (!is_null($employee->employeeDetail->joining_date))
            dateSelected: new Date("{{ str_replace('-', '/', $employee->employeeDetail->joining_date) }}"),
            @endif
            ...datepickerConfig
        });

        datepicker('#date_of_birth', {
            position: 'bl',
            maxDate: new Date(),
            @if (!is_null($employee->employeeDetail->date_of_birth))
            dateSelected: new Date("{{ str_replace('-', '/', $employee->employeeDetail->date_of_birth) }}"),
            @endif
            ...datepickerConfig
        });

        datepicker('#probation_end_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#notice_period_start_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#notice_period_end_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#marriage_anniversary_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#internship_end_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#contract_end_date', {
            position: 'bl',
            ...datepickerConfig
        });

        var employment_type = $('#employment_type').val();
        if(employment_type == 'internship') {
            $('.internship-date').removeClass('d-none');
        }
        else if(employment_type == 'on_contract') {
            $('.contract-date').removeClass('d-none');
        }

        $('#employment_type').change(function(){
            var value = $(this).val();

            if(value == 'on_contract') {
                $('.contract-date').removeClass('d-none');
                $('#internship_end_date').val('');
            }
            else {
                $('.contract-date').addClass('d-none');
            }

            if(value == 'internship') {
                $('.internship-date').removeClass('d-none');
                $('#contract_end_date').val('');
            }
            else {
                $('.internship-date').addClass('d-none');
            }
        });

        var input = document.querySelector('input[name=tags]'),
            // init Tagify script on the above inputs
            tagify = new Tagify(input, {
                whitelist: {!! json_encode($skills) !!},
            });

        var marital_status = $('#marital_status').val();
        if(marital_status == '{{ \App\Enums\MaritalStatus::Married->value }}') {
            $('.marriage_date').removeClass('d-none');
        }

        $('#marital_status').change(function(){
            var value = $(this).val();
            if(value == '{{ \App\Enums\MaritalStatus::Married->value }}') {
                $('.marriage_date').removeClass('d-none');
            }
            else {
                $('.marriage_date').addClass('d-none');
            }
        })

        $('#save-form').click(function() {
            const url = "{{ route('employees.update', $employee->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-form",
                file: true,
                data: $('#save-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        });


        $('#random_password').click(function() {
            const randPassword = Math.random().toString(36).substr(2, 8);

            $('#password').val(randPassword);
        });

        $('#designation-setting-edit').click(function() {
            const url = "{{ route('designations.create') }}?model=true";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        })

        $('#department-edit').click(function() {
            const url = "{{ route('departments.create') }}?model=true";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        if ($('#last_date').length > 0) {
            datepicker('#last_date', {
                position: 'bl',
                @if ($employee->employeeDetail->last_date)
                    dateSelected: new Date("{{ str_replace('-', '/', $employee->employeeDetail->last_date) }}"),
                @endif
                ...datepickerConfig,
                onSelect: function(dateText) {
                    checkExitDate();
                }
            });
        }

        function updatePhoneCode() {
            var selectedCountry = $('#country').find(':selected');
            var phonecode = selectedCountry.data('phonecode');
            var iso = selectedCountry.data('iso');

            $('#country_phonecode').find('option').each(function() {
                if ($(this).data('country-iso') === iso) {
                    $(this).val(phonecode);
                    $(this).prop('selected', true); // Set the option as selected
                }
            });
        }
        updatePhoneCode();

        $('#country').change(function(){
            updatePhoneCode();
            $('.select-picker').selectpicker('refresh');
        });
        <x-forms.custom-field-filejs/>

        init(RIGHT_MODAL);
    });

    $('.cropper').on('dropify.fileReady', function(e) {
            var inputId = $(this).find('input').attr('id');
            var url = "{{ route('cropper', ':element') }}";
            url = url.replace(':element', inputId);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        @if (function_exists('sms_setting') && sms_setting()->telegram_status)
        var clipboard = new ClipboardJS('.btn-copy');

        clipboard.on('success', function(e) {
            Swal.fire({
                icon: 'success',
                text: '@lang("app.urlCopied")',
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    confirmButton: 'btn btn-primary',
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
            })
        });
    @endif
</script>
