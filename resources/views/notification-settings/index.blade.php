@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>

            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link f-15 active email-setting"
                                href="{{ route('notifications.index') }}" role="tab" aria-controls="nav-ticketAgents"
                                aria-selected="true">@lang('app.email')
                            </a>
                            <a class="nav-item nav-link f-15 slack-setting"
                                href="{{ route('notifications.index') }}?tab=slack-setting" role="tab"
                                aria-controls="nav-ticketTypes" aria-selected="true" ajax="false">@lang('app.slack') <i
                                class="fa fa-circle ml-1 {{ $slackSettings->status == 'active' ? 'text-light-green' : 'text-red' }}"></i>
                            </a>
                            <a class="nav-item nav-link f-15 push-notification-setting"
                                href="{{ route('notifications.index') }}?tab=push-notification-setting" role="tab"
                                aria-controls="nav-ticketTypes" aria-selected="true"
                                ajax="false">@lang('app.pushNotification')<i
                                class="fa fa-circle ml-1 {{ ($pushSettings->status == 'active' || $pushSettings->beams_push_status == 'active') ? 'text-light-green' : 'text-red' }}"></i>
                            </a>
                            <a class="nav-item nav-link f-15 pusher-setting"
                                href="{{ route('notifications.index') }}?tab=pusher-setting" role="tab"
                                aria-controls="nav-ticketTypes" aria-selected="true"
                                ajax="false">@lang('app.menu.pusherSettings')<i
                                id="pusher-setting-tab" class="fa fa-circle ml-1 {{ $pusherSettings->status == 1 ? 'text-light-green' : 'text-red' }}"></i>
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
                        $('#nav-tabContent .flex-wrap').html('');
                        $('#nav-tabContent .flex-wrap').html(response.html);
                        init('#nav-tabContent');
                    }
                }
            });
        });

        $(document).on('change', '#pusher_status', function() {
            $('.pusher_details').toggleClass('d-none');
        });

        $(document).on('change', '#slack_status', function() {
            $('.slack_details').toggleClass('d-none');
        });

        $(document).on('change', '#push_status', function() {
            $('.push_details').toggleClass('d-none');
            $('#beams_push_status').prop('checked', false);

            if ($(this).is(':checked')) {
                $('.beams_push_details').addClass('d-none');
            }
        });

        $(document).on('change', '#beams_push_status', function() {
            $('.beams_push_details').toggleClass('d-none');
            $('#push_status').prop('checked', false);

            if ($(this).is(':checked')) {
                $('.push_details').addClass('d-none');
            }
        });
    </script>
@endpush
