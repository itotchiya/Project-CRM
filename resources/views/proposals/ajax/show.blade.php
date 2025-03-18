<style>
    #logo {
        height: 50px;
    }

    .signature_wrap {
        position: relative;
        height: 150px;
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        user-select: none;
        width: 400px;
    }

    .signature-pad {
        position: absolute;
        left: 0;
        top: 0;
        width: 400px;
        height: 150px;
    }

</style>

@if(!in_array('client', user_roles()))
    @if(!is_null($invoice->last_viewed))
        <x-alert type="info">
            @lang('app.viewedOn') {{$invoice->last_viewed->timezone($settings->timezone)->translatedFormat($settings->date_format)}}
            @lang('app.at') {{$invoice->last_viewed->timezone($settings->timezone)->translatedFormat($settings->time_format)}}
            @lang('app.usingIpAddress'):{{$invoice->ip_address}}

            @if (request()->ip() == $invoice->ip_address)
                <strong>(@lang('modules.invoices.sameIp'))</strong>
            @endif
        </x-alert>
    @endif
@endif

<!-- INVOICE CARD START -->

<div class="card border-0 invoice">
    <!-- CARD BODY START -->
    <div class="card-body">
        <div class="invoice-table-wrapper">
            <table width="100%" class="">
                <tr class="inv-logo-heading">
                    <td><img src="{{ invoice_setting()->logo_url }}" alt="{{ company()->company_name }}"
                            id="logo" /></td>
                    <td align="right" class="font-weight-bold f-21 text-dark text-uppercase mt-4 mt-lg-0 mt-md-0">
                        @lang('modules.lead.proposal')</td>
                </tr>
                <tr class="inv-num">
                    <td class="f-14 text-dark">
                        <p class="mt-3 mb-0">
                            {{ company()->company_name }}<br>
                            @if (!is_null($settings))
                                {!! nl2br(default_address()->address) !!}<br>
                                {{ company()->company_phone }}
                            @endif
                            @if ($invoiceSetting->show_gst == 'yes' && !is_null($invoiceSetting->gst_number))
                                <br>@lang('app.gstIn'): {{ $invoiceSetting->gst_number }}
                            @endif
                        </p><br>
                    </td>
                    <td align="right">
                        <table class="inv-num-date text-dark f-13 mt-3">
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">
                                    @lang('modules.lead.proposal')</td>
                                <td class="border-left-0">{{ $invoice->proposal_number }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">
                                    @lang('modules.estimates.validTill')</td>
                                <td class="border-left-0">{{ $invoice->valid_till->translatedFormat(company()->date_format) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">
                                    @lang('app.date')</td>
                                <td class="border-left-0">
                                    {{ $invoice->created_at->translatedFormat(company()->date_format) }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td height="20"></td>
                </tr>
            </table>
            <table width="100%">
                <tr class="inv-unpaid">
                    <td class="f-14 text-dark">
                        @if ($invoice->lead && ($invoice->lead->contact->client_name || $invoice->lead->contact->client_email || $invoice->lead->contact->mobile || $invoice->lead->contact->company_name || $invoice->lead->contact->address) && (invoice_setting()->show_client_name == 'yes' || invoice_setting()->show_client_email == 'yes' || invoice_setting()->show_client_phone == 'yes' || invoice_setting()->show_client_company_name == 'yes' || invoice_setting()->show_client_company_address == 'yes'))
                        <p class="mb-0 text-left">
                            <span class="text-dark-grey ">
                                @lang("modules.invoices.billedTo")
                            </span><br>

                            @if ($invoice->deal && !empty($invoice->deal->name))
                                {{ $invoice->deal->name }}<br>
                            @endif
                            @if ($invoice->lead->contact && $invoice->lead->contact->client_name && invoice_setting()->show_client_name == 'yes')
                                {{ $invoice->lead->contact->client_name_salutation }}<br>
                            @endif
                            @if ($invoice->lead->contact && $invoice->lead->contact->client_email && invoice_setting()->show_client_email == 'yes')
                                {{ $invoice->lead->contact->client_email }}<br>
                            @endif
                            @if ($invoice->lead->contact && $invoice->lead->contact->mobile && invoice_setting()->show_client_phone == 'yes')
                                {{ $invoice->lead->contact->mobile }}<br>
                            @endif
                            @if ($invoice->lead->contact && $invoice->lead->contact->company_name && invoice_setting()->show_client_company_name == 'yes')
                                {{ $invoice->lead->contact->company_name }}<br>
                            @endif
                            @if ($invoice->lead->contact && $invoice->lead->contact->address && invoice_setting()->show_client_company_address == 'yes')
                                {!! nl2br($invoice->lead->contact->address) !!}
                            @endif
                        </p>
                        @endif
                    </td>

                    <td align="right" class="mt-4 mt-lg-0 mt-md-0">
                        <span
                            class="unpaid {{ $invoice->status == 'waiting' ? 'text-warning border-warning' : '' }} {{ $invoice->status == 'accepted' ? 'text-success border-success' : '' }} rounded f-15 ">@lang('modules.proposal.'.$invoice->status)</span>
                    </td>
                </tr>
                <tr>
                    <td height="30" colspan="2"></td>
                </tr>
            </table>
            <div class="row">
                <div class="col-sm-12 ql-editor">
                    {!! $invoice->description !!}
                </div>
            </div>
            @if (count($invoice->items) > 0)
                <table width="100%" class="inv-desc d-none d-lg-table d-md-table">
                    <tr>
                        <td colspan="2">
                            <table class="inv-detail f-14 table-responsive-sm" width="100%">
                                <tr class="i-d-heading bg-light-grey text-dark-grey font-weight-bold">
                                    <td class="border-right-0" width="35%">@lang('app.description')</td>
                                    @if($invoiceSetting->hsn_sac_code_show == 1)
                                        <td class="border-right-0 border-left-0" align="right">@lang("app.hsnSac")</td>
                                    @endif
                                    <td class="border-right-0 border-left-0" align="right">@lang('modules.invoices.qty')</td>
                                    <td class="border-right-0 border-left-0" align="right">
                                        @lang("modules.invoices.unitPrice") ({{ $invoice->currency->currency_code }})
                                    </td>
                                    <td class="border-right-0 border-left-0" align="right">
                                        @lang("modules.invoices.tax")
                                    </td>
                                    <td class="border-left-0" align="right">
                                        @lang("modules.invoices.amount")
                                        ({{ $invoice->currency->currency_code }})</td>
                                </tr>
                                @foreach ($invoice->items->sortBy('field_order') as $item)
                                    @if ($item->type == 'item')
                                        <tr class="text-dark font-weight-semibold f-13">
                                            <td>{{ $item->item_name }}</td>
                                            @if($invoiceSetting->hsn_sac_code_show == 1)
                                                <td align="right">{{ $item->hsn_sac_code }}</td>
                                            @endif
                                            <td align="right">{{ $item->quantity }}@if($item->unit)<br><span class="f-11 text-dark-grey">{{ $item->unit->unit_type }}</span>@endif</td>
                                            <td align="right">
                                                {{ currency_format($item->unit_price, $invoice->currency_id, false) }}
                                            </td>
                                            <td align="right">
                                                {{ $item->tax_list }}
                                            </td>
                                            <td align="right">{{ currency_format($item->amount, $invoice->currency_id, false) }}
                                            </td>
                                        </tr>
                                        @if ($item->item_summary || $item->proposalItemImage)
                                            <tr class="text-dark f-12">
                                                <td colspan="6" class="border-bottom-0">
                                                    {!! nl2br(strip_tags($item->item_summary)) !!}
                                                    @if ($item->proposalItemImage)
                                                        <p class="mt-2">
                                                            <a href="javascript:;" class="img-lightbox" data-image-url="{{ $item->proposalItemImage->file_url }}">
                                                                <img src="{{ $item->proposalItemImage->file_url }}" width="80" height="80" class="img-thumbnail">
                                                            </a>
                                                        </p>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @endif
                                @endforeach

                                <tr>
                                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '4' : '3' }}" class="blank-td border-bottom-0 border-left-0 border-right-0"></td>
                                    <td class="p-0 border-right-0" align="right">
                                        <table width="100%">
                                            <tr class="text-dark-grey" align="right">
                                                <td class="w-50 border-top-0 border-left-0">
                                                    @lang("modules.invoices.subTotal")</td>
                                            </tr>
                                            @if ($discount != 0 && $discount != '')
                                                <tr class="text-dark-grey" align="right">
                                                    <td class="w-50 border-top-0 border-left-0">
                                                        @lang("modules.invoices.discount"): {{$discountType}}</td>
                                                </tr>
                                            @endif
                                            @foreach ($taxes as $key => $tax)
                                                <tr class="text-dark-grey" align="right">
                                                    <td class="w-50 border-top-0 border-left-0">
                                                        {{ $key }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-light-grey text-dark f-w-500 f-16" align="right">
                                                <td class="w-50 border-bottom-0 border-left-0">
                                                    @lang("modules.invoices.total")</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td class="p-0 border-right-0" align="right">
                                        <table width="100%">
                                            <tr class="text-dark-grey" align="right">
                                                <td class="border-top-0 border-left-0">
                                                    {{ currency_format($invoice->sub_total, $invoice->currency_id, false) }}</td>
                                            </tr>
                                            @if ($discount != 0 && $discount != '')
                                                <tr class="text-dark-grey" align="right">
                                                    <td class="border-top-0 border-left-0">
                                                        {{ currency_format($discount, $invoice->currency_id, false) }}</td>
                                                </tr>
                                            @endif
                                            @foreach ($taxes as $key => $tax)
                                                <tr class="text-dark-grey" align="right">
                                                    <td class="border-top-0 border-left-0">
                                                        {{ currency_format($tax, $invoice->currency_id, false) }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-light-grey text-dark f-w-500 f-16" align="right">
                                                <td class="border-bottom-0 border-left-0">
                                                    {{ currency_format($invoice->total, $invoice->currency_id, false) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>

                    </tr>
                </table>
                <table width="100%" class="inv-desc-mob d-block d-lg-none d-md-none">

                    @foreach ($invoice->items->sortBy('field_order') as $item)
                        @if ($item->type == 'item')

                            <tr>
                                <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                    @lang('app.description')</th>
                                <td class="p-0 ">
                                    <table>
                                        <tr width="100%" class="font-weight-semibold f-13">
                                            <td class="border-left-0 border-right-0 border-top-0">
                                                {{ $item->item_name }}</td>
                                        </tr>
                                        @if ($item->item_summary != '' || $item->proposalItemImage)
                                            <tr>
                                                <td class="border-left-0 border-right-0 border-bottom-0 f-12">
                                                    {!! nl2br(strip_tags($item->item_summary)) !!}
                                                    @if ($item->proposalItemImage)
                                                        <p class="mt-2">
                                                            <a href="javascript:;" class="img-lightbox" data-image-url="{{ $item->proposalItemImage->file_url }}">
                                                                <img src="{{ $item->proposalItemImage->file_url }}" width="80" height="80" class="img-thumbnail">
                                                            </a>
                                                        </p>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                    @lang('modules.invoices.qty')
                                </th>
                                <td width="50%">{{ $item->quantity }}</td>
                            </tr>
                            <tr>
                                <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                    @lang("modules.invoices.unitPrice")
                                    ({{ $invoice->currency->currency_code }})</th>
                                <td width="50%">{{ currency_format($item->unit_price, $invoice->currency_id, false) }}</td>
                            </tr>
                            <tr>
                                <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                    @lang("modules.invoices.amount")
                                    ({{ $invoice->currency->currency_code }})</th>
                                <td width="50%">{{ currency_format($item->amount, $invoice->currency_id, false) }}</td>
                            </tr>
                            <tr>
                                <td height="3" class="p-0 " colspan="2"></td>
                            </tr>
                        @endif
                    @endforeach

                    <tr>
                        <th width="50%" class="text-dark-grey font-weight-normal">@lang("modules.invoices.subTotal")
                        </th>
                        <td width="50%" class="text-dark-grey font-weight-normal">
                            {{ currency_format($invoice->sub_total, $invoice->currency_id, false) }}</td>
                    </tr>
                    @if ($discount != 0 && $discount != '')
                        <tr>
                            <th width="50%" class="text-dark-grey font-weight-normal">@lang("modules.invoices.discount")
                            </th>
                            <td width="50%" class="text-dark-grey font-weight-normal">
                                {{ currency_format($discount, $invoice->currency_id, false) }}</td>
                        </tr>
                    @endif

                    @foreach ($taxes as $key => $tax)
                        <tr>
                            <th width="50%" class="text-dark-grey font-weight-normal">{{ $key }}</th>
                            <td width="50%" class="text-dark-grey font-weight-normal">
                                {{ currency_format($tax, $invoice->currency_id, false) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <th width="50%" class="text-dark-grey font-weight-bold">@lang("modules.invoices.total")</th>
                        <td width="50%" class="text-dark-grey font-weight-bold">
                            {{ currency_format( $invoice->total, $invoice->currency_id, false) }}</td>
                    </tr>
                </table>
                <table class="inv-note">
                    <tr>
                        <td height="30" colspan="2"></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: text-top">
                            <table>
                                <tr>@lang('app.note')</tr>
                                <tr>
                                    <p class="text-dark-grey">{!! !empty($invoice->note) ? nl2br($invoice->note) : '--' !!}</p>
                                </tr>
                            </table>
                        </td>
                        <td align="right">
                            <table>
                                <tr>@lang('modules.invoiceSettings.invoiceTerms')</tr>
                                <tr>
                                    <p class="text-dark-grey">{!! nl2br($invoiceSetting->invoice_terms) !!}</p>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if (isset($invoiceSetting->other_info))
                        <tr>
                            <td align="vertical-align: text-top">
                                <table>
                                    <tr>
                                        <p class="text-dark-grey">{!! nl2br($invoiceSetting->other_info) !!}
                                        </p>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td>
                            @if (isset($taxes) && invoice_setting()->tax_calculation_msg == 1)
                                <p class="text-dark-grey">
                                    @if ($invoice->calculate_tax == 'after_discount')
                                        @lang('messages.calculateTaxAfterDiscount')
                                    @else
                                        @lang('messages.calculateTaxBeforeDiscount')
                                    @endif
                                </p>
                            @endif
                        </td>
                    </tr>
                </table>
            @endif
        </div>

        @if ($invoice->signature)
            <div class="row">
                <div class="col-sm-12 mt-4">
                    @if (!is_null($invoice->signature->signature))
                        <img src="{{ $invoice->signature->signature }}" style="width: 200px;">
                        <h6>@lang('modules.estimates.signature')</h6>
                    @else
                        <h6>@lang('modules.estimates.signedBy')</h6>
                    @endif
                    <p>({{ $invoice->signature->full_name }})</p>
                </div>
            </div>
        @endif

         @if ($invoice->client_comment)
             <div class="row">
                <div class="col-md-12">
                    <hr>
                    <h4 class="name heading-h4" style="margin-bottom: 20px;">@lang('app.rejectReason')</h4>
                    <p> {{ $invoice->client_comment }} </p>
                </div>
            </div>
        @endif

    </div>
    <!-- CARD BODY END -->
    <!-- CARD FOOTER START -->
    <div class="card-footer bg-white border-0 d-flex justify-content-start py-0 py-lg-4 py-md-4 mb-4 mb-lg-3 mb-md-3 ">

        <div class="d-flex">
            <div class="inv-action mr-3 mr-lg-3 mr-md-3 dropup">
                <button class="dropdown-toggle btn-secondary" type="button" id="dropdownMenuButton"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@lang('app.action')
                    <span><i class="fa fa-chevron-down f-15 text-dark-grey"></i></span>
                </button>
                <!-- DROPDOWN - INFORMATION -->
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton" tabindex="0">
                    <li>
                        <a class="dropdown-item f-14 text-dark"
                            href="{{ url()->temporarySignedRoute('front.proposal', now()->addDays(\App\Models\GlobalSetting::SIGNED_ROUTE_EXPIRY), $invoice->hash) }}" target="_blank">
                            <i class="fa fa-link f-w-500 mr-2 f-11"></i> @lang('modules.proposal.publicLink')
                        </a>
                        <a class="dropdown-item f-14 text-dark"
                            href="{{ route('proposals.download', [$invoice->id]) }}">
                            <i class="fa fa-download f-w-500 mr-2 f-11"></i> @lang('app.download')
                        </a>
                    </li>
                    @if (!$invoice->signature || $invoice->status == 'waiting')
                        <li>
                            <a class="dropdown-item openRightModal"
                                href="{{ route('proposals.edit', [$invoice->id]) }}">
                                <i class="fa fa-edit f-w-500 mr-2 f-11"></i> @lang('app.edit')
                            </a>
                        </li>
                    @endif
                    @if (!$invoice->signature || $firstProposal->id == $invoice->id)
                            <li>
                                <a class="dropdown-item delete-table-row" href="javascript:;"
                                    data-proposal-id="{{ $invoice->id }}">
                                    <i class="fa fa-trash mr-2"></i>@lang('app.delete')
                                </a>
                            </li>
                    @endif
                </ul>
            </div>

            <x-forms.button-cancel :link="route('proposals.index')" class="border-0">
                @lang('app.cancel')
            </x-forms.button-cancel>
        </div>


    </div>
    <!-- CARD FOOTER END -->
</div>
<!-- INVOICE CARD END -->

@push('scripts')
    <script>

        $('body').on('click', '.delete-table-row', function() {
                var id = $(this).data('proposal-id');

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
                        var url = "{{ route('proposals.destroy', ':id') }}";
                        url = url.replace(':id', id);

                        var token = "{{ csrf_token() }}";

                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            blockUI: true,
                            data: {
                                '_token': token,
                                '_method': 'DELETE'
                            },
                            success: function(response) {
                                if (response.status == "success") {
                                    window.location.href = "{{ route('proposals.index') }}";
                                }
                            }
                        });
                    }
                });
            });
    </script>
@endpush
