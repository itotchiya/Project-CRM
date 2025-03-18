@if($invoice->invoicePaymentDetail)
    <table class="inv-detail f-14 table-responsive-sm mt-4 b-collapse" width="50%">
        <thead class="thead-light">
        <tr style=" font-size: 14px;" class="i-d-heading bg-light-grey text-dark-grey font-weight-bold main-table-heading text-grey">
            <th class="description f-14 " style="text-align: left; padding: 10px; border: 1px solid #e7e9eb;">@lang('modules.invoices.paymentDetails')</th>
            @if($invoice->invoicePaymentDetail->image)<th></th>@endif
        </tr>
        </thead>
        <tbody>
        <tr class="main-table-items text-black">
            <td class="description f-14 text-dark" style="padding: 10px;" width="60%">
                <p><span  class="float-left"> <strong>{{ $invoice->invoicePaymentDetail->title }}</strong><br>
                                                    {!! !empty($invoice->invoicePaymentDetail->payment_details)
                                                    ? nl2br(e($invoice->invoicePaymentDetail->payment_details)) : '--' !!}</span>


                </p>
            </td>
            @if($invoice->invoicePaymentDetail->image)<td class="text-center"><span > <img src="{{$invoice->invoicePaymentDetail->image_url}}" height="150px" width="150px"/></span></td>@endif
        </tr>
        </tbody>
    </table>
@endif
