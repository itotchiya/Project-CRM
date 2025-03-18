@php
$addLeadFollowUpPermission = user()->permission('add_lead_follow_up');
$viewLeadFollowUpPermission = user()->permission('view_lead_follow_up');
$editLeadFollowUpPermission = user()->permission('edit_lead_follow_up');
$deleteLeadFollowUpPermission = user()->permission('delete_lead_follow_up');
@endphp

<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">
    <div class="d-flex p-20">
        @if ($deal->leadStage->slug == 'win' || $deal->leadStage->slug == 'lost')
            <x-alert type="info" icon="info-circle">@lang('messages.cantAddFollowup') </x-alert>
        @endif
        @if ($deal->leadStage->slug != 'win' && $deal->leadStage->slug != 'lost' && ($addLeadFollowUpPermission == 'all' || $addLeadFollowUpPermission == 'added'))
            <div class="row">
                <div class="col-md-12">
                    <a class="f-15 f-w-500" href="javascript:;" id="add-lead-followup"><i
                            class="icons icon-plus font-weight-bold mr-1"></i>@lang('modules.followup.newFollowUp')</a>
                </div>
            </div>
        @endif
    </div>

    <div class="d-flex flex-wrap p-20" id="task-file-list">
        @if ($viewLeadFollowUpPermission == 'all' || $viewLeadFollowUpPermission == 'added')
            <x-table headType="thead-light">
                <x-slot name="thead">
                    <th>@lang('app.createdOn')</th>
                    <th>@lang('modules.lead.nextFollowUp')</th>
                    <th>@lang('app.remark')</th>
                    <th>@lang('app.status')</th>
                    <th class="text-right">@lang('app.action')</th>
                </x-slot>

                @forelse ($dealFollowUps as $folllowUp)
                    <tr id="row-{{ $folllowUp->id }}">
                        <td>{{ $folllowUp->created_at->timezone(company()->timezone)->format(company()->date_format . ' ' . company()->time_format) }}</td>
                        <td>{{ $folllowUp->next_follow_up_date->format(company()->date_format . ' ' . company()->time_format) }}</td>
                        <td>{{ $folllowUp->remark }}</td>
                        <td>
                            @if ($folllowUp->status == 'pending')
                                <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                            @elseif ($folllowUp->status == 'canceled')
                                <i class="fa fa-circle mr-1 text-red f-10"></i>
                            @elseif ($folllowUp->status == 'completed')
                                <i class="fa fa-circle mr-1 text-dark-green f-10"></i>
                            @endif
                            {{ ucfirst($folllowUp->status) }}
                        </td>
                        <td class="text-right">
                            <div class="dropdown ml-auto file-action">
                                <button class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                                    type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                        aria-labelledby="dropdownMenuLink" tabindex="0">
                                    @if ($editLeadFollowUpPermission == 'all' || ($editLeadFollowUpPermission == 'added' && $folllowUp->added_by == user()->id))
                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 edit-table-row-lead"
                                            href="javascript:;" data-followup-id="{{ $folllowUp->id }}">
                                            @lang('app.edit')
                                        </a>
                                    @endif
                                    @if ($deleteLeadFollowUpPermission == 'all' || ($deleteLeadFollowUpPermission == 'added' && user()->id == $folllowUp->added_by))
                                        <a class="cursor-pointer d-block text-dark-grey f-13 pb-3 px-3 delete-table-row-lead"
                                            data-followup-id="{{ $folllowUp->id }}" href="javascript:;">
                                            @lang('app.delete')
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-cards.no-record :message="__('messages.noRecordFound')" icon="clock" />
                        </td>
                    </tr>
                @endforelse
            </x-table>
        @endif
    </div>
</div>
<!-- TAB CONTENT END -->

