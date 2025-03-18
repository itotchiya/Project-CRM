@extends('layouts.app')

@push('styles')
    <style>
        ol>li {
            list-style: unset;
        }

        .nav-tabs .nav-link.active {
            border-bottom: 2px solid var(--header_color) !important;
        }

        .nav-tabs .nav-link {
            border: unset;
        }
    </style>
@endpush


@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">

                            <a class="nav-item nav-link f-15 active type" data-toggle="tab"
                                href="{{ route('leaves-settings.index') }}?tab=type" role="tab" aria-controls="nav-type"
                                aria-selected="true">@lang($pageTitle)</a>

                            <a class="nav-item nav-link f-15 general" data-toggle="tab"
                                href="{{ route('leaves-settings.index') }}?tab=general" role="tab"
                                aria-controls="nav-general"
                                aria-selected="false">@lang('app.menu.leaveGeneralSettings')</a>

                            <a class="nav-item nav-link f-15 general" data-toggle="tab"
                                href="{{ route('leaves-settings.index') }}?tab=archive" role="tab"
                                aria-controls="nav-general"
                                aria-selected="false">@lang('app.menu.archivedLeaveType')</a>

                        </div>
                    </nav>
                </div>
            </x-slot>

            <x-slot name="buttons">
                <div class="row">
                    <div class="col-md-12 mb-2">
                        <x-forms.button-primary icon="plus" id="addNewLeaveType" class="addNewLeaveType mb-2 actionBtn">
                            @lang('app.addNewLeaveType')
                        </x-forms.button-primary>
                    </div>
                </div>
            </x-slot>

            {{-- Include tabs here --}}
            @include($view)

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')

    <script>

        $('.nav-item').removeClass('active');
        const activeTab = "{{ $activeTab }}";
        $('.' + activeTab).addClass('active');

        showBtn(activeTab);

        function showBtn(activeTab) {
            (activeTab == 'general' || activeTab == 'archive') ? $('.actionBtn').addClass('d-none') : $('.actionBtn').removeClass('d-none')
        }

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
                    if (response.status == "success") {
                        showBtn(response.activeTab);
                        $('#nav-tabContent').html(response.html);
                        init('#nav-tabContent');
                    }
                }
            });
        });

        $(MODAL_XL).on('shown.bs.modal', function () {
        $('#page_reload').val('true')
        })

    </script>
@endpush
