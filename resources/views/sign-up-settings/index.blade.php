@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        @include('sections.setting-sidebar')

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
                @method('PUT')
                <div class="row">
                    <div class="col-lg-4 mb-3">
                        <div class="form-group mb-4">
                            <x-forms.label fieldId="sign_up_term" :popover="__('app.signUpTermsNote')"
                                        :fieldLabel="__('app.showSignUpTerms')"></x-forms.label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                    @if ($global->sign_up_terms == 'yes') checked @endif
                                    class="custom-control-input change-module-setting"
                                    id="sign_up_terms" name="sign_up_terms" value="yes">
                                <label class="custom-control-label cursor-pointer" for="sign_up_terms"></label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 @if ($global->sign_up_terms == 'no') d-none @endif" id="terms_link_div">
                        <div class="form-group mb-lg-0 mb-md-0 mb-4">
                            <x-forms.label fieldId="terms_link" :fieldLabel="__('app.showSignUpTerms')" fieldRequired="true">
                            </x-forms.label>
                            <div class="input-group">
                                <input type="text" name="terms_link"
                                    class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                    placeholder="@lang('placeholders.url')" value="{{ $global->terms_link }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <x-slot name="action">
                <!-- Buttons Start -->
                <div class="w-100 border-top-grey">
                    <x-setting-form-actions>
                        <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
                        </x-forms.button-primary>
                        </x-setting-form-actions>
                </div>
                <!-- Buttons End -->
            </x-slot>

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
    <script>

        $('#sign_up_terms').change(function () {

            let terms = $(this).is(':checked') ? 'active' : 'inactive';

            if ($(this).is(':checked')) {
                $('#terms_link_div').removeClass('d-none');
            } else {
                $('#terms_link_div').addClass('d-none');
            }
        });

        $('#save-form').click(function () {
            var url = "{{ route('sign-up-settings.update', company()->id) }}";

            $.easyAjax({
                url: url,
                container: '#editSettings',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-form",
                data: $('#editSettings').serialize(),
            })
        });
    </script>
@endpush
