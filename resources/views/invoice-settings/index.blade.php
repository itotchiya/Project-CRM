@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('vendor/css/image-picker.min.css') }}">

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        @include('sections.setting-sidebar')

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">
                            @if (in_array('invoices', user_modules()))
                                <a class="nav-item nav-link f-15 active general"
                                    href="{{ route('invoice-settings.index') }}?tab=general" role="tab"
                                    aria-controls="nav-ticketAgents" aria-selected="true">@lang('app.menu.invoiceSettings')
                                </a>
                            @endif

                            @if (in_array('invoices', user_modules()) || in_array('estimates', user_modules()) || in_array('orders', user_modules()) || in_array('leads', user_modules()))
                                <a class="nav-item nav-link f-15 active template"
                                    href="{{ route('invoice-settings.index') }}?tab=template" role="tab"
                                    aria-controls="nav-ticketAgents" aria-selected="true">@lang('app.menu.invoiceTemplate')
                                </a>

                                <a class="nav-item nav-link f-15 active prefix"
                                    href="{{ route('invoice-settings.index') }}?tab=prefix" role="tab"
                                    aria-controls="nav-ticketAgents" aria-selected="true">@lang('app.menu.prefixSettings')
                                </a>

                                <a class="nav-item nav-link f-15 units"
                                        href="{{ route('invoice-settings.index') }}?tab=units" role="tab"
                                        aria-controls="nav-ticketTypes" aria-selected="true">@lang('app.menu.units')
                                </a>
                            @endif

                            @if (in_array('invoices', user_modules()) || in_array('payments', user_modules()))
                                <a class="nav-item nav-link f-15 quickbooks"
                                    href="{{ route('invoice-settings.index') }}?tab=quickbooks" role="tab"
                                    aria-controls="nav-ticketTypes" aria-selected="true">@lang('app.menu.quickBookSettings')
                                </a>
                            @endif

                            @if (in_array('invoices', user_modules()))
                                <a class="nav-item nav-link f-15 payment"
                                    href="{{ route('invoice-settings.index') }}?tab=payment" role="tab"
                                    aria-controls="nav-ticketTypes" aria-selected="true">@lang('app.menu.invoicePaymentDetails')
                                </a>
                            @endif

                        </div>
                    </nav>
                </div>
            </x-slot>

            <x-slot name="buttons">
                <div class="row">
                    <div class="col-md-12  mb-3">
                        <x-forms.button-primary id="addUnitType" icon="plus" class="addUnit btn btn-primary mb-2 actionBtn units-btn"
                            type="button" data-toggle="tooltip">@lang('app.addUnit')</x-forms.button-primary>

                        <x-alert type="info" icon="info-circle" class="actionBtn quickbooks-btn">
                            @lang('modules.invoiceSettings.syncInfo')
                        </x-alert>

                        <x-alert type="info" icon="info-circle" class="actionBtn payment-btn">
                            @lang('modules.invoiceSettings.invoiceInfo')
                        </x-alert>

                        <x-forms.button-primary id="add-payment-details" icon="plus" class="addUnit btn btn-primary mb-2 actionBtn payment-btn"
                        type="button" data-toggle="tooltip"> @lang('modules.invoices.addPaymentDetails')</x-forms.button-primary>

                    </div>
                </div>
            </x-slot>

            @include($view)

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/image-picker/0.3.1/image-picker.min.js"></script>
<script>

    $('.nav-item').removeClass('active');
    const activeTab = "{{ $activeTab }}";
    $('.' + activeTab).addClass('active');

    showBtn(activeTab);

    function showBtn(activeTab) {
        $('.actionBtn').addClass('d-none');
        $('.' + activeTab + '-btn').removeClass('d-none');
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

    $('#addUnitType').click(function() {
        const url = "{{ route('unit-type.create') }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '#add-payment-details', function() {
        var url = "{{ route('invoices-payment-details.create') }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

</script>
@endpush

