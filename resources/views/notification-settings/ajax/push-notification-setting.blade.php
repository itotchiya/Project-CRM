    <div class="col-xl-8 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">

        <div class="row">
            <div class="col-lg-12">
                <x-alert type="info" icon="info-circle">
                    @lang('modules.pushSettings.activeNotificationInfo')
                </x-alert>

            </div>

            <div class="col-lg-12 mb-2">
                <x-forms.checkbox :fieldLabel="__('app.enable').' Beams Push Notification (' . __('app.recommended') .')'" fieldName="beams_push_status" fieldId="beams_push_status"
                    fieldValue="active" fieldRequired="true" :checked="$pushSettings->beams_push_status == 'active'" />
            </div>

            <div class="col-lg-12">
                <x-forms.checkbox :fieldLabel="__('app.enable').' One Signal Push Notification'" fieldName="status" fieldId="push_status"
                    fieldValue="active" fieldRequired="true" :checked="$pushSettings->status == 'active'" />
            </div>

        </div>

        <div class="row push_details mt-3 @if ($pushSettings->status == 'inactive') d-none @endif">
            <div class="col-lg-6 col-md-6">
                <x-forms.text :fieldLabel="__('modules.pushSettings.oneSignalAppId')"
                    :fieldPlaceholder="__('placeholders.id')" fieldName="onesignal_app_id" fieldId="onesignal_app_id" fieldRequired="true"
                    :fieldValue="$pushSettings->onesignal_app_id" />
            </div>

            <div class="col-lg-6 col-md-6">
                <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.pushSettings.oneSignalRestApiKey')" fieldRequired="true"
                    :fieldPlaceholder="__('placeholders.key')" fieldName="onesignal_rest_api_key"
                    fieldId="onesignal_rest_api_key" :fieldValue="$pushSettings->onesignal_rest_api_key" />
            </div>

        </div>

        <div class="row beams_push_details mt-3 @if ($pushSettings->beams_push_status == 'inactive') d-none @endif">
            <div class="col-lg-6 col-md-6">
                <x-forms.text :fieldLabel="__('modules.pushSettings.pusherInstanceId')"
                    :fieldPlaceholder="__('placeholders.id')" fieldName="instance_id" fieldId="instance_id" fieldRequired="true"
                    :fieldValue="$pushSettings->instance_id" />
            </div>

            <div class="col-lg-6 col-md-6">
                <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.pushSettings.beamsSecret')" fieldRequired="true"
                    :fieldPlaceholder="__('placeholders.key')" fieldName="beam_secret"
                    fieldId="beam_secret" :fieldValue="$pushSettings->beam_secret" />
            </div>

        </div>
    </div>

    <div class="col-xl-4 col-lg-12 col-md-12 ntfcn-tab-content-right border-left-grey p-4">
        <h4 class="f-16  f-w-500 text-dark-grey">@lang("modules.pushSettings.notificationTitle")</h4>
        <div class="mb-3 d-flex">
            <x-forms.checkbox  :checked="$checkedAll==true"
                :fieldLabel="__('modules.permission.selectAll')"
                fieldName="select_all_checkbox" fieldId="select_all"
                fieldValue="all"/>
        </div>
        @foreach ($emailSettings as $emailSetting)
            <div class="mb-3 d-flex notification">
                <x-forms.checkbox :checked="$emailSetting->send_push == 'yes'"
                    :fieldLabel="__('modules.emailNotification.'.str_slug($emailSetting->setting_name))"
                    fieldName="send_push[]" :fieldId="'send_push_'.$emailSetting->id" :fieldValue="$emailSetting->id" />
            </div>
        @endforeach
    </div>

    <!-- Buttons Start -->
    <div class="w-100 border-top-grey set-btns">
        <x-setting-form-actions>
            <x-forms.button-primary id="save-push-form" class="mr-3" icon="check">@lang('app.save')
            </x-forms.button-primary>

            @if ($pushSettings->status == 'active' || $pushSettings->beams_push_status == 'active')
                <x-forms.button-secondary id="send-test-notification" icon="location-arrow">
                @lang('modules.slackSettings.sendTestNotification')</x-forms.button-secondary>
            @endif
        </x-setting-form-actions>
    </div>
    <!-- Buttons End -->

    <script>
        $('body').on('click', '#save-push-form', function() {
            $.easyAjax({
                url: "{{ route('push-notification-settings.update', $emailSetting->id) }}",
                type: "POST",
                container: "#editSettings",
                blockUI: true,
                data: $('#editSettings').serialize(),
                success: function () {
                    window.location.reload();
                }
            })
        });

        $('body').on('click', '#send-test-notification', function() {
            $.easyAjax({
                url: "{{ route('push_notification_settings.send_test_notification') }}",
                type: "GET",
            })
        });

        var checkboxes = document.querySelectorAll(".notification input[type=checkbox]");

        $('body').on('click', '#select_all', function() {
            var selectAll = $('#select_all').is(':checked');

            if(selectAll == true){
                checkboxes.forEach(function(checkbox){
                    checkbox.checked = true;
                })
            }
            else{
                checkboxes.forEach(function(checkbox){
                    checkbox.checked = false;
                })
            }
        });

    </script>
