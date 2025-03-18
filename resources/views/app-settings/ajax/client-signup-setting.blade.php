<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
    <div class="row">

        <div class="col-lg-6">
            <x-forms.checkbox :checked="company()->allow_client_signup"
                              :fieldLabel="__('modules.accountSettings.allowClientSignup')"
                              fieldName="allow_client_signup"
                              :popover="__('modules.accountSettings.allowClientSignupPopUp')"
                              fieldId="allow_client_signup"/>
        </div>
        <div class="col-lg-5 {{ !company()->allow_client_signup ? 'd-none' : '' }}"
             id="admin-approval">
            <x-forms.checkbox :checked="company()->admin_client_signup_approval"
                              :fieldLabel="__('modules.accountSettings.needClientSignupApproval')"
                              fieldName="admin_client_signup_approval"
                              :popover="__('modules.accountSettings.needClientSignupApprovalPopUp')"
                              fieldId="admin_client_signup_approval"/>
        </div>

    </div>
</div>

<div class="w-100 border-top-grey set-btns">
    <x-setting-form-actions>
        <x-forms.button-primary id="save-client-signup-setting-form" class="mr-3" icon="check">@lang('app.save')
        </x-forms.button-primary>

    </x-setting-form-actions>
</div>

<script>
    $('body').on('change', '#allow_client_signup', function () {
        $(this).is(':checked') ? $('#admin-approval').removeClass('d-none') : $('#admin-approval').addClass('d-none');
    });

    $('body').on('click', '#save-client-signup-setting-form', function () {
        const url = "{{ route('app-settings.update', [company()->id]) }}?page=client-signup-setting";

        $.easyAjax({
            url: url,
            container: '#editSettings',
            type: "POST",
            disableButton: true,
            buttonSelector: "#save-client-signup-setting-form",
            data: $('#editSettings').serialize(),
        })
    });
</script>
