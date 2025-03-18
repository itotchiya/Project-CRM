<div class="row">
    <div class="col-sm-12">
        <x-form id="save-bankaccount-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.menu.addBankAccount')</h4>
                <div class="row p-20">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3" for="usr">@lang('modules.bankaccount.type')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="type-bank" class="type" :fieldLabel="__('modules.bankaccount.bank')" fieldValue="bank"
                                    fieldName="type" checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="type-cash" class="type" :fieldLabel="__('modules.bankaccount.cash')" fieldValue="cash"
                                    fieldName="type">
                                </x-forms.radio>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 bank_name">
                        <x-forms.text fieldId="bank_name" :fieldLabel="__('modules.bankaccount.bankName')"
                            fieldName="bank_name" fieldRequired="true"
                            :fieldPlaceholder="__('placeholders.bankaccount.bankName')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="account_name" :fieldLabel="__('modules.bankaccount.accountName')"
                            fieldName="account_name" fieldRequired="true"
                            :fieldPlaceholder="__('placeholders.bankaccount.accountName')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4 accountNumber">
                        <x-forms.text fieldId="account_number" :fieldLabel="__('modules.bankaccount.accountNumber')" fieldName="account_number"
                            :fieldPlaceholder="__('placeholders.bankaccount.accountNumber')" fieldRequired="true"></x-forms.text>
                    </div>

                    <div class="col-md-4 accountType">
                        <x-forms.select fieldId="account_type" :fieldLabel="__('modules.bankaccount.accountType')" fieldName="account_type"
                            search="true">
                            <option value="saving">@lang('modules.bankaccount.saving')</option>
                            <option value="current">@lang('modules.bankaccount.current')</option>
                            <option value="credit-card">@lang('modules.bankaccount.credit-card')</option>
                            <option value="loan">@lang('modules.bankaccount.loan')</option>
                            <option value="overdraft">@lang('modules.bankaccount.overdraft')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="currency_id" :fieldLabel="__('app.currency')" fieldName="currency_id"
                            search="true" fieldRequired="true">
                                <option value="">--</option>
                                @foreach ($currencies as $currency)
                                    <option @if ($currency->id == company()->currency_id) selected @endif value="{{ $currency->id }}">
                                        {{ $currency->currency_code . ' (' . $currency->currency_symbol . ')' }}
                                    </option>
                                @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.tel fieldId="contact_number" :fieldLabel="__('modules.bankaccount.contactNumber')" fieldName="contact_number"
                           :fieldPlaceholder="__('placeholders.mobile')" fieldRequired="true"></x-forms.tel>
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldId="opening_balance" :fieldLabel="__('modules.bankaccount.openingBalance')" fieldName="opening_balance"
                            :fieldPlaceholder="__('placeholders.price')" fieldRequired="true"></x-forms.number>
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status" fieldRequired="true"
                        search="true">
                            <option value="">--</option>
                            <option value="1">@lang('app.active')</option>
                            <option value="0">@lang('app.inactive')</option>
                    </x-forms.select>
                    </div>

                    <div class="col-md-12 bankLogo">
                        <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2"
                            :fieldLabel="__('modules.bankaccount.bankLogo')" fieldName="bank_logo"
                            fieldId="bank_logo" :popover="__('modules.themeSettings.logoSize')">
                        </x-forms.file>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-bankaccount" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('bankaccounts.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function(){

        $('#save-bankaccount').click(function() {
        const url = "{{ route('bankaccounts.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-bankaccount-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-bankaccount",
                data: $('#save-bankaccount-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        });

       // show/hide bank accoun detail
        $('.type').change(function () {
            $('.bank_name').toggleClass('d-none');
            $('.accountNumber').toggleClass('d-none');
            $('.bankLogo').toggleClass('d-none');
            $('.accountType').toggleClass('d-none');
        });


        init(RIGHT_MODAL);
    })

</script>
