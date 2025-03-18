@php
    $addTaskNotePermission = user()->permission('add_task_notes');
    $editTaskNotePermission = user()->permission('edit_task_notes');
    $deleteTaskNotePermission = user()->permission('delete_task_notes');
@endphp

<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">
    @if ($addTaskNotePermission == 'all'
    || ($addTaskNotePermission == 'added' && $task->added_by == user()->id)
    || ($addTaskNotePermission == 'owned' && in_array(user()->id, $taskUsers))
    || ($addTaskNotePermission == 'both' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
    )

        <div class="row p-20">
            <div class="col-md-12">
                <a class="f-15 f-w-500" href="javascript:;" id="add-notes"><i
                        class="icons icon-plus font-weight-bold mr-1"></i>@lang('modules.client.createNote')
                    </a>
            </div>
        </div>

        @php
            $userRoles = user_roles();
            $isAdmin = in_array('admin', $userRoles);
            $isEmployee = in_array('employee', $userRoles);
        @endphp

        @if ($task->approval_send == 1 && $isEmployee && !$isAdmin && $task->project->need_approval_by_admin == 1 && $status->slug == 'waiting_approval')
            <!-- Popup for Send Approval -->
            @include('tasks.ajax.sent-approval-modal')
        @else
            <x-form id="save-note-data-form" class="d-none">
                <div class="col-md-12 p-20 ">
                    <div class="media">
                        <img src="{{ user()->image_url }}" class="align-self-start mr-3 taskEmployeeImg rounded"
                            alt="{{ user()->name }}">
                        <div class="media-body bg-white">
                            <div class="form-group">
                                <div id="task-note"></div>
                                <textarea name="note" class="form-control invisible d-none" id="task-note-text"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="w-100 justify-content-end d-flex mt-2">
                        <x-forms.button-cancel id="cancel-note" class="border-0 mr-3">@lang('app.cancel')
                        </x-forms.button-cancel>
                        <x-forms.button-primary id="submit-note" icon="location-arrow">@lang('app.submit')
                            </x-forms.button-primary>
                    </div>

                </div>
            </x-form>
        @endif
    @endif


    <div class="d-flex flex-wrap justify-content-between p-20" id="note-list">
        @forelse($task->notes as $note)
            <div class="card w-100 rounded-0 border-0 note">
                <div class="card-horizontal">
                    <div class="card-img my-1 ml-0">
                        <img src="{{ $note->user->image_url }}" alt="{{ $note->user->name }}">
                    </div>
                    <div class="card-body border-0 pl-0 py-1">
                        <div class="d-flex flex-grow-1">
                            <h4 class="card-title f-15 f-w-500 text-dark mr-3">{{ $note->user->name }}</h4>
                            <p class="card-date f-11 text-lightest mb-0">
                                {{ $note->created_at->diffForHumans() }}
                            </p>
                            @if ($editTaskNotePermission == 'all' || ($editTaskNotePermission == 'added' && $note->added_by == user()->id) ||
                            $deleteTaskNotePermission == 'all' || ($deleteTaskNotePermission == 'added' && $note->added_by == user()->id))
                            <div class="dropdown ml-auto note-action">
                                <button
                                    class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                                    type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>

                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                     aria-labelledby="dropdownMenuLink" tabindex="0">
                                    @if ($editTaskNotePermission == 'all' || ($editTaskNotePermission == 'added' && $note->added_by == user()->id))
                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 edit-note"
                                           href="javascript:;" data-row-id="{{ $note->id }}">@lang('app.edit')</a>
                                    @endif

                                    @if ($deleteTaskNotePermission == 'all' || ($deleteTaskNotePermission == 'added' && $note->added_by == user()->id))
                                        <a class="cursor-pointer d-block text-dark-grey f-13 pb-3 px-3 delete-note"
                                           data-row-id="{{ $note->id }}"
                                           href="javascript:;">@lang('app.delete')</a>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="card-text f-14 text-dark-grey text-justify ql-editor">{!! $note->note !!}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <x-cards.no-record :message="__('messages.noNoteFound')" icon="clipboard"/>
        @endforelse
    </div>


</div>
<!-- TAB CONTENT END -->

<script>
    var add_task_notes = "{{ $addTaskNotePermission }}";
    var send_approval = "{{ $task->approval_send }}";
    var admin = "{{ in_array('admin', user_roles()) }}";
    var employee = "{{ in_array('employee', user_roles()) }}";
    var needApproval = "{{ $task?->project?->need_approval_by_admin }}";
    var status = "{{ $status->slug }}";

    $('#add-notes').click(function () {
        if (send_approval == 1 && employee == 1 && admin != 1 && needApproval == 1 && status == 'waiting_approval') {
            $('#send-approval-modal').modal('show');
            $('.modal-backdrop').css('display', 'none');
        }else{
            $(this).closest('.row').addClass('d-none');
            $('#save-note-data-form').removeClass('d-none');
        }
    });

    $('#cancel-note').click(function () {
        $('#save-note-data-form').addClass('d-none');
        $('#add-notes').closest('.row').removeClass('d-none');
    });

    var atValues = @json($taskuserData);

    $(document).ready(function () {

        if (add_task_notes == "all" || add_task_notes == "added") {
            quillMention(atValues, '#task-note');
        }


        $('#submit-note').click(function () {
            var note = document.getElementById('task-note').children[0].innerHTML;
            document.getElementById('task-note-text').value = note;
            var mention_user_id = $('#task-note span[data-id]').map(function(){
                return $(this).attr('data-id')

            }).get();
            var token = '{{ csrf_token() }}';

            const url = "{{ route('task-note.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-note-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#submit-note",
                data: {
                    '_token': token,
                    note: note,
                    mention_user_id : mention_user_id,
                    taskId: '{{ $task->id }}'
                },
                success: function (response) {
                    if (response.status == "success") {
                        $('#note-list').html(response.view);
                        document.getElementById('task-note').children[0].innerHTML = "";
                        $('#task-note-text').val('');
                    }

                }
            });
        });

    });
</script>
