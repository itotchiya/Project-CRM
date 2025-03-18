@extends('layouts.app')

@php
$manageShiftPermission = user()->permission('manage_employee_shifts');
@endphp
@section('content')
    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">



        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link f-15 active attendance"
                                href="{{ route('attendance-settings.index') }}?tab=attendance" role="tab"
                                aria-controls="nav-ticketAgents" aria-selected="true">@lang('app.menu.attendanceSettings')
                            </a>

                            @if ($manageShiftPermission == 'all')
                                <a class="nav-item nav-link f-15 shift"
                                    href="{{ route('attendance-settings.index') }}?tab=shift" role="tab"
                                    aria-controls="nav-ticketTypes" aria-selected="true">@lang('app.menu.employeeShifts')
                                </a>
                                <a class="nav-item nav-link f-15 shift-rotation"
                                    href="{{ route('attendance-settings.index') }}?tab=shift-rotation" role="tab"
                                    aria-controls="nav-ticketTypes" aria-selected="true" ajax="false">@lang('app.menu.shiftRotation')
                                </a>
                            @endif

                            <a class="nav-item nav-link f-15 qrcode"
                            href="{{ route('attendance-settings.index') }}?tab=qrcode"  role="tab"
                            aria-controls="nav-ticketTy" aria-selected="true">@lang('app.qrCode')
                        </a>
                        </div>
                    </nav>
                </div>
            </x-slot>

                <x-slot name="buttons">
                    <div class="row">

                        <div class="col-md-12 mb-2">
                            <x-forms.button-primary icon="plus" id="addEmployeeShift" class="shift-btn mb-2 d-none actionBtn">
                                @lang('app.addNewShift')
                            </x-forms.button-primary>

                            <x-forms.link-primary :link="route('shift-rotations.create')" class="mr-3 openRightModal float-left shift-rotation-btn mb-2 d-none actionBtn" icon="plus">
                                @lang('app.addNewShiftRotation')
                            </x-forms.link-primary>

                            <x-forms.link-primary :link="route('shift-rotations.automate_shift')" class="mr-3 openRightModal float-left shift-rotation-btn mb-2 d-none actionBtn" icon="plus">
                                @lang('modules.attendance.automateShifts')
                            </x-forms.link-primary>

                            <x-forms.button-primary icon="sync-alt" id="runShiftRotation" class="shift-rotation-btn mb-2 d-none actionBtn">
                                @lang('modules.attendance.runRotation')
                            </x-forms.button-primary>

                        </div>

                    </div>
                </x-slot>


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
            $('.actionBtn').addClass('d-none');
            $('.' + activeTab + '-btn').removeClass('d-none');
        }

        $("body").on("click", ".shift-rotation", function (event) {
            var url = "{{ route('attendance-settings.index') }}?tab=shift-rotation";
            window.location.href = url;
        });

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

        $('body').on('click', '#runShiftRotation', function(event) {
            var url = "{{ route('shift-rotations.run_rotation_get') }}";
            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });
    </script>
@endpush
