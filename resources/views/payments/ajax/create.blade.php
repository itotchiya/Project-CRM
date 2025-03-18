<div class="row">
    <div class="col-sm-12">
        <x-form id="save-payment-data-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.payments.paymentDetails')</h4>
                <div class="row p-20">

                    <div class="col-md-3">
                        <input type="hidden" name="default_client" value="{{ $defaultClient ?? '' }}">
                        @if (isset($project) || !is_null($project))
                            <input type="hidden" id="payment_project_id" name="project_id"
                                value="{{ !is_null($project) ? $project->id : '' }}">
                            <x-forms.text :fieldLabel="__('app.project')" fieldName="projectName" fieldId="projectName"
                                :fieldValue="!is_null($project) ? $project->project_name : ''" fieldReadOnly="true" />
                        @else
                            <x-forms.select fieldId="payment_project_id" :fieldLabel="__('app.project')"
                                fieldName="project_id" search="true">
                                <option value="">--</option>
                                @foreach ($projects as $data)
                                    <option data-currency-id="{{ $data->currency_id }}" data-currency-code="{{ $data->currency->currency_code }}" @if (isset($project) && $data->id == $project->id) selected @endif
                                        value="{{ $data->id }}">
                                        {{ $data->project_name }}</option>
                                @endforeach
                            </x-forms.select>
                        @endif
                    </div>

                    <div class="col-md-3">

                        @if (isset($invoice))
                            <input type="hidden" id="invoice_id" name="invoice_id" value="{{ $invoice->id }}">
                            <input type="hidden" id="invoice_currency_id" value="{{ $invoice->currency_id }}">
                            <x-forms.text :fieldLabel="__('app.invoice')" fieldName="invoice_number"
                                fieldId="invoice_number" :fieldValue="$invoice->invoice_number" fieldReadOnly="true" />
                        @else
                            <x-forms.select fieldId="payment_invoice_id" :fieldLabel="__('app.invoice')"
                                fieldName="invoice_id" search="true">
                                <option value="">--</option>
                                @foreach ($invoices as $inv)
                                    @php
                                        $paidAmount = $inv->amountPaid();
                                    @endphp
                                    <option data-currency-code="{{$inv->currency->currency_code}}" data-currency-id="{{ $inv->currency_id }}"
                                        data-content="{{ $inv->invoice_number . ' - ' . __('app.total') . ': <span class=\'text-dark f-w-500 mr-2\'>' . currency_format($inv->total, $inv->currency->id) . ' </span>' . __('modules.invoices.due') . ': <span class=\'text-red\'>' . currency_format(max($inv->total - $paidAmount, 0), $inv->currency->id) . '</span>' }}"
                                        value="{{ $inv->id }}">{{ $inv->invoice_number }}</option>
                                @endforeach
                            </x-forms.select>
                        @endif

                    </div>


                    <div class="col-md-3">
                        <x-forms.datepicker fieldId="paid_on" :fieldLabel="__('modules.payments.paidOn')"
                            fieldName="paid_on" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="now()->timezone(company()->timezone)->format(company()->date_format)" />
                    </div>

                    <div class="col-md-3">
                        <x-forms.number fieldId="amount" :fieldLabel="__('modules.invoices.amount')" fieldName="amount"
                            :fieldValue="$unpaidAmount ?? ''" :fieldPlaceholder="__('placeholders.price')"
                            fieldRequired="true" />
                    </div>
                </div>

                <div class="row pl-20 pr-20">
                    <div class="col-md-3">
                        <input type="hidden" id="currency_id" name="currency_id" value="{{company()->currency_id}}">
                        <x-forms.select fieldId="currency" :fieldLabel="__('app.currency')" fieldName="currency"
                            search="true">
                            @if (isset($invoice))
                                <option value="{{ $invoice->currency->id }}">
                                    {{ $invoice->currency->currency_code . ' (' . $invoice->currency->currency_symbol . ')' }}
                                </option>
                            @else
                                @foreach ($currencies as $currency)
                                    <option @selected($currency->id == company()->currency_id) value="{{ $currency->id }}"
                                        data-currency-code="{{ $currency->currency_code }}">
                                        {{ $currency->currency_code . ' (' . $currency->currency_symbol . ')' }}
                                    </option>
                                @endforeach
                            @endif
                        </x-forms.select>
                    </div>
                    <div class="col-md-3">
                        <x-forms.text fieldId="exchange_rate" :fieldLabel="__('modules.currencySettings.exchangeRate')"
                        fieldName="exchange_rate" fieldRequired="true" :fieldValue="(isset($projectName) ? $project->currency->exchange_rate : (isset($invoice) ? $invoice->exchange_rate : $companyCurrency->exchange_rate))"
                        :fieldHelp="$currencyCode != company()->currency->currency_code ? ($currencyCode .' '.__('app.to').' '. company()->currency->currency_code) : ' '" />
                    </div>

                    <div class="col-md-3">
                        <x-forms.text fieldId="transaction_id" :fieldLabel="__('modules.payments.transactionId')"
                            fieldName="transaction_id" :fieldPlaceholder="__('placeholders.payments.transactionId')" />
                    </div>

                    <div class="col-md-3">
                        <x-forms.select fieldId="payment_gateway_id" :fieldLabel="__('modules.payments.paymentGateway')" fieldName="gateway"
                        search="true">
                            <option value="">--</option>
                            <option value="Offline"  id="offline_method" >{{ __('modules.offlinePayment.offlinePayment') }}</option>
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
                                <option value="authorize">{{ __('app.authorize') }}</option>
                            @endif
                            @if ($paymentGateway->square_status == 'active')
                                <option value="square">{{ __('app.square') }}</option>
                            @endif
                            @if ($paymentGateway->flutterwave_status == 'active')
                                <option value="flutterwave">{{ __('app.flutterwave') }}</option>
                            @endif
                        </x-forms.select>
                    </div>

                    <div class="col-md-3 d-none" id = "add_offline">
                        <x-forms.select fieldId="add_offline_methods" :fieldLabel="__('modules.payments.offlinePaymentMethod')" fieldName="offline_methods"
                        search="true">
                        </x-forms.select>
                    </div>

                    @if($linkPaymentPermission == 'all')
                        <div class="col-md-3">
                            <x-forms.select fieldId="bank_account_id" :fieldLabel="__('app.menu.bankaccount')" fieldName="bank_account_id"
                                search="true">
                                <option value="">--</option>
                                @if($viewBankAccountPermission != 'none')
                                    @foreach ($bankDetails as $bankDetail)
                                        <option @selected(isset($invoice) && $invoice->bank_account_id == $bankDetail->id) value="{{ $bankDetail->id }}">@if($bankDetail->type == 'bank')
                                            {{ $bankDetail->bank_name }} | @endif
                                            {{ $bankDetail->account_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </x-forms.select>
                        </div>
                    @endif

                    <div class="col-lg-12">
                        <x-forms.file allowedFileExtensions="txt pdf doc xls xlsx docx rtf png jpg jpeg svg" class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.receipt')" fieldName="bill"
                            fieldId="bill" :popover="__('messages.fileFormat.multipleImageFile')" />
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.remark')"
                                fieldName="remarks" fieldId="remarks"
                                :fieldPlaceholder="__('placeholders.payments.remark')" />
                        </div>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-payment-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('payments.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {
        datepicker('#paid_on', {
            position: 'bl',
            ...datepickerConfig
        });

        $('#payment_invoice_id').change(function() {
            var companyCurrency = '{{ $companyCurrency->id }}';
            var companyCurrencyName = "{{$companyCurrency->currency_code}}";

            if ($('#payment_invoice_id').val() != '') {

                var curId = $('#payment_invoice_id option:selected').attr('data-currency-id');
                var currentCurrencyName = $('#payment_invoice_id option:selected').attr('data-currency-code');
                $('#currency').removeAttr('disabled');
                $('#currency').selectpicker('refresh');
                $('#currency').val(curId);
                $('#currency_id').val(curId);
                $('#currency').prop('disabled', true);
                $('#currency').selectpicker('refresh');
            } else {
                    if($('#payment_project_id').val() != ''){
                        $('#currency').prop('disabled', true);
                    } else {
                        $('#currency').prop('disabled', false);
                        $('#currency').selectpicker('refresh');
                    }

                    var currentCurrencyName = $('#currency option:selected').attr('data-currency-code');
            }
            let currencyExchange = (companyCurrencyName != currentCurrencyName) ? '( '+companyCurrencyName+' @lang('app.to') '+currentCurrencyName+' )' : '';
            $('#exchange_rateHelp').html(currencyExchange);

            var url = "{{ route('payments.account_list') }}";
            var curId = $('#currency_id').val();
            var token = "{{ csrf_token() }}";
            var paymentInvoiceId = $('#payment_invoice_id').val();

            $.easyAjax({
                url: url,
                container: '#save-payment-data-form',
                type: "GET",
                blockUI: true,
                data: {
                    _token: token,
                    'curId' :curId,
                    'paymentInvoiceId': paymentInvoiceId
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#bank_account_id').html(response.data);
                        $('#bank_account_id').selectpicker('refresh');
                        $('#exchange_rate').val(response.exchangeRate);
                    }
                }
            });
        });

        $('#save-payment-form').click(function() {
            const url = "{{ route('payments.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-payment-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-payment-form",
                file: true,
                data: $('#save-payment-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        });

        $('#payment_project_id').change(function() {
            var companyCurrency = '{{ $companyCurrency->id }}';
            var companyCurrencyName = "{{$companyCurrency->currency_code}}";

            var id = $(this).val();

            if (id == '') {
                id = 0;
            }

            if ($('#payment_invoice_id').val() != '' || ($('#payment_project_id').val() != '' && $('#payment_project_id').val() != 0)) {
                var invoiceId = $('#invoice_id').val();
                var currentCurrencyName = $('#payment_project_id option:selected').attr('data-currency-code');

                if(invoiceId){
                    var curId = $('#invoice_currency_id').val();
                } else {
                    var curId = $('#payment_project_id option:selected').attr('data-currency-id');
                }
                $('#currency').removeAttr('disabled');
                $('#currency').selectpicker('refresh');
                $('#currency_id').val(curId);
                $('#currency').val(curId);
                $('#currency').prop('disabled', true);
                $('#currency').selectpicker('refresh');
            } else {
                var currentCurrencyName = $('#currency option:selected').attr('data-currency-code');
                $('#currency').prop('disabled', false);
                $('#currency').selectpicker('refresh');
            }
            let currencyExchange = (companyCurrencyName != currentCurrencyName) ? '( '+companyCurrencyName+' @lang('app.to') '+currentCurrencyName+' )' : '';
            $('#exchange_rateHelp').html(currencyExchange);

            var url = "{{ route('projects.invoice_list', ':id') }}";
            url = url.replace(':id', id);
            var currencyId = $('#currency_id').val();
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                type: "POST",
                blockUI: true,
                data: {
                    _token: token,
                    'currencyId' :currencyId
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#payment_invoice_id').html(response.data);
                        $('#payment_invoice_id').selectpicker('refresh');
                        $('#bank_account_id').html(response.account);
                        $('#bank_account_id').selectpicker('refresh');
                        if(id != 0) {
                            $('#exchange_rate').val(response.exchangeRate);
                        }
                    }
                }
            });
        });

        $('#currency').change(function() {
            var curId = $(this).val();
            $('#currency_id').val(curId);

            var companyCurrencyName = "{{$companyCurrency->currency_code}}";
            var currentCurrencyName = $('#currency option:selected').attr('data-currency-code');
            var companyCurrency = '{{ $companyCurrency->id }}';

            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: "{{ route('payments.account_list') }}",
                type: "GET",
                blockUI: true,
                data: { 'curId' : curId , _token: token},
                success: function(response) {
                    if (response.status == 'success') {
                        $('#bank_account_id').html(response.data);
                        $('#bank_account_id').selectpicker('refresh');
                        $('#exchange_rate').val(response.exchangeRate);
                        let currencyExchange = (companyCurrencyName != currentCurrencyName) ? '( '+currentCurrencyName+' @lang('app.to') '+companyCurrencyName+' )' : '';
                        $('#exchange_rateHelp').html(currencyExchange);
                    }
                }
            });
        });

        $('#payment_gateway_id').on('change', function() {
            var val = $(this).val();
            if (val == 'Offline'){
                var url = "{{ route('offline.methods') }}"
                $.easyAjax({
                url : url,
                type : "GET",
                success: function (response) {
                    if (response.status == 'success') {
                        $('#add_offline').removeClass('d-none');
                        var options = [];
                        var rData = [];
                        rData = response.data;
                            $.each(rData, function (index, value) {
                            var selectData = '';
                            if(value.status=='yes'){
                            selectData = '<option value="' + value.id + '">' + value.name + '</option>';
                            }
                            options.push(selectData);
                        });
                        $('#add_offline_methods').html(
                            options);
                        $('#add_offline_methods').selectpicker('refresh');
                    }

                }

                });

            }
            else
            {
                $('#add_offline').addClass('d-none');
            }
        });

        function ucWord(str){
            str = str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
                return letter.toUpperCase();
            });
            return str;
        }

        init(RIGHT_MODAL);
    });
</script>
