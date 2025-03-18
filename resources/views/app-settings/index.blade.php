@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="buttons">
                <x-cron-message />
            </x-slot>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link f-15 active app-setting"
                               href="{{ route('app-settings.index') }}" role="tab" aria-controls="nav-ticketAgents"
                               aria-selected="true">@lang('app.menu.appSettings')
                            </a>
                            <a class="nav-item nav-link f-15 client-signup-setting"
                               href="{{ route('app-settings.index') }}?tab=client-signup-setting" role="tab"
                               aria-controls="nav-ticketTypes" aria-selected="true"
                               ajax="false">@lang('app.clientSignUpSettings')
                            </a>
                            <a class="nav-item nav-link f-15 file-upload-setting"
                               href="{{ route('app-settings.index') }}?tab=file-upload-setting" role="tab"
                               aria-controls="nav-ticketTypes" aria-selected="true" ajax="false">@lang('modules.accountSettings.fileUploadSetting')
                            </a>
                            <a class="nav-item nav-link f-15 google-map-setting"
                               href="{{ route('app-settings.index') }}?tab=google-map-setting" role="tab"
                               aria-controls="nav-ticketTypes" aria-selected="true"
                               ajax="false">@lang('app.googleMapSettings')
                            </a>
                        </div>
                    </nav>
                </div>
            </x-slot>

            {{-- include tabs here --}}
            @include($view)

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')
    <script>
        /* manage menu active class */
        $('.nav-item').removeClass('active');
        const activeTab = "{{ $activeTab }}";
        $('.' + activeTab).addClass('active');

        $("body").on("click", "#editSettings .nav a", function(event) {
            event.preventDefault();

            $('.nav-item').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: "#nav-tabContent",
                historyPush: true,
                success: function(response) {
                    if (response.status === "success") {
                        $('#nav-tabContent .flex-wrap').html(response.html);
                        init('#nav-tabContent');
                    }
                }
            });
        });
    </script>
@endpush
