<div class="row">
    <div class="col-sm-12">
        <x-cards.data :title="__('modules.payments.paymentDetails')" class=" mt-4">
            <x-cards.data-row :label="__('app.amount')"
                :value="currency_format($payment->amount, ($payment->currency ? $payment->currency->id : company()->currency_id))" />

            <x-cards.data-row :label="__('app.paymentOn')" :value="$payment->paid_on->translatedFormat(company()->date_format)" />

            <x-cards.data-row :label="__('app.invoice')"
                :value="(!is_null($payment->invoice_id)) ? $payment->invoice->invoice_number : '--'" />

            @if ($payment->order_id)
                <x-cards.data-row :label="__('app.order')"
                :value="$payment->order->order_number" />
            @endif
            <x-cards.data-row :label="__('app.project')"
                :value="(!is_null($payment->project_id)) ? $payment->project->project_name : '--'" />

            @php
                $bankName = isset($payment->transactions[0]) && $payment->transactions[0]->bankAccount->bank_name ? $payment->transactions[0]->bankAccount->bank_name.' |' : ''
            @endphp
            <x-cards.data-row :label="__('app.menu.bankaccount')"
            :value="(count($payment->transactions) > 0 ? $bankName.' '.$payment->transactions[0]->bankAccount->account_name : '--')" />

            <x-cards.data-row :label="__('app.transactionId')" :value="$payment->transaction_id ?? '--'" />

            @if ($payment->gateway == 'Offline' && $payment->offlineMethods && $payment->offlineMethods->name)
                <x-cards.data-row :label="__('app.gateway')" :value="$payment->gateway  ?  $payment->gateway .  ' ('. $payment->offlineMethods->name.')' : '--' "/>
            @else
                <x-cards.data-row :label="__('app.gateway')" :value="$payment->gateway   ?  $payment->gateway : '--' "/>
            @endif

            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                    @lang('app.status')</p>
                <p class="mb-0 text-dark-grey f-14 w-70">
                    @if ($payment->status == 'complete')
                        <i class="fa fa-circle mr-1 text-dark-green f-10"></i>
                    @elseif ($payment->status == 'pending')
                        <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                    @else
                        <i class="fa fa-circle mr-1 text-red f-10"></i>
                    @endif
                    @lang('app.'.$payment->status)
                </p>
            </div>

            @if ($payment->status =='failed' && !is_null($payment->payment_gateway_response))
                <x-cards.data-row :label="__('app.reason')" :value="$payment->payment_gateway_response->message" />
            @endif

            <div class="col-12 px-0 pb-3 d-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                    @lang('app.receipt')</p>
                <p class="mb-0 text-dark-grey f-14">
                    @if (!is_null($payment->bill))
                        <a target="_blank" class="text-dark-grey"
                            href="{{ $payment->file_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewReceipt')</u></a>
                    @else
                        --
                    @endif
                </p>
            </div>

            <x-cards.data-row :label="__('app.remark')" :value="$payment->remarks ?? '--'" />


        </x-cards.data>
    </div>
</div>
