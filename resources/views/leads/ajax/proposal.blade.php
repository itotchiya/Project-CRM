@php
    use \Carbon\Carbon;
    use App\Models\GlobalSetting;
    $addProposalPermission = user()->permission('add_lead_proposals');
    $editProposalPermission = user()->permission('edit_lead_proposals');
    $addInvoicePermission = user()->permission('add_invoices');
    $deleteProposalPermission = user()->permission('delete_lead_proposals');
    $viewProposalPermission = user()->permission('view_lead_proposals');
@endphp

<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">
    @if ($addProposalPermission == 'all' || $addProposalPermission == 'added')
        <div class="row p-20">
            <div class="col-md-12">
                <a class="f-15 f-w-500" target="_blank" data-redirect-url="{{ url()->full() }}" href="{{ route('proposals.create').'?deal_id='.$deal->id }}" id="add-proposal">
                    <i class="icons icon-plus font-weight-bold mr-1"></i>@lang('modules.proposal.createProposal')
                </a>
            </div>
        </div>
    @endif


    <div class="d-flex flex-wrap p-20" id="task-file-list">
        @if ($viewProposalPermission == 'all' || $viewProposalPermission == 'added')
            <x-table headType="thead-light">
                <x-slot name="thead">
                    <th>@lang('modules.lead.proposal') @lang('app.number')</th>
                    <th>@lang('app.total')</th>
                    <th>@lang('app.date')</th>
                    <th>@lang('modules.estimates.validTill')</th>
                    <th>@lang('app.status')</th>
                    <th class="text-right">@lang('app.action')</th>
                </x-slot>

                @forelse ($proposals as $proposal)
                    <tr>
                        <td>
                            <a href="{{ route('proposals.show', $proposal->id) }}" target="_blank" style="color:black;">{{ $proposal->proposal_number }}</a>
                        </td>
                        <td>{{ currency_format($proposal->total, $proposal->currencyId) }}</td>
                        <td>{{ Carbon::parse($proposal->created_at)->translatedFormat(company()->date_format) }}</td>
                        <td>{{ Carbon::parse($proposal->valid_till)->translatedFormat(company()->date_format) }}</td>
                        <td>
                            @if ($proposal->status == 'waiting')
                                <i class="fa fa-circle mr-1 text-yellow f-10"></i>@lang('app.waiting')
                            @elseif ($proposal->status == 'accepted')
                                <i class="fa fa-circle mr-1 text-dark-green f-10"></i>@lang('app.accepted')
                            @elseif ($proposal->status == 'declined')
                                <i class="fa fa-circle mr-1 text-red f-10"></i>@lang('app.declined')
                            @endif
                            @if (!$proposal->send_status)
                                <span class="badge badge-secondary">@lang('modules.invoices.notSent')</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="dropdown ml-auto file-action">
                                <button class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                     aria-labelledby="dropdownMenuLink" tabindex="0">
                                    @if ($viewProposalPermission == 'all' || ($viewProposalPermission == 'added' && user()->id == $proposal->added_by) || ($viewProposalPermission == 'both' && user()->id == $proposal->added_by))
                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-1 px-3 view-proposal" target="_blank"
                                           href="{{ route('proposals.show', $proposal->id) }}">@lang('app.view')</a>
                                    @endif
                                    @if ($proposal->send_status)
                                        <a target="_blank" class="cursor-pointer d-block text-dark-grey f-13 py-1 px-3" href="{{ url()->temporarySignedRoute('front.proposal', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $proposal->hash) }}">
                                            @lang('modules.proposal.publicLink')
                                        </a>
                                    @endif
                                    <a class="cursor-pointer d-block text-dark-grey f-13 py-1 px-3" href="{{ route('proposals.download', [$proposal->id]) }}">
                                        @lang('app.download')
                                    </a>
                                    @if (!$proposal->signature && $editProposalPermission == 'all' || ($editProposalPermission == 'added' && $proposal->added_by == user()->id))
                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-1 px-3 edit-proposal" target="_blank"
                                           href="{{ route('proposals.edit', $proposal->id) }}" data-row-id="{{ $proposal->id }}">@lang('app.edit')</a>
                                    @endif
                                    @if ($proposal->status != 'declined')
                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-1 px-3 sendButton" href="javascript:;" data-toggle="tooltip"  data-proposal-id="{{ $proposal->id }}">
                                            @lang('app.send')
                                        </a>
                                    @endif
                                    @if ($proposal->status != 'declined' && $proposal->send_status == 0)
                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-1 px-3 sendButton" href="javascript:;" data-toggle="tooltip" data-proposal-id="{{ $proposal->id }}">
                                            @lang('app.markSent')
                                        </a>
                                    @endif
                                    @if ($addInvoicePermission == 'all' || ($addInvoicePermission == 'added' && user()->id == $proposal->added_by))
                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-1 px-3" target="_blank" href="{{ route('invoices.create').'?proposal='.$proposal->id }}">
                                            @lang('app.create') @lang('app.invoice')
                                        </a>
                                    @endif
                                    @if (!$proposal->signature && $deleteProposalPermission == 'all' || ($deleteProposalPermission == 'added' && $proposal->added_by == user()->id))
                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-1 px-3 delete-proposal-table-row"
                                           data-proposal-id="{{ $proposal->id }}"
                                           href="javascript:;">@lang('app.delete')</a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-cards.no-record :message="__('messages.noRecordFound')" icon="clipboard" />
                        </td>
                    </tr>
                @endforelse
            </x-table>
        @endif
    </div>


</div>
<!-- TAB CONTENT END -->
