
<div id="notice-detail-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey  justify-content-between p-20">
                    <div class="row">
                        <div class="col-lg-10 col-10">
                            <h3 class="heading-h1">@lang('app.bankTransactionDetails')</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($bankTransaction->bankAccount->type == 'bank')
                        <x-cards.data-row :label="__('modules.bankaccount.bankName')" :value="$bankTransaction->bankAccount->bank_name ? $bankTransaction->bankAccount->bank_name : '----'" />
                    @endif
                    <x-cards.data-row :label="__('modules.bankaccount.accountName')" :value="$bankTransaction->bankAccount->account_name" />

                    @if($bankTransaction->bankAccount->type == 'bank')
                        <x-cards.data-row :label="__('modules.bankaccount.accountNumber')" :value="$bankTransaction->bankAccount->account_number ? $bankTransaction->bankAccount->account_number : '----'" />
                    @endif

                    <x-cards.data-row :label="__('modules.bankaccount.contactNumber')" :value="$bankTransaction->bankAccount->contact_number" />
                    <x-cards.data-row :label="__('app.amount')" :value="currency_format($bankTransaction->amount, ($bankTransaction->bankAccount->currency ? $bankTransaction->bankAccount->currency->id : company()->currency->id))" />

                    @if ($bankTransaction->type == 'Cr')
                        <x-cards.data-row :label="__('modules.bankaccount.type')" html="true" value="<span class='badge badge-success'>{{ __('modules.bankaccount.credit') }}</span>" />
                    @else
                        <x-cards.data-row :label="__('modules.bankaccount.type')" html="true" value="<span class='badge badge-danger'>{{ __('modules.bankaccount.debit') }}</span>" />
                    @endif

                    <x-cards.data-row :label="__('modules.bankaccount.transactionDate')" :value="\Carbon\Carbon::parse($bankTransaction->transaction_date)->translatedFormat(company()->date_format)" />

                    <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">@lang('modules.bankaccount.relation')</p>
                        <p class="mb-0 text-dark-grey f-14 w-70">
                                @if(!is_null($bankTransaction->payment_id))
                                    <a href="{{ route('payments.show', $bankTransaction->payment_id) }}" class="text-darkest-grey openRightModal">@lang('app.view')
                                       @lang('app.relatedPayment')</a><br>
                                @elseif ($type == 'payment')
                                    @lang('app.payment')<br>
                                @endif
                                @if (!is_null($bankTransaction->invoice_id))
                                    <a href="{{ route('invoices.show', $bankTransaction->invoice_id) }}" class="text-darkest-grey">@lang('app.view')
                                        @lang('app.relatedInvoice')</a><br>
                                @endif
                                @if (!is_null($bankTransaction->expense_id))
                                    <a href="{{ route('expenses.show', $bankTransaction->expense_id) }}" class="text-darkest-grey openRightModal">@lang('app.view')
                                        @lang('app.relatedExpense')</a>
                                @elseif ($type == 'expense')
                                    @lang('app.expense')
                                @endif
                                @if(is_null($bankTransaction->payment_id) && is_null($bankTransaction->invoice_id) && is_null($bankTransaction->expense_id) && $type == 'bank')
                                    --
                                @endif
                        </p>
                    </div>

                    <x-cards.data-row :label="__('modules.bankaccount.memo')" :value="$bankTransaction->memo ? $bankTransaction->memo : '--'" />
                </div>
            </div>
        </div>
    </div>
</div>
