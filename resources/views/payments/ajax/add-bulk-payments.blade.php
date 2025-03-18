<!-- CLIENT SEARCH START -->
<div class="row">
    <div class="col-md-12">
        <div class="bg-white d-flex rounded">
            @if (!in_array('client', user_roles()))
                <div class="select-box py-2 px-lg-2 px-md-2 px-0 ml-2">
                    <div class="select-status">
                        <select class="mt-3 form-control select-picker" id="client_id" name="client_id" data-live-search="true">
                            <option value="all">@lang('modules.payments.filterByCustomer')</option>
                            @foreach ($clients as $client)
                                <x-user-option :user="$client"/>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <div class="select-box py-2 px-lg-2 px-md-2 px-0 ml-2">
                <div class="select-status">
                    <select class="mt-3 form-control select-picker" id="payment_gateway" name="payment_gateway" data-live-search="true">
                        <option value="all">@lang('modules.payments.selectPaymentMethod')</option>
                        <option value="Offline" id="offline_method">
                            {{ __('modules.offlinePayment.offlinePayment') }}</option>
                        @if ($paymentGateway->paypal_status == 'active')
                            <option value="paypal">{{ __('app.paypal') }}</option>
                        @endif
                        @if ($paymentGateway->stripe_status == 'active')
                            <option value="stripe">{{ __('app.stripe') }}</option>
                        @endif
                        @if ($paymentGateway->razorpay_status == 'active')
                            <option value="razorpay">{{ __('app.razorpay') }}</option>
                        @endif
                        @if ($paymentGateway->paystack_status == 'active')
                            <option value="paystack">{{ __('app.paystack') }}</option>
                        @endif
                        @if ($paymentGateway->mollie_status == 'active')
                            <option value="mollie">{{ __('app.mollie') }}</option>
                        @endif
                        @if ($paymentGateway->payfast_status == 'active')
                            <option value="payfast">{{ __('app.payfast') }}</option>
                        @endif
                        @if ($paymentGateway->authorize_status == 'active')
                            <option value="authorize">{{ __('app.authorize') }}
                            </option>
                        @endif
                        @if ($paymentGateway->square_status == 'active')
                            <option value="square">{{ __('app.square') }}</option>
                        @endif
                        @if ($paymentGateway->flutterwave_status == 'active')
                            <option value="flutterwave">{{ __('app.flutterwave') }}
                            </option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="select-box py-2 px-lg-2 px-md-2 px-0 ml-2 d-none" id="offline_gateway">
                <div class="select-status">
                    <select class="mt-3 form-control select-picker" id="offline_gateway_data" name="offline_gateway_data" data-live-search="true">
                        <option value="all">@lang('modules.payments.selectOfflineMethod')</option>
                        @foreach ($offlineMethods as $offlineMethod)
                            <option value="{{ $offlineMethod->id }}">
                                {{ $offlineMethod->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- CLIENT SEARCH END -->

<!-- PENDING INVOICE TABLE START -->
<div class="row">
    <div class="col-sm-12">
        <x-form id="save-bulk-payment-form">

            <div class="bg-white rounded">
                <div class="row p-20" id="bulk-table">

                    <div class="col-md-12 table-responsive">
                        <table width="100%" class="table" id="bulk-data-table">
                            <thead lass="thead-light">
                                <tr>
                                    <td class="border-bottom-0 btrr-mbl btlr text-dark text-left pl-0">@lang('modules.invoices.invoiceNumber') #</td>
                                    <td >@lang('modules.payments.paymentDate')<sup class="text-red f-14 mr-1">*</sup></td>
                                    <td>@lang('modules.invoices.paymentMethod')</td>
                                    <td>@lang('modules.payments.offlinePaymentMethod')</td>
                                    @if($linkPaymentPermission == 'all') <td>@lang('app.menu.bankaccount')</td> @endif
                                    <td>@lang('modules.payments.transactionId')</td>
                                    <td>@lang('modules.payments.amountReceived')<sup class="text-red f-14 mr-1">*</sup></td>
                                    <td class="text-right pr-0">@lang('modules.invoices.invoiceBalanceDue')</td>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pendingPayments as $key => $pendingPayment)
                                    <tr>
                                        <td class="border-bottom-0 btrr-mbl btlr text-left pl-0">
                                            <input type="hidden" id="invoice_number" name="invoice_number[]"
                                                value="{{ $pendingPayment->id }}">
                                            {{ $pendingPayment->invoice_number }}
                                        </td>
                                        <td class="border-bottom-0 btrr-mbl btlr">
                                            <div class="input-group">
                                                <input type="text" data-id="{{ $key }}"
                                                    id="payment_date{{ $key }}" name="payment_date[]"
                                                    class="payment_date px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15 w-100"
                                                    placeholder="@lang('placeholders.date')"
                                                    value="{{ now(company()->timezone)->format(company()->date_format) }}">
                                            </div>
                                        </td>
                                        <td class="border-bottom-0 btrr-mbl btlr">
                                            <div class="input-group">
                                                <select name="gateway[]" data-id={{ $key }}
                                                    id="payment_gateway_id{{ $key }}"
                                                    class="form-control select-picker payment_gateway_id"
                                                    data-live-search="true" search="true">
                                                    <option value="all">--</option>
                                                    <option value="Offline" id="offline_method">
                                                        {{ __('modules.offlinePayment.offlinePayment') }}</option>
                                                    @if ($paymentGateway->paypal_status == 'active')
                                                        <option value="paypal">{{ __('app.paypal') }}</option>
                                                    @endif
                                                    @if ($paymentGateway->stripe_status == 'active')
                                                        <option value="stripe">{{ __('app.stripe') }}</option>
                                                    @endif
                                                    @if ($paymentGateway->razorpay_status == 'active')
                                                        <option value="razorpay">{{ __('app.razorpay') }}</option>
                                                    @endif
                                                    @if ($paymentGateway->paystack_status == 'active')
                                                        <option value="paystack">{{ __('app.paystack') }}</option>
                                                    @endif
                                                    @if ($paymentGateway->mollie_status == 'active')
                                                        <option value="mollie">{{ __('app.mollie') }}</option>
                                                    @endif
                                                    @if ($paymentGateway->payfast_status == 'active')
                                                        <option value="payfast">{{ __('app.payfast') }}</option>
                                                    @endif
                                                    @if ($paymentGateway->authorize_status == 'active')
                                                        <option value="authorize">{{ __('app.authorize') }}
                                                        </option>
                                                    @endif
                                                    @if ($paymentGateway->square_status == 'active')
                                                        <option value="square">{{ __('app.square') }}</option>
                                                    @endif
                                                    @if ($paymentGateway->flutterwave_status == 'active')
                                                        <option value="flutterwave">{{ __('app.flutterwave') }}
                                                        </option>
                                                    @endif
                                                </select>
                                            </div>
                                        </td>
                                        <td class="border-bottom-0 btrr-mbl btlr">
                                            <div class="input-group" id="add_offline{{ $key }}">
                                                <select class="form-control select-picker add_offline_methods"
                                                    id="add_offline_methods{{$key}}" data-id="{{ $key }}"
                                                    name="offline_methods[]" data-live-search="true"
                                                    search="true">
                                                    <option value="">--</option>
                                                    @foreach ($offlineMethods as $offlineMethod)
                                                        <option value="{{ $offlineMethod->id }}">
                                                            {{ $offlineMethod->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <input type="hidden" id="offline_method_id{{$key}}"
                                                name="offline_method_id[]" value="">
                                        </td>
                                        @if($linkPaymentPermission == 'all')
                                            <td class="border-bottom-0 btrr-mbl btlr">
                                                <div class="input-group" id="bank_account_id{{ $key }}">
                                                    <select class="form-control select-picker bank_account_id"
                                                        id="bank_account_id{{$key}}" data-id="{{ $key }}"
                                                        name="bank_account_id[]" data-live-search="true"
                                                        search="true" @if($pendingPayment->bank_account_id) disabled @endif>
                                                        <option value="">--</option>
                                                        @if($viewBankAccountPermission != 'none')
                                                            @foreach ($bankDetails as $bankDetail)
                                                                @if ($pendingPayment->currency->id == $bankDetail->currency_id)
                                                                    <option @selected ($pendingPayment->bank_account_id == $bankDetail->id) value="{{ $bankDetail->id }}">@if($bankDetail->type == 'bank')
                                                                        {{ $bankDetail->bank_name }} | @endif {{ $bankDetail->account_name }}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    @if($pendingPayment->bank_account_id)
                                                        <input type="hidden" id="bank_account_id{{$key}}" name="bank_account_id[]" value="{{ $pendingPayment->bank_account_id }}">
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                        <td class="border-bottom-0 btrr-mbl btlr">
                                            <div class="input-group">
                                                <input type="text" class="form-control height-35 f-14"
                                                    name="transaction_id[]" id="transaction_id">
                                            </div>
                                        </td>
                                        <td class="border-bottom-0 btrr-mbl btlr">
                                            <div class="input-group">
                                                <input type="number" class="form-control height-35 f-14 amount"
                                                    name="amount[]" id="amount{{ $key }}"
                                                    data-id="{{ $key }}">
                                            </div>
                                        </td>
                                        <td class="border-bottom-0 btrr-mbl btlr text-right pr-0">
                                            <input type="hidden" id="due_amount{{ $key }}"
                                                value="{{ $pendingPayment->amountDue() }}">
                                            {{ !is_null($pendingPayment->amountDue()) ? currency_format($pendingPayment->amountDue(), $pendingPayment->currency->id, $pendingPayment->currency->currency_symbol) : currency_format($pendingPayment->amountDue(), $pendingPayment->currency->id) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td @if($linkPaymentPermission == 'all') colspan="8" @else colspan="7" @endif>
                                            <x-cards.no-record icon="coins" :message="__('messages.noRecordFound')" />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <x-form-actions>
                        @if (count($pendingPayments) > 0)
                            <x-forms.button-primary id="save-bulk-payment-button" class="mr-3" icon="check">
                                @lang('app.save')
                            </x-forms.button-primary>
                        @endif
                        <x-forms.button-cancel :link="route('payments.index')" class="border-0">@lang('app.cancel')
                        </x-forms.button-cancel>
                    </x-form-actions>

                </div>
            </div>
        </x-form>
    </div>
</div>
<!-- PENDING INVOICE TABLE END -->

<script>
    $(document).ready(function() {

        $(document).on('show.bs.dropdown', '.table-responsive', function() {
            $('.table-responsive').css( "overflow", "inherit" );
        });

        $(document).on('click', '.payment_date', function() {
            $('.table-responsive').css( "overflow", "inherit" );
        });

        $(document).on('hide.bs.dropdown', '.table-responsive', function() {
            $('.table-responsive').css( "overflow", "auto" );
        })

        let paymentsData = $('.payment_date');

        $(paymentsData).each(function() {
            datepicker(this, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        let offlineData = $('.payment_gateway_id');

        $(offlineData).each(function() {
            let id = $(this).data('id');
            let val = $(this).val();
            let offlineVal = $('#add_offline_methods'+id).val();

            if (val == 'Offline') {
                $('#offline_method_id'+id).val(offlineVal);
            }
            else {
                $('#add_offline'+id).addClass('d-none');
            }
        });

        $(document).on('keyup', '.amount', function() {
            const id = $(this).data('id');
            const amount = parseInt($(this).val());
            const dueAmount = parseInt($('#due_amount'+id+ '').val());

            if (amount > dueAmount || amount < 1) {
                $(this).val('');
            }
        });

        $('#client_id').on('change', function() {
            showTable();
        });

        $('#payment_gateway').on('change', function() {
            $('#offline_gateway_data').val('all').change();
            showTable();
        });

        $('#offline_gateway_data').on('change', function() {
            showTable();
        });

        const showTable = () => {
            const clientID = $('#client_id').val();
            const paymentID = $('#payment_gateway').val();
            const offlineID = $('#offline_gateway_data').val();
            var url = "{{ route('payments.add_bulk_payments') }}?client_id=" + clientID + '&payment_id=' + paymentID + '&offline_id=' + offlineID;
            $.easyAjax({
                url: url,
                type: "GET",
                disableButton: true,
                blockUI: true,
                success: function(response) {
                    if (response.status == 'success') {
                        const content = response.table;

                        $("#bulk-table").html('');
                        $('#bulk-table').html(content);

                        if (response.payment== 'Offline') {
                            $('#offline_gateway').removeClass('d-none');
                            $('.add_offline_methods').selectpicker('refresh');
                        }
                        else {
                            $('#offline_gateway').addClass('d-none');
                        }

                        $('.payment_gateway_id, .add_offline_methods, .bank_account_id').selectpicker('destroy');
                        $('.payment_gateway_id, .add_offline_methods, .bank_account_id').selectpicker('render');
                    }
                }
            });
        };

        $(document).on('click', '#save-bulk-payment-button', function() {
            const url = "{{ route('payments.save_bulk_payments') }}";

            $.easyAjax({
                url: url,
                container: '#save-bulk-payment-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-payment-form",
                file: true,
                data: $('#save-bulk-payment-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        });

        $(document).on('change', '.payment_gateway_id', function() {
            let val = $(this).val();
            let id = $(this).data('id');
            let offlineId = $('#add_offline_methods'+id).val();
            $('.payment_gateway_id').selectpicker('destroy');
            $('.payment_gateway_id').selectpicker('render');

            if (val == 'Offline') {
                $('#offline_method_id'+id).val(offlineId);
                $('#add_offline'+id).removeClass('d-none');
                return false;
            } else {
                $('#offline_method_id'+id).val('');
                $('#add_offline'+id).addClass('d-none');
                return false;
            }
        });

        $(document).on('change', '.add_offline_methods', function() {
            let id = $(this).data('id');
            let val = $(this).val();

            $('#offline_method_id'+id).val(val);
            $('.add_offline_methods').selectpicker('destroy');
            $('.add_offline_methods').selectpicker('render');
        });

        init(RIGHT_MODAL);

    });
</script>
