@php
    $paymentID = $paymentID;
    $offlineID = $offlineID;
@endphp

<div class="col-md-12 table-responsive">
    <table width="100%" class="table" id="bulk-data-table">
        <thead lass="thead-light">
            <tr>
                <td class="border-bottom-0 btrr-mbl btlr text-dark">@lang('modules.invoices.invoiceNumber') #</td>
                <td>@lang('modules.payments.paymentDate')<sup class="text-red f-14 mr-1">*</sup></td>
                <td>@lang('modules.invoices.paymentMethod')</td>
                <td>@lang('modules.payments.offlinePaymentMethod')</td>
                @if($linkPaymentPermission == 'all') <td>@lang('app.menu.bankaccount')</td> @endif
                <td>@lang('modules.payments.transactionId')</td>
                <td>@lang('modules.payments.amountReceived')<sup class="text-red f-14 mr-1">*</sup></td>
                <td>@lang('modules.invoices.invoiceBalanceDue')</td>
            </tr>
        </thead>
        <tbody>
            @forelse ($pendingPayments as $key => $pendingPayment)
                <tr>
                    <td class="border-bottom-0 btrr-mbl btlr">
                        <input type="hidden" id="invoice_number" name="invoice_number[]" value="{{ $pendingPayment->id }}">
                        {{ $pendingPayment->invoice_number }}
                    </td>
                    <td class="border-bottom-0 btrr-mbl btlr">
                        <div class="input-group">
                            <input type="text" data-id="{{ $key }}" id="payment_date{{ $key }}"
                                name="payment_date[]"
                                class="payment_date px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15 w-100"
                                placeholder="@lang('placeholders.date')"
                                value="{{ now(company()->timezone)->format(company()->date_format) }}">
                        </div>
                    </td>
                    <td class="border-bottom-0 btrr-mbl btlr">
                        <div class="input-group">
                            <select name="gateway[]" data-id={{ $key }}
                                id="payment_gateway_id{{ $key }}"
                                    class="form-control select-picker payment_gateway_id" data-live-search="true"
                                    search="true">
                                <option value="all" @selected($paymentID == 'all')>--</option>
                                <option value="Offline" id="offline_method" @selected ($paymentID == 'Offline')>
                                    {{ __('modules.offlinePayment.offlinePayment') }}</option>
                                @if ($paymentGateway->paypal_status == 'active')
                                    <option value="paypal" @selected($paymentID == 'paypal') >{{ __('app.paypal') }}</option>
                                @endif
                                @if ($paymentGateway->stripe_status == 'active')
                                    <option value="stripe" @selected($paymentID == 'stripe') >{{ __('app.stripe') }}</option>
                                @endif
                                @if ($paymentGateway->razorpay_status == 'active')
                                    <option value="razorpay"  @selected($paymentID == 'razorpay')>{{ __('app.razorpay') }}</option>
                                @endif
                                @if ($paymentGateway->paystack_status == 'active')
                                    <option value="paystack" @selected($paymentID == 'paystack')>{{ __('app.paystack') }}</option>
                                @endif
                                @if ($paymentGateway->mollie_status == 'active')
                                    <option value="mollie" @selected($paymentID == 'mollie')>{{ __('app.mollie') }}</option>
                                @endif
                                @if ($paymentGateway->payfast_status == 'active')
                                    <option value="payfast" @selected($paymentID == 'payfast')>{{ __('app.payfast') }}</option>
                                @endif
                                @if ($paymentGateway->authorize_status == 'active')
                                    <option value="authorize" @selected($paymentID == 'authorize')>{{ __('app.authorize') }}
                                    </option>
                                @endif
                                @if ($paymentGateway->square_status == 'active')
                                    <option value="square" @selected($paymentID == 'square')>{{ __('app.square') }}</option>
                                @endif
                                @if ($paymentGateway->flutterwave_status == 'active')
                                    <option value="flutterwave" @selected($paymentID == 'flutterwave')>{{ __('app.flutterwave') }}
                                    </option>
                                @endif
                            </select>
                        </div>
                    </td>
                    <td class="border-bottom-0 btrr-mbl btlr">
                        <div class="input-group" id="add_offline{{ $key }}">
                            <select class="form-control select-picker add_offline_methods" id="add_offline_methods{{$key}}"
                                data-id="{{ $key }}" name="offline_methods[]" data-live-search="true"
                                search="true">
                                <option value="" @if ($offlineID == 'all') selected @endif>--</option>
                                @foreach ($offlineMethods as $offlineMethod)
                                    <option value="{{ $offlineMethod->id }}" @if ($offlineID == $offlineMethod->id) selected @endif>
                                        {{ $offlineMethod->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" id="offline_method_id{{ $key }}" name="offline_method_id[]"
                            value="">
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
                                                <option @if ($pendingPayment->bank_account_id == $bankDetail->id) selected @endif value="{{ $bankDetail->id }}">@if($bankDetail->type == 'bank')
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
                            <input type="text" class="form-control height-35 f-14" name="transaction_id[]"
                                id="transaction_id">
                        </div>
                    </td>
                    <td class="border-bottom-0 btrr-mbl btlr">
                        <div class="input-group">
                            <input type="number" class="form-control height-35 f-14 amount" name="amount[]"
                                id="amount{{ $key }}" data-id="{{ $key }}">
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

<script>
    $(document).ready(function() {
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

        $('#client_id, .payment_gateway_id, .add_offline_methods, .bank_account_id').selectpicker('destroy');
        $('#client_id, .payment_gateway_id, .add_offline_methods, .bank_account_id').selectpicker('render');
    });
</script>
