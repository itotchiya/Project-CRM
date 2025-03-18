 <div class="p-4 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 ">
    <div class="row">

        <div class="col-lg-3">
            <x-forms.select fieldId="date_format" :fieldLabel="__('modules.accountSettings.dateFormat')"
                            fieldName="date_format" search="true">
                @foreach ($dateFormat as $format)
                    <option value="{{ $format }}"
                            @if (companyOrGlobalSetting()->date_format == $format) selected @endif>
                        {{ $format }} ({{ $dateObject->setTimezone(companyOrGlobalSetting()->timezone)->translatedFormat($format) }})
                    </option>
                @endforeach
            </x-forms.select>
        </div>
        <div class="col-lg-3">
            <x-forms.select fieldId="time_format" :fieldLabel="__('modules.accountSettings.timeFormat')"
                            fieldName="time_format" search="true">
                <option value="h:i A" @if (companyOrGlobalSetting()->time_format == 'h:i A') selected @endif>
                    12 @lang('app.hour') ({{ now(companyOrGlobalSetting()->timezone)->translatedFormat('h:i A') }})
                </option>
                <option value="h:i a" @if (companyOrGlobalSetting()->time_format == 'h:i a') selected @endif>
                    12 @lang('app.hour') ({{ now(companyOrGlobalSetting()->timezone)->translatedFormat('h:i a') }})
                </option>
                <option value="H:i" @if (companyOrGlobalSetting()->time_format == 'H:i') selected @endif>
                    24 @lang('app.hour') ({{ now(companyOrGlobalSetting()->timezone)->translatedFormat('H:i') }})
                </option>
            </x-forms.select>
        </div>
        <div class="col-lg-2">
            <x-forms.select fieldId="timezone" :fieldLabel="__('modules.accountSettings.defaultTimezone')"
                            fieldName="timezone" search="true">
                @foreach ($timezones as $tz)
                    <option @if (companyOrGlobalSetting()->timezone == $tz) selected @endif value="{{ $tz }}">{{ $tz }}</option>
                @endforeach
            </x-forms.select>
        </div>
        <div class="col-lg-2">
            <x-forms.select fieldId="currency_id"
                            :fieldLabel="__('modules.accountSettings.defaultCurrency')"
                            fieldName="currency_id" search="true" :popover="__('modules.accountSettings.appCurrencyInfo')">
                @foreach ($currencies as $currency)
                    <option @if ($currency->id == companyOrGlobalSetting()->currency_id)
                            selected
                            @endif value="{{ $currency->id }}">
                        {{ $currency->currency_symbol . ' (' . $currency->currency_code . ')' }}
                    </option>
                @endforeach
            </x-forms.select>
        </div>
        <div class="col-lg-2">
            <!-- Language selection dropdown -->
            <x-forms.select
                fieldId="locale"
                :fieldLabel="__('modules.accountSettings.language')"
                fieldName="locale"
                search="true"
                :popover="__('modules.accountSettings.appLanguageInfo')"
            >
                @foreach ($languageSettings as $language)
                    <option
                        {{ companyOrGlobalSetting()->locale == $language->language_code ? 'selected' : '' }}
                        data-content="<span class='flag-icon flag-icon-{{ $language->flag_code == 'en' ? 'gb' : str($language->flag_code)->lower() }} flag-icon-squared'></span> {{ $language->language_name . ($language->is_rtl == 1 ? ' (' . __('app.rtl') . ')' : '') }}"
                        value="{{ $language->language_code }}"
                    >
                    </option>
                @endforeach
            </x-forms.select>
        </div>

        <div class="col-lg-3">
            <x-forms.select fieldId="session_driver"
                            :fieldLabel="__('modules.accountSettings.sessionDriver')"
                            :popover="__('modules.accountSettings.sessionInfo')" fieldName="session_driver">
                <option {{ global_setting()->session_driver == 'file' ? 'selected' : '' }} value="file">
                    @lang('modules.accountSettings.sessionFile')</option>
                <option
                    {{ global_setting()->session_driver == 'database' ? 'selected' : '' }} value="database">
                    @lang('modules.accountSettings.sessionDatabase')</option>
            </x-forms.select>
            @if (global_setting()->session_driver == 'database')
                <small><a id="delete-sessions" href="javascript:;"><i class="fa fa-trash"></i>
                        @lang('modules.accountSettings.deleteSessions')</a></small>
            @endif
        </div>
        <div class="col-lg-3 mt-lg-5">
            <x-forms.checkbox :checked="global_setting()->app_debug"
                              :fieldLabel="__('modules.accountSettings.appDebug')"
                              fieldName="app_debug" :popover="__('modules.accountSettings.appDebugInfo')"
                              fieldId="app_debug"/>
        </div>
        <div class="col-lg-3 mt-lg-5">
            <x-forms.checkbox :checked="global_setting()->system_update"
                              :fieldLabel="__('modules.accountSettings.updateEnableDisable')"
                              fieldName="system_update"
                              :popover="__('modules.accountSettings.updateEnableDisableTest')"
                              fieldId="system_update"/>
        </div>
        <div class="col-lg-3 mt-lg-5">
            @php
                $cleanCache = '';
            @endphp
            @if ($cachedFile)
                @php
                    $cleanCache = '<a id="clear-cache" href="javascript:;"><i class="fa fa-trash"></i>' . __('modules.accountSettings.clearCache') . '</a>';
                @endphp

            @endif
            <x-forms.checkbox :checked="$cachedFile" :fieldLabel="__('app.enableCache')" fieldName="cache"
                              fieldId="cache" :fieldHelp="$cleanCache"/>
        </div>
        <div class="col-lg-3">
            <x-forms.select fieldId="datatable_row_limit" :fieldLabel="__('modules.accountSettings.datatableRowLimit')"
                            fieldName="datatable_row_limit"  :popover="__('modules.accountSettings.datatableRowLimitPopover')">
               <option {{ companyOrGlobalSetting()->datatable_row_limit == 10 ? 'selected' : '' }}value="10">10</option>
               <option {{ companyOrGlobalSetting()->datatable_row_limit == 25 ? 'selected' : '' }} value="25">25</option>
               <option {{ companyOrGlobalSetting()->datatable_row_limit == 50 ? 'selected' : '' }} value="50">50</option>
               <option {{ companyOrGlobalSetting()->datatable_row_limit == 100 ? 'selected' : '' }} value="100">100</option>
            </x-forms.select>
        </div>
        @if (company())
            <div class="col-lg-3 mt-lg-5">
                <x-forms.checkbox :checked="company()->employee_can_export_data"
                                :fieldLabel="__('app.employeeCanExportData')"
                                fieldName="employee_can_export_data"
                                fieldId="employee_can_export_data"/>
            </div>
        @endif

    </div>
</div>

 <div class="w-100 border-top-grey set-btns">
     <x-setting-form-actions>
         <x-forms.button-primary id="save-app-settings-form" class="mr-3" icon="check">@lang('app.save')
         </x-forms.button-primary>

     </x-setting-form-actions>
 </div>



<script>

    $('body').on('click', '#save-app-settings-form', function () {
        const url = "{{ route('app-settings.update', [company()->id]) }}?page=app-setting";

        $.easyAjax({
            url: url,
            container: '#editSettings',
            type: "POST",
            disableButton: true,
            buttonSelector: "#save-app-settings-form",
            data: $('#editSettings').serialize(),
            success: function () {
                window.location.reload();
            }
        })
    });

    $('body').on('click', '#delete-sessions', function () {
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.sessionDeleteConfirmation')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('app.delete')",
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

                const url = "{{ route('app-settings.delete_sessions') }}";

                const token = "{{ csrf_token() }}";

                $.easyAjax({
                    url: url,
                    type: "POST",
                    container: '#editSettings',
                    data: {
                        _token: token
                    },
                    success: function () {
                        window.location.reload();
                    }
                });
            }
        });
    });
</script>

