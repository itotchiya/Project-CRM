<x-auth>
    <x-form id="acceptInviteForm">
        <input type="hidden" name="send_mail_to_admin" value="yes">

        <h3 class=" mb-4 f-w-500">@lang('app.signUp')</h3>

        <div class="alert alert-success m-t-10 d-none" id="success-msg"></div>

        <div class="group">
            <div class="form-group text-left">
                <label for="user-name">@lang('modules.employees.fullName')<sup
                        class="f-14">*</sup></label>
                <input type="text" name="name" class="form-control height-50 f-15 light_text"
                       placeholder="@lang('placeholders.name')" id="user-name">
            </div>

            @if (!is_null($invite->email_restriction))
                <div class="form-group text-left">
                    <x-forms.label fieldId="user-email" :fieldLabel="__('app.email')"
                                   fieldRequired="true">
                    </x-forms.label>
                    <x-forms.input-group>
                        <input type="text" name="email_address" id="email_address"
                               class="form-control height-50 f-15 light_text">
                        <x-slot name="append">
                        <span class="input-group-text height-50 border bg-white">

                            {{ '@'.$invite->email_restriction }}</span>
                        </x-slot>
                    </x-forms.input-group>
                    <input type="hidden" name="email_domain" id="email_domain"
                           value="{{ $invite->email_restriction }}">
                    <input type="hidden" name="email" id="user-email">
                </div>
            @else
                <div class="form-group text-left">
                    <label for="user-email">@lang('app.email')<sup class="f-14">*</sup></label>
                    <input type="email" name="email" class="form-control height-50 f-15 light_text"
                           placeholder="@lang('placeholders.email')" id="user-email">
                </div>
            @endif


            <div class="form-group text-left">
                <label for="password">@lang('app.password')<sup class="f-14">*</sup></label>
                <input type="password" name="password" class="form-control height-50 f-15 light_text"
                       placeholder="@lang('placeholders.password')" id="password">
            </div>

            @if ($globalSetting->sign_up_terms == 'yes')
                <div class="form-group text-left" >
                    <input autocomplete="off" id="read_agreement"
                        name="terms_and_conditions" type="checkbox" >
                    <label for="read_agreement">@lang('app.acceptTerms') <a href="{{ $globalSetting->terms_link }}" target="_blank" id="terms_link" >@lang('app.termsAndCondition')</a></label>
                </div>
            @endif

            <button type="button" id="submit-signup"
                    class="btn-primary f-w-500 rounded w-100 height-50 f-18">
                @lang('app.signUp') <i class="fa fa-arrow-right pl-1"></i>
            </button>
        </div>
        <div class="forgot_pswd mt-3">
            <a href="{{ route('login') }}" class="justify-content-center">@lang('app.login')</a>
        </div>
        <input type="hidden" name="locale" value="{{ session()->has('locale') ? session('locale') : global_setting()->locale }}">
    </x-form>

    <x-slot name="scripts">
        <script>
            $('#email_address').change(function () {
                var email = $('#email_address').val() + '@' + $('#email_domain').val();
                $('#user-email').val(email);
            });

            $('#submit-signup').click(function () {

                var url = "{{ route('accept_invite') . '?invite=' . $invite->invitation_code }}";
                $.easyAjax({
                    url: url,
                    container: '#acceptInviteForm',
                    disableButton: true,
                    buttonSelector: "#submit-signup",
                    type: "POST",
                    blockUI: true,
                    messagePosition: 'inline',
                    data: $('#acceptInviteForm').serialize(),
                    success: function (response) {
                        $('#success-msg').removeClass('d-none');
                        $('#success-msg').html(response.message);
                        $('.group').remove();
                        setTimeout(() => {
                            window.location.href = "{{ route('dashboard') }}"
                        }, 2000);
                    },
                })
            });

        </script>
    </x-slot>

</x-auth>
