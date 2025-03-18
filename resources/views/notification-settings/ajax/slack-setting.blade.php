<div class="col-xl-8 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
    <div class="row" id="slack-row">

        <div class="col-lg-12">
            <x-forms.checkbox :fieldLabel="__('app.status')" fieldName="slack_status" fieldId="slack_status"
                              fieldValue="active" fieldRequired="true" :checked="$slackSettings->status=='active'"/>
        </div>

        <div class="col-lg-12 slack_details @if($slackSettings->status=='inactive') d-none @endif">
            <div class="row mt-3">
                <div class="col-lg-12 col-md-12 ">
                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.slackSettings.slackWebhook')"
                                  :fieldPlaceholder="__('placeholders.slackWebhook')" fieldName="slack_webhook"
                                  fieldId="slack_webhook"
                                  :fieldValue="$slackSettings->slack_webhook" :fieldRequired="true"/>
                </div>

                <div class="col-lg-12">
                    <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2"
                                  :fieldLabel="__('modules.slackSettings.slackNotificationLogo')"
                                  :fieldValue="$slackSettings->slack_logo_url" fieldName="slack_logo"
                                  fieldId="slack_logo" :popover="__('messages.fileFormat.ImageFile')"/>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="col-xl-4 col-lg-12 col-md-12 ntfcn-tab-content-right border-left-grey p-4">
    <h4 class="f-16  f-w-500 text-dark-grey">@lang("modules.slackSettings.notificationTitle")</h4>
    <div class="mb-3 d-flex">
        <x-forms.checkbox  :checked="$checkedAll==true"
                :fieldLabel="__('modules.permission.selectAll')"
                fieldName="select_all_checkbox" fieldId="select_all"
                fieldValue="all"/>
    </div>
    @foreach ($emailSettings as $emailSetting)
        <div class="mb-3 d-flex notification">
            <x-forms.checkbox :checked="$emailSetting->send_slack == 'yes'"
                              :fieldLabel="__('modules.emailNotification.'.str_slug($emailSetting->setting_name))"
                              fieldName="send_slack[]" :fieldId="'send_slack'.$emailSetting->id"
                              :fieldValue="$emailSetting->id"/>
        </div>
    @endforeach
</div>

<!-- Buttons Start -->
<div class="w-100 border-top-grey set-btns">
    <x-setting-form-actions>
        <x-forms.button-primary id="save-slack-form" class="mr-3" icon="check">@lang('app.save')
        </x-forms.button-primary>

        <x-forms.button-secondary id="send-test-notification" icon="location-arrow">
            @lang('modules.slackSettings.sendTestNotification')</x-forms.button-secondary>
    </x-setting-form-actions>
</div>
<!-- Buttons End -->
<script>
    $(document).ready(function () {
        $('body').on('click', '#save-slack-form', function () {
            var url = "{{ route('slack-settings.update', slack_setting()->id) }}";

            $.easyAjax({
                url: url,
                type: "POST",
                container: "#editSettings",
                blockUI: true,
                file: true,
                data: $('#editSettings').serialize(),
                success: function () {
                    window.location.reload();
                }
            })
        });

        $('body').on('click', '#send-test-notification', function () {
            var url = '{{ route('slack_settings.send_test_notification') }}';
            $.easyAjax({
                url: url,
                type: "GET",
            })
        });

        init('#slack-row');


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

    });
</script>
<script>


</script>
