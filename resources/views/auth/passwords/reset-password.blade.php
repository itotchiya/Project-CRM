<x-auth>
    <form id="reset-password-form" action="{{ route('password.update') }}" class="ajax-form" method="POST">
        {{ csrf_field() }}

        <h3 class=" mb-4 f-w-500">@lang('app.resetPassword')</h3>

        <div class="alert alert-success m-t-10 d-none" id="success-msg"></div>

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="group">
            <div class="form-group text-center">
                <input type="hidden" name="email" value="{{ $request->email }}">
            </div>
            <div class="form-group text-left">
                <label for="password">@lang('app.password')</label>

                <input type="password" name="password"
                       class="form-control height-50 f-15 light_text" placeholder="Password"
                       id="password">
            </div>

            <div class="form-group text-left">
                <label for="password">@lang('app.confirmPassword')</label>
                <input type="password" name="password_confirmation"
                       class="form-control height-50 f-15 light_text" placeholder="Confirm Password"
                       id="password_confirmation">
            </div>

            <button
                type="button"
                id="submit-login"
                class="btn-primary f-w-500 rounded w-100 height-50 f-18">
                @lang('app.resetPassword') <i class="fa fa-arrow-right pl-1"></i>
            </button>
        </div>
        <div class="forgot_pswd mt-3">
            <a href="{{ route('login') }}" class="justify-content-center">@lang('app.login')</a>
        </div>
        <input type="hidden" name="locale" value="{{ session()->has('locale') ? session('locale') : global_setting()->locale }}">
    </form>

    <x-slot name="scripts">
        <script>

            $('#submit-login').click(function () {

                var url = "{{ route('password.update') }}";
                $.easyAjax({
                    url: url,
                    container: '#reset-password-form',
                    disableButton: true,
                    blockUI: true,
                    buttonSelector: "#submit-login",
                    type: "POST",
                    data: $('#reset-password-form').serialize(),
                    success: function (response) {
                        $('#success-msg').removeClass('d-none');
                        $('#success-msg').html(response.message);
                        $('.group').remove();
                        setTimeout(() => {
                            window.location.href = "{{ route('login') }}"
                        }, 3000);
                    }
                })
            });

        </script>
    </x-slot>

</x-auth>
