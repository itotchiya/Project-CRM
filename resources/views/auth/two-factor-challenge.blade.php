<x-auth>
    @includeIf('sections.2fa-css')

    <form id="two-factor-challenge-form"
        action="{{ Session::get('login.authenticate_via') == 'email' ? route('check_code') : route('two-factor.login') }}"
        class="ajax-form" method="POST">
        @csrf
        <h3 class=" mb-5 f-w-500">
            <i class="fa fa-lock mr-3"></i>@lang('app.twoFactorVerification')
        </h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-group text-center code">
            <label id="2fa-code-label" for="code">@lang('app.twoFactorCode')</label>

            @includeIf('sections.2fa-input-field')

            <input type="hidden" value="{{ Session::get('login.id') }}" name="user_id">
        </div>
        <div class="form-group text-left recovery_code d-none">
            <label for="code">@lang('app.twoFactorRecoveryCode')</label>
            <input type="text" name="recovery_code" class="form-control height-50 f-15 light_text"
                   id="recovery_code">
        </div>

        <div
            class="position-relative mb-4 text-dark-grey resend-code-container @if (Session::get('login.authenticate_via') != 'email') d-none @endif">
            @lang('messages.resendCode') <a href="javascript:;" id="resend-code"
                                            class="border-0 d-inline f-14 font-weight-bold text-primary"><u>@lang('app.clickHere')</u></a>
        </div>

        <div
            class="position-relative mb-4 text-dark-grey two-fa-app-info-container @if (Session::get('login.authenticate_via') == 'email') d-none @endif">
            @lang('messages.twoFaAppInfo')
        </div>

        <div class="forgot_pswd verify-using-recovery-code-container mb-4 @if (Session::get('login.authenticate_via') == 'email') d-none @endif">
            <a href="javascript:;" id="verify-using-recovery-code"
               class="justify-content-center">@lang('app.verifyUsingRecoveryCodes')</a>
        </div>

        <div
            class="forgot_pswd verify-using-email-container mb-4 {{ Session::get('login.authenticate_via') == 'both' ? '' : 'd-none' }}">
            <a href="javascript:;" id="verify-using-email"
               class="justify-content-center">@lang('app.verifyUsingEmail')</a>
        </div>

        <button type="submit" id="submit-login"
                class="btn btn-primary f-w-500 rounded w-100 height-50 f-18 otp-submit">
            @lang('app.verify') <i class="fa fa-arrow-right pl-1"></i>
        </button>

        <div class="forgot_pswd mt-3">
            <a href="{{ route('login') }}" class="justify-content-center">@lang('app.login')</a>
        </div>
        <input type="hidden" name="locale" value="{{ session()->has('locale') ? session('locale') : global_setting()->locale }}">
    </form>

    <x-slot name="scripts">
        @includeIf('sections.2fa-js')

        <script>
            $("form").submit(function () {
                const button = $('form').find('#submit-login');

                const text = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{__('app.loading')}}';

                button.prop("disabled", true);
                button.html(text);
            });

            $('#resend-code').click(function() {
                resendCode();
            });

            function resendCode() {
                let url = "{{ route('resend_code') }}";
                let user_id = "{{ Session::get('login.id') }}";
                $.easyAjax({
                    url: url,
                    container: '.login_box',
                    type: "GET",
                    blockUI: true,
                    messagePosition: "pop",
                    data: {
                        user_id: user_id
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            showEmailMessage();
                        }
                    }
                });
            }

            $('#verify-using-recovery-code').click(function() {

                $('.code').toggleClass('d-none');
                $('.recovery_code').toggleClass('d-none');
                $('.resend-code-container').addClass('d-none');

                let text = $('.recovery_code').hasClass('d-none') ? '{{ __('app.verifyUsingRecoveryCodes') }}' :
                    '{{ __('app.verifyUsingGoogleAuthenticatorCodes') }}';

                $(this).text(text);

                if ($('.recovery_code').hasClass('d-none')) {
                    $('#recovery_code').removeAttr('required');
                    $("#code").attr("required", "true");
                    $('.two-fa-app-info-container').removeClass('d-none');
                } else {
                    $('#code').removeAttr('required');
                    $("#recovery_code").attr("required", "true");
                    $('.two-fa-app-info-container').addClass('d-none');
                }

                $('#two-factor-challenge-form').attr('action', "{{ route('two-factor.login') }}");
            });

            $('#verify-using-email').click(function() {
                resendCode();
                $('#two-factor-challenge-form').attr('action', "{{ route('check_code') }}");
                $(this).addClass('d-none');
            });

            function showEmailMessage() {
                $('.resend-code-container').removeClass('d-none');
                $('.two-fa-app-info-container').addClass('d-none');
                $('#2fa-code-label').text("{{ __('app.twoFactorCodeEmail') }}");
            }

        </script>

    </x-slot>

</x-auth>
