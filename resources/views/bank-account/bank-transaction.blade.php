<script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>


@php
$addBankTransferPermission = user()->permission('add_bank_transfer');
$addBankDepositPermission = user()->permission('add_bank_deposit');
$addBankWithdrawPermission = user()->permission('add_bank_withdraw');
@endphp

<!-- ROW START -->
<div class="row pb-5">

    <div class="col-lg-7">
        <x-cards.data class="mb-4">
            <div class="d-flex justify-content-between">
                <div class="d-flex">
                    @php
                        $bankLogo = $bankaccount->bank_logo ? '<img data-toggle="tooltip" src="' . $bankaccount->file_url . '" class="width-35 height-35 img-fluid">' : '<span class="f-27">'.$bankaccount->file_url .'</span>';
                    @endphp
                    {!! $bankLogo !!}

                    <div class="ml-2">
                        @if ($bankaccount->bank_name != '')
                            <h3 class="heading-h3">{{ $bankaccount->bank_name }}</h3>
                            <p class="f-12 font-weight-normal text-dark-grey mb-0">
                                {{ $bankaccount->account_name }} &bull; {{ __('modules.bankaccount.'.$bankaccount->account_type) }} &bull; <span class="text-primary font-weight-semibold">#{{ $bankaccount->account_number }}</span>
                            </p>
                        @else
                           <h3 class="heading-h3">{{ $bankaccount->account_name }}</h3>
                        @endif
                    </div>
                </div>

                <div class="text-right">
                    <div class="f-12 text-dark-grey">@lang('modules.bankaccount.bankBalance')</div>
                    <h2 class="heading-h2 text-primary mt-2">{{ currency_format($bankaccount->bank_balance, $bankaccount->currency_id) }}</h2>
                </div>
            </div>


            <div class="card-footer bg-white border-top-grey px-0 mt-3">
                <div class="d-flex justify-content-between mt-3">
                    @if ($addBankDepositPermission == 'all')
                        <x-forms.link-secondary class="openRightModal" :link="route('bankaccounts.create_transaction').'?accountId='.$bankaccount->id.'&type=deposit'" icon="plus-circle">
                            @lang('modules.bankaccount.deposit')
                        </x-forms.link-secondary>
                    @endif
                    @if ($addBankWithdrawPermission == 'all')
                        <x-forms.link-secondary class="openRightModal" :link="route('bankaccounts.create_transaction').'?accountId='.$bankaccount->id.'&type=withdraw'" icon="minus-circle">
                            @lang('modules.bankaccount.withdraw')
                        </x-forms.link-secondary>
                    @endif
                    @if ($addBankTransferPermission == 'all')
                        <x-forms.link-secondary class="openRightModal" :link="route('bankaccounts.create_transaction').'?accountId='.$bankaccount->id.'&type=account'" icon="exchange-alt">
                            @lang('modules.bankaccount.bankAccountTransfer')
                        </x-forms.link-secondary>
                    @endif
                </div>
            </div>

        </x-cards.data>

        <x-cards.data :title="__('modules.bankaccount.creditVsDebit')">
            <x-line-chart id="task-chart5" :chartData="$creditVsDebitChart" height="250" multiple="true" />
        </x-cards.data>

    </div>

    <div class="col-lg-5">
        <x-cards.data :title="__('modules.bankaccount.recentTransactions')" class="table-hover" padding="false">
            <x-table>
                <x-slot name="thead">
                    <th>#</th>
                    <th>@lang('app.title')</th>
                    <th>@lang('app.date')</th>
                    <th class="text-right pr-20">@lang('app.amount')</th>
                </x-slot>
                @forelse ($recentTransactions as $key => $item)
                    <tr>
                        <td class="pl-20">
                            {{ ($key+1) }}
                        </td>
                        <td>
                            @lang('modules.bankaccount.'.$item->title)
                        </td>
                        <td>{{ $item->transaction_date->translatedFormat(company()->date_format) }}</td>
                        <td class="pr-20 text-right">
                            <span @class(['text-success' => $item->type == 'Cr', 'text-danger' => $item->type == 'Dr'])> {{ ($item->type == 'Cr' ? '+' : '-') }} {{ currency_format($item->amount, $bankaccount->currency_id) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4"></td>
                    </tr>
                @endforelse
            </x-table>
        </x-cards.data>
    </div>

    <div class="col-lg-12 col-md-12 my-4 mb-xl-0 mb-lg-4">

        <!-- Add Task Export Buttons Start -->
        <div class="d-flex justify-content-between action-bar">
            <x-forms.button-primary id="generate_statement" data-account-id="{{ $bankaccount->id }}" icon="file-pdf">
                @lang('modules.bankaccount.generateStatement')
            </x-forms.button-primary>
        </div>
        <!-- Add Task Export Buttons End -->

        <div class="d-flex flex-column w-tables rounded bg-white mt-4">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>


    </div>
</div>

@include('sections.datatable_js')

<script>
    $('#bank-transaction-table').on('preXhr.dt', function(e, settings, data) {

        var bankId = "{{ $bankaccount->id }}";
        data['bankId'] = bankId;

        // var dateRangePicker = $('#datatableRange').data('daterangepicker');
        // var startDate = $('#datatableRange').val();

        // if (startDate == '') {
        //     startDate = null;
        //     endDate = null;
        // } else {
        //     startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
        //     endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
        // }

        // var searchText = $('#search-text-field').val();

        // data['startDate'] = startDate;
        // data['endDate'] = endDate;
        // data['searchText'] = searchText;
    });
    const showTable = () => {
        window.LaravelDataTables["bank-transaction-table"].draw();
    }

    $('#search-text-field').on('change keyup', function() {
        if ($('#search-text-field').val() != "") {
            $('#reset-filters').removeClass('d-none');
            showTable();
        } else {
            $('#reset-filters').addClass('d-none');
            showTable();
        }
    });

    $('#reset-filters').click(function() {
        $('#filter-form')[0].reset();
        $('.select-picker').val('all');

        $('.select-picker').selectpicker("refresh");
        $('#reset-filters').addClass('d-none');

        showTable();
    });

    $('#quick-action-type').change(function() {
        const actionValue = $(this).val();
        if (actionValue != '') {
            $('#quick-action-apply').removeAttr('disabled');
        } else {
            $('#quick-action-apply').attr('disabled', true);
        }
    });

    $('#quick-action-apply').click(function() {
        const actionValue = $('#quick-action-type').val();
        if (actionValue == 'delete') {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    applyQuickAction();
                }
            });

        } else {
            applyQuickAction();
        }
    });

    const applyQuickAction = () => {
        var rowdIds = $("#bank-transaction-table input:checkbox:checked").map(function() {
            return $(this).val();
        }).get();

        var url = "{{ route('bankaccounts.apply_transaction_quick_action') }}?row_ids=" + rowdIds;

        $.easyAjax({
            url: url,
            container: '#quick-action-form',
            type: "POST",
            disableButton: true,
            buttonSelector: "#quick-action-apply",
            data: $('#quick-action-form').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    showTable();
                    resetActionButtons();
                    deSelectAll();
                }
            }
        })
    };

    $('body').on('click', '.delete-table-row', function() {
        var id = $(this).data('user-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var url = "{{ route('bankaccounts.destroy_transaction') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        'transactionId': id
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            showTable();
                        }
                    }
                });
            }
        });
    });

    $('#generate_statement').click(function () {
        let accountId = $(this).data('account-id');
        var url = "{{ route('bankaccounts.generate_statement', [':id']) }}";
        url = url.replace(':id', accountId);

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });
</script>
