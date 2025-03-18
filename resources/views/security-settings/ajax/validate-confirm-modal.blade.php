<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.authenticationRequired')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="reset-password-form" class="ajax-form" method="POST">
        <div class="row">
            <div class="col-lg-12 text-center">
                <x-forms.label class="mt-3" fieldId="password"
                    :fieldLabel="__('app.twoFactorCode')">
                </x-forms.label>
                <x-forms.input-group>
                    @includeIf('sections.2fa-input-field')
                </x-forms.input-group>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="submit-login" icon="check" class="otp-submit">@lang('modules.twofactor.validate2FA')</x-forms.button-primary>
</div>

@includeIf('sections.2fa-js')
<script>
    $('#submit-login').click(function() {

        var url = "{{ route('two-fa-settings.confirm') }}";
        $.easyAjax({
            url: url,
            container: '#reset-password-form',
            disableButton: true,
            blockUI: true,
            buttonSelector: "#submit-login",
            type: "POST",
            data: $('#reset-password-form').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            }
        })
    });
</script>
