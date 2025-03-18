<div class="p-4 col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100">
    <div class="row">
        @include('sections.password-autocomplete-hide')

        <div class="col-lg-12">
            <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                          :fieldLabel="__('modules.profile.profilePicture')"
                          :fieldValue="($user->image ? $user->masked_image_url : $user->image_url)" fieldName="image"
                          fieldId="profile-image" :popover="__('modules.themeSettings.logoSize')">
            </x-forms.file>
        </div>

        <div class="col-lg-4">
            <label class="mt-3 mb-12 f-14 text-dark-grey w-100"
                   for="usr">@lang('modules.profile.yourName')</label>
            <div class="input-group">
                <select class="select-picker form-control" name="salutation" id="salutation"
                        data-live-search="true">
                    <option value="">--</option>
                    @foreach ($salutations as $salutation)
                        <option value="{{ $salutation->value }}" @selected($user->salutation == $salutation)>{{ $salutation->label() }}</option>
                    @endforeach
                </select>
                <div class="input-group-append w-70">
                    <input type="text" class="form-control f-14" placeholder="@lang('placeholders.name')"
                           name="name" id="name" value="{{ $user->name }}">
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.profile.yourEmail')"
                          fieldRequired="true" :fieldPlaceholder="__('placeholders.email')" fieldName="email"
                          fieldId="email" :fieldValue="$user->email"></x-forms.text>
        </div>

        <div class="col-lg-4">
            <x-forms.label class="mt-3" fieldId="password"
                           :fieldLabel="__('modules.profile.yourPassword')">
            </x-forms.label>
            <x-forms.input-group>

                <input type="password" name="password" id="password" autocomplete="off"
                       placeholder="@lang('placeholders.password')" class="form-control height-35 f-14">
                <x-slot name="preappend">
                    <button type="button" data-toggle="tooltip" data-original-title="@lang('app.viewPassword')"
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

        <div class="col-lg-4">
            <div class="my-3 form-group">
                <label class="mb-12 f-14 text-dark-grey w-100"
                       for="usr">@lang('modules.emailSettings.emailNotifications')</label>
                <div class="d-flex">
                    <x-forms.radio fieldId="login-yes" :fieldLabel="__('app.enable')"
                                   fieldName="email_notifications" fieldValue="1" checked="true"
                                   :checked="($user->email_notifications == 1) ? 'checked' : ''">
                    </x-forms.radio>
                    <x-forms.radio fieldId="login-no" :fieldLabel="__('app.disable')" fieldValue="0"
                                   fieldName="email_notifications"
                                   :checked="($user->email_notifications == 0) ? 'checked' : ''">
                    </x-forms.radio>
                </div>
            </div>
        </div>


        <div class="col-lg-4">
            <div class="my-3 form-group">
                <label class="mb-12 f-14 text-dark-grey w-100" for="usr">@lang('app.googleCalender')</label>
                <div class="d-flex">
                    <x-forms.radio fieldId="google_calendar_status-yes" :fieldLabel="__('app.yes')"
                                   fieldName="google_calendar_status" fieldValue="1"
                                   :checked="($user->google_calendar_status == 1) ? 'checked' : ''">
                    </x-forms.radio>
                    <x-forms.radio fieldId="google_calendar_status-no" :fieldLabel="__('app.no')" fieldValue="0"
                                   fieldName="google_calendar_status"
                                   :checked="($user->google_calendar_status == 0) ? 'checked' : ''">
                    </x-forms.radio>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <x-forms.select fieldId="country_id" :fieldLabel="__('app.country')" fieldName="country_id"
                            search="true" alignRight="true">
                <option value="">--</option>
                @foreach ($countries as $item)
                    <option data-tokens="{{ $item->iso3 }}" data-phonecode="{{ $item->phonecode }}" data-iso="{{ $item->iso }}"
                            data-content="<span class='flag-icon flag-icon-{{ strtolower($item->iso) }} flag-icon-squared'></span> {{ $item->nicename }}"
                            value="{{ $item->id }}" {{ $item->id == $user->country_id ? 'selected' : '' }}>
                        {{ $item->nicename }}</option>
                @endforeach
            </x-forms.select>
        </div>

        <div class="col-lg-4">
            <x-forms.label class="my-3" fieldId="mobile"
                           :fieldLabel="__('app.mobile')"></x-forms.label>
            <x-forms.input-group style="margin-top:-4px">
                <x-forms.select fieldId="country_phonecode" fieldName="country_phonecode"
                                search="true">
                    @php
                        $check = true;
                    @endphp
                    @foreach ($countries as $item)

                        @php

                            $selected = '';

                             if (($user->country && $user->country->iso == $item->iso && $user->country_phonecode == $item->phonecode) ||
                                 ($check && $user->country_phonecode == $item->phonecode)) {
                                 $selected = 'selected';
                                 $check = false;
                             }


                        @endphp
                        <option
                                {{$selected}}
                                data-tokens="{{ $item->name }}" data-country-iso="{{ $item->iso }}"
                                data-content="{{$item->flagSpanCountryCode()}}"
                                value="{{ $item->phonecode }}">{{ $item->phonecode }}
                        </option>
                    @endforeach
                </x-forms.select>
                <input type="tel" class="form-control height-35 f-14" placeholder="@lang('placeholders.mobile')"
                       name="mobile" id="mobile" value="{{ $user->mobile }}">
            </x-forms.input-group>
        </div>

        <div class="col-lg-4">
            <x-forms.select fieldId="locale" :fieldLabel="__('modules.accountSettings.changeLanguage')"
                            fieldName="locale" search="true">
                @foreach ($languageSettings as $language)
                    <option {{ user()->locale == $language->language_code ? 'selected' : '' }}
                            data-content="<span class='flag-icon flag-icon-{{ ($language->flag_code == 'en') ? 'gb' : $language->flag_code }} flag-icon-squared'></span> {{ $language->language_name . ($language->is_rtl == 1 ? ' (' . __('app.rtl') . ')' : '') }}"
                            value="{{ $language->language_code }}">{{ $language->language_name }}</option>
                @endforeach
            </x-forms.select>
        </div>

        <div class="col-md-4">
            <x-forms.select fieldId="gender" :fieldLabel="__('modules.employees.gender')" fieldName="gender">
                <option @selected($user->gender == 'male') value="male">@lang('app.male')</option>
                <option @selected($user->gender == 'female') value="female">@lang('app.female')</option>
                <option @selected($user->gender == 'others') value="others">@lang('app.others')</option>
            </x-forms.select>
        </div>

        @if(!in_array('client', user_roles()))
            <div class="col-lg-4">
                <x-forms.datepicker fieldId="date_of_birth" :fieldLabel="__('modules.employees.dateOfBirth')"
                                    fieldName="date_of_birth" :fieldPlaceholder="__('placeholders.date')"
                                    :fieldValue="($user->employeeDetail->date_of_birth ? $user->employeeDetail->date_of_birth->format(company()->date_format) : '')"/>
            </div>
            <div class="col-lg-4">
                <x-forms.label class="my-3" fieldId="slack_username"
                               :fieldLabel="__('modules.employees.slackUsername')"></x-forms.label>
                <x-forms.input-group>
                    <x-slot name="prepend">
                        <span class="input-group-text f-14 bg-white-shade">@</span>
                    </x-slot>

                    <input type="text" class="form-control height-35 f-14" name="slack_username"
                           id="slack_username" value="{{ $user->employeeDetail->slack_username }}">
                </x-forms.input-group>
            </div>
        @endif
        @if ($socialAuthSettings->twitter_status == 'enable')
            <div class="col-lg-4">
                <x-forms.text :fieldLabel="__('modules.profile.twitterId')"
                                :fieldPlaceholder="__('placeholders.twitterId')"
                                fieldName="twitter_id" fieldId="twitter_id"
                                :fieldValue="$user->twitter_id"/>
            </div>
        @endif
        @if (in_array('employee', user_roles()))
            <div class="col-md-4">
                <x-forms.select fieldId="marital_status" :fieldLabel="__('modules.employees.maritalStatus')"
                        fieldName="marital_status" :fieldPlaceholder="__('placeholders.date')">
                        @foreach (\App\Enums\MaritalStatus::cases() as $status)
                            <option @selected($user->employeeDetail->marital_status == $status)
                                value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                </x-forms.select>
            </div>
            <div class="col-lg-3 col-md-6 d-none marriage_date">
                <x-forms.datepicker fieldId="marriage_anniversary_date" :fieldLabel="__('modules.employees.marriageAnniversaryDate')"
                    fieldName="marriage_anniversary_date" :fieldPlaceholder="__('placeholders.date')"
                    :fieldValue="$user->employeeDetail->marriage_anniversary_date ? Carbon\Carbon::parse($user->employeeDetail->marriage_anniversary_date)->format(company()->date_format) : '' " />
            </div>
        @endif

        <div class="col-md-12">
            <div class="my-3 form-group">

                <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.profile.yourAddress')"
                                  fieldRequired="false" fieldName="address" fieldId="address"
                                  :fieldPlaceholder="__('placeholders.address')"
                                  :fieldValue="($user->employeeDetail ? $user->employeeDetail->address : $user->clientDetails->address)">
                </x-forms.textarea>

            </div>
        </div>

        @if (!in_array('client', user_roles()))
            <div class="col-md-12">
                <div class="my-3 form-group">
                    <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.about')"
                                      fieldName="about_me" fieldId="about_me" fieldPlaceholder=""
                                      :fieldValue="($user->employeeDetail ? $user->employeeDetail->about_me : '')">
                    </x-forms.textarea>
                </div>
            </div>
        @endif

        @if (function_exists('sms_setting') && sms_setting()->telegram_status)
            <div class="col-md-6">
                <x-forms.number fieldName="telegram_user_id" fieldId="telegram_user_id"
                                fieldLabel="<i class='fab fa-telegram'></i> {{ __('sms::modules.telegramUserId') }}"
                                :fieldValue="$user->telegram_user_id" :popover="__('sms::modules.userIdInfo')"/>
                <p class="text-bold text-danger">
                    @lang('sms::modules.telegramBotNameInfo')
                </p>
                <p class="text-bold"><span id="telegram-link-text">https://t.me/{{ sms_setting()->telegram_bot_name }}</span>
                    <a href="javascript:;" class="p-1 py-2 ml-1 rounded btn-copy btn-secondary f-12"
                        data-clipboard-target="#telegram-link-text">
                        <i class="mx-1 fa fa-copy"></i>@lang('app.copy')</a>
                    <a href="https://t.me/{{ sms_setting()->telegram_bot_name }}" target="_blank" class="p-1 py-2 ml-1 rounded btn-secondary f-12">
                        <i class="mx-1 fa fa-copy"></i>@lang('app.openInNewTab')</a>
                </p>
            </div>
        @endif

    </div>
</div>

<!-- Buttons Start -->
<div class="w-100 border-top-grey set-btns">
    <x-setting-form-actions>
        <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
        </x-forms.button-primary>
    </x-setting-form-actions>
</div>
<!-- Buttons End -->
@if (function_exists('sms_setting') && sms_setting()->telegram_status)
    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
@endif
<script>

    $(document).ready(function () {

        @if (!in_array('client', user_roles()))
            datepicker('#date_of_birth', {
                position: 'bl',
                maxDate: new Date(),
                @if(!is_null($user->employeeDetail->date_of_birth))
                dateSelected: new Date("{{ $user->employeeDetail->date_of_birth ? str_replace('-', '/', $user->employeeDetail->date_of_birth) : str_replace('-', '/', now()) }}"),
                @endif
                ...datepickerConfig
            });

            datepicker('#marriage_anniversary_date', {
                position: 'bl',
                ...datepickerConfig
            });

        @endif

        $('#random_password').click(function () {
            const randPassword = Math.random().toString(36).substr(2, 8);

            $('#password').val(randPassword);
        });

        $('#save-form').on('click', function (e) {
            var url = "{{ route('profile.update', [$user->id]) }}";
            $.easyAjax({
                url: url,
                container: '#editSettings',
                type: "POST",
                blockUI: true,
                disableButton: true,
                buttonSelector: "#save-form",
                file: true,
                data: $('#editSettings').serialize(),
                success: function (response) {
                    if (response.status == 'success') {
                        if (response.redirect) {
                            window.location.href = response.redirectUrl;
                        }

                    }
                }
            });
        });

        $('.cropper').on('dropify.fileReady', function (e) {
            var inputId = $(this).find('input').attr('id');
            var url = "{{ route('cropper', ':element') }}";
            url = url.replace(':element', inputId);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#country_id').on('change', function() {
            var phonecode = $(this).find(':selected').data('phonecode');
            var iso = $(this).find(':selected').data('iso');

            $('#country_phonecode').find('option').each(function() {
                if ($(this).data('country-iso') === iso) {
                    $(this).val(phonecode);
                    $(this).prop('selected', true); // Set the option as selected
                }
            });
            $('#country_phonecode').selectpicker('refresh');
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

