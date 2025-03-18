@php
    $addDealNotePermission = user()->permission('add_deal_note');
    $editLeadNotePermission = user()->permission('edit_deal_note');
    $viewLeadNotePermission = user()->permission('view_deal_note');
    $deleteLeadNotePermission = user()->permission('delete_deal_note');
@endphp

<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">
    @if ($addDealNotePermission == 'all' || $addDealNotePermission == 'added' || $addDealNotePermission == 'both')
        <div class="row p-20">
            <div class="col-md-12">
                <a class="f-15 f-w-500 openRightModal" href="{{ route('deal-notes.create').'?lead='.$deal->id }}" id="add-notes"><i
                        class="icons icon-plus font-weight-bold mr-1"></i>@lang('modules.client.createNote')
                    </a>
            </div>
        </div>
    @endif


    <div class="d-flex flex-wrap p-20" id="task-file-list">
        @if ($viewLeadNotePermission == 'all' || $viewLeadNotePermission == 'added' || $viewLeadNotePermission == 'both')
            <x-table headType="thead-light">
                <x-slot name="thead">
                    <th>@lang('modules.client.noteDetail')</th>
                    <th>@lang('app.createdOn')</th>
                    <th class="text-right">@lang('app.action')</th>
                </x-slot>

                @forelse ($notes as $note)
                    <tr>
                        <td>
                            <a href="{{ route('deal-notes.show', $note->id) }}" class="openRightModal" style="color:black;">
                                <div class="mb-0 text-dark-grey f-14 w-70 text-wrap ql-editor p-0">{!! nl2br($note->details) !!}</div>
                            </a>
                        </td>
                        <td>{{ $note->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}</td>

                        <td class="text-right">
                            <div class="dropdown ml-auto note-action">
                                <button
                                    class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                                    type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>

                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                     aria-labelledby="dropdownMenuLink" tabindex="0">
                                    @if ($viewLeadNotePermission == 'all' || ($viewLeadNotePermission == 'added' && user()->id == $note->added_by) || ($viewLeadNotePermission == 'both' && user()->id == $note->added_by))
                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-1 px-3 view-note openRightModal"
                                           href="{{ route('deal-notes.show', $note->id) }}">@lang('app.view')</a>
                                    @endif
                                    @if ($editLeadNotePermission == 'all' || ($editLeadNotePermission == 'added' && $note->added_by == user()->id))
                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-1 px-3 edit-note openRightModal"
                                           href="{{ route('deal-notes.edit', $note->id) }}" data-row-id="{{ $note->id }}">@lang('app.edit')</a>
                                    @endif

                                    @if ($deleteLeadNotePermission == 'all' || ($deleteLeadNotePermission == 'added' && $note->added_by == user()->id))
                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-1 px-3 delete-note-lead"
                                           data-id="{{ $note->id }}"
                                           href="javascript:;">@lang('app.delete')</a>
                                    @endif
                                </div>
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <x-cards.no-record :message="__('messages.noRecordFound')" icon="clipboard" />
                        </td>
                    </tr>
                @endforelse
            </x-table>
        @endif
    </div>


</div>
<!-- TAB CONTENT END -->
