<x-form id="addCurrency">
    <div class="modal-header">
        <h5 class="modal-title" id="modelHeading">@lang('modules.currencySettings.addNewCurrency')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">×</span></button>
    </div>
    <div class="modal-body">


            {{-- Used for show currency error inline --}}
            <div class="row">
                <div class="col-sm-12" id="alert">
                </div>
            </div>
            <div class="row">

                <div class=" col-sm-12 col-lg-4">
                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2"
                                  :fieldLabel="__('modules.currencySettings.currencyName')"
                                  :fieldPlaceholder="__('placeholders.currency.currencyName')"
                                  fieldName="currency_name"
                                  fieldId="currency_name" fieldRequired="true"></x-forms.text>
                </div>

                <div class="col-sm-12 col-lg-4">
                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2"
                                  :fieldLabel="__('modules.currencySettings.currencySymbol')"
                                  :fieldPlaceholder="__('placeholders.currency.currencySymbol')"
                                  fieldName="currency_symbol"
                                  fieldId="currency_symbol" fieldRequired="true"></x-forms.text>
                </div>

                <div class="col-sm-12 col-lg-4">
                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2"
                                  :fieldLabel="__('modules.currencySettings.currencyCode')"
                                  :fieldPlaceholder="__('placeholders.currency.currencyCode')"
                                  fieldName="currency_code"
                                  fieldId="currency_code" fieldRequired="true"></x-forms.text>
                </div>

                <div class="col-sm-12 col-lg-4">
                    <div class="form-group my-3">
                        <label class="f-14 text-dark-grey mb-12 w-100"
                               for="usr">@lang('modules.currencySettings.isCryptoCurrency')</label>
                        <div class="d-flex">
                            <x-forms.radio fieldId="crypto_currency_yes" :fieldLabel="__('app.yes')"
                                           fieldName="is_cryptocurrency" fieldValue="yes">
                            </x-forms.radio>
                            <x-forms.radio fieldId="crypto_currency_no" :fieldLabel="__('app.no')" fieldValue="no"
                                           fieldName="is_cryptocurrency" checked>
                            </x-forms.radio>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-lg-4 crypto-currency" style="display: none">
                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.currencySettings.usdPrice')"
                                  :fieldPlaceholder="__('placeholders.price')" fieldName="usd_price"
                                  fieldId="usd_price" fieldRequired="true">
                    </x-forms.text>
                </div>

                <div class="col-sm-12 col-lg-6 regular-currency">
                    <x-forms.number class="mr-0 mr-lg-2 mr-md-2"
                                    :fieldLabel="__('modules.currencySettings.exchangeRate')"
                                    :fieldPlaceholder="__('placeholders.price')" fieldName="exchange_rate"
                                    fieldId="exchange_rate" fieldRequired="true" fieldHelp="( {{company()->currency->currency_code}} {{__('app.to')}} {{company()->currency->currency_code}} )">
                    </x-forms.number>

                    @if(global_setting()->currency_converter_key !=='')
                        <a href="javascript:;" class="fetch-exchange-rate" icon="key"><i class="fa fa-key"></i>
                            @lang('modules.currencySettings.fetchLatestExchangeRate')
                        </a>
                    @else
                        @lang('messages.configureCurrencyConverterKey',['link'=> '<a href="javascript:;" class="fetch-exchange-rate" icon="key"><i class="fa fa-key"></i> '.__("app.clickHere").'</a>'])
                    @endif
                </div>

            </div>
            <div class="col-12 p-0 mt-4">
                <h5 class="mb-0 pt-3  border-top-grey">@lang('modules.accountSettings.currencyFormatSetting')</h5>
            </div>
            <div class="row pt-3">
                <div class="col-lg-6">
                    <x-forms.select fieldId="currency_position"
                                    :fieldLabel="__('modules.currencySettings.currencyPosition')"
                                    fieldName="currency_position"
                                    :popover="__('messages.currency.currencyPosition')">
                        <option
                            @if ($currencyFormatSetting->currency_position == 'left') selected @endif
                        value="left">@lang('modules.currencySettings.left')</option>
                        <option @if ($currencyFormatSetting->currency_position == 'right') selected
                                @endif value="right">@lang('modules.currencySettings.right')</option>
                        <option @if ($currencyFormatSetting->currency_position == 'left_with_space') selected
                                @endif value="left_with_space">@lang('modules.currencySettings.leftWithSpace')</option>
                        <option @if ($currencyFormatSetting->currency_position == 'right_with_space') selected
                                @endif value="right_with_space">@lang('modules.currencySettings.rightWithSpace')</option>
                    </x-forms.select>
                </div>
                <div class="col-lg-6">
                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2"
                                    :fieldLabel="__('modules.currencySettings.thousandSeparator')"
                                    :fieldPlaceholder="__('placeholders.currency.thousandSeparator')"
                                    fieldName="thousand_separator" fieldId="thousand_separator"
                                    :popover="__('messages.currency.thousandSeparator')"
                                    :fieldValue="$currencyFormatSetting->thousand_separator"></x-forms.text>
                </div>
                <div class="col-lg-6">
                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.currencySettings.decimalSeparator')"
                                    :fieldPlaceholder="__('placeholders.currency.decimalSeparator')"
                                    fieldName="decimal_separator" fieldId="decimal_separator"
                                    :popover="__('messages.currency.decimalSeparator')"
                                    :fieldValue="$currencyFormatSetting->decimal_separator"></x-forms.text>
                </div>
                <div class="col-lg-6">
                    <x-forms.number class="mr-0 mr-lg-2 mr-md-2"
                                    :fieldLabel="__('modules.accountSettings.numberOfdecimals')" fieldName="no_of_decimal"
                                    fieldId="no_of_decimal" :popover="__('messages.currency.numberOfdecimals')"
                                    :fieldValue="$currencyFormatSetting->no_of_decimal"/>
                </div>
            </div>


        <div class="col-12 p-0 mt-4">
            <p class="ntfcn-tab-content-left w-100 pt-3 px-1 border-top-grey">@lang('modules.currencySettings.sample') - <span id="formatted_currency">{{ $defaultFormattedCurrency }}</span>
            </p>
        </div>
    </div>
    <!-- SETTINGS END -->

    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0">@lang('app.cancel')
        </x-forms.button-cancel>
        <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
        </x-forms.button-primary>
    </div>
</x-form>
<script>
    $(".select-picker").selectpicker();

    $(document).ready(function () {

        // Toggle between Usd Price field
        $("input[name=is_cryptocurrency]").click(function () {
            if ($(this).val() == 'yes') {
                $('.regular-currency').hide();
                $('.crypto-currency').show();
            } else {
                $('.crypto-currency').hide();
                $('.regular-currency').show();
            }
        })

        // Save form data
        $('#save-form').click(function () {
            $.easyAjax({
                url: "{{ route('currency-settings.store') }}",
                container: '#addCurrency',
                type: "POST",
                blockUI: true,
                redirect: true,
                disableButton: true,
                buttonSelector: "#save-form",
                data: $('#addCurrency').serialize(),
                success: function (response) {
                    if (response.status == 'success') {
                        window.location.reload();
                    }
                }
            })
        });

        $('.fetch-exchange-rate').click(function () {

            let currencyConverterKey = '{{ global_setting()->currency_converter_key }}';

            if (currencyConverterKey === "") {
                addCurrencyExchangeKey();
                return false;
            }

            let currencyCode = $('#currency_code').val();
            let url = "{{ route('currency_settings.exchange_rate', '#cc') }}";
            url = url.replace('#cc', currencyCode);

            $.easyAjax({
                url: url,
                type: "GET",
                data: {
                    currencyCode: currencyCode
                },
                disableButton: true,
                blockUI: true,
                messagePosition: 'inline',
                success: function (response) {
                    $('#exchange_rate').val(response);
                }
            })
        });

        function addCurrencyExchangeKey() {
            const url = "{{ route('currency_settings.exchange_key') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        }

        $("body").on("change keyup",  "#currency_symbol, #currency_code, #currency_position, #thousand_separator, #decimal_separator, #no_of_decimal", function () {
            let number = 1234567.89;
            let no_of_decimal = $('#no_of_decimal').val();
            let thousand_separator = $('#thousand_separator').val();
            let currency_position = $('#currency_position').val();
            let decimal_separator = $('#decimal_separator').val();
            var companyCurrencyName = "{{company()->currency->currency_code}}";


            var currentCurrencyName = $('#currency_code').val();

            if(currentCurrencyName == ''){
                $('#exchange_rateHelp').html('( '+companyCurrencyName+' @lang('app.to') '+companyCurrencyName+' )');
            }
            else {
                $('#exchange_rateHelp').html('( '+currentCurrencyName+' @lang('app.to') '+companyCurrencyName+' )');
            }

            let formatted_currency = number_format(number, no_of_decimal, decimal_separator, thousand_separator, currency_position);
            $('#formatted_currency').html(formatted_currency);
        });

        function number_format(number, decimals, dec_point, thousands_sep, currency_position) {

            // Strip all characters but numerical ones.
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');

            var currency_symbol = $('#currency_symbol').val();

            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }

            // number = dec_point == '' ? s[0] : s.join(dec);

            number = s.join(dec);

            switch (currency_position) {
                case 'left':
                    number = currency_symbol + number;
                    break;
                case 'right':
                    number = number + currency_symbol;
                    break;
                case 'left_with_space':
                    number = currency_symbol + ' ' + number;
                    break;
                case 'right_with_space':
                    number = number + ' ' + currency_symbol;
                    break;
                default:
                    number = currency_symbol + number;
                    break;
            }
            return number;
        }

        init('#addCurrency');

    });


</script>
