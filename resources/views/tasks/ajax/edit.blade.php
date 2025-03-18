@php
$addTaskCategoryPermission = user()->permission('add_task_category');
$addEmployeePermission = user()->permission('add_employees');
$addTaskFilePermission = user()->permission('add_task_files');
$editTaskPermission = user()->permission('edit_tasks');
$viewProjectPermission = user()->permission('view_projects');
$viewTaskCategoryPermission = user()->permission('view_task_category');
$editMilestonePermission = user()->permission('edit_project_milestones');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-task-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.tasks.taskInfo')</h4>
                <div class="row p-20">

                    <div class="col-lg-6 col-md-6">
                        <x-forms.text :fieldLabel="__('app.title')" fieldName="heading" fieldRequired="true"
                            fieldId="heading" :fieldPlaceholder="__('placeholders.task')"
                            :fieldValue="$task->heading" />
                    </div>

                    <div class="col-md-6 col-lg-6">
                        <x-forms.label class="my-3" fieldId="category_id"
                            :fieldLabel="__('modules.tasks.taskCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="task_category_id"
                                data-live-search="true" data-size="8">
                                <option value="">--</option>
                                @if ($viewTaskCategoryPermission == 'all' || $viewTaskCategoryPermission == 'added')
                                    @foreach ($categories as $category)
                                        <option @selected($task->task_category_id == $category->id) value="{{ $category->id }}">
                                            {{ $category->category_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>

                            @if ($addTaskCategoryPermission == 'all' || $addTaskCategoryPermission == 'added')
                                <x-slot name="append">
                                    <button id="create_task_category" type="button"
                                        class="btn btn-outline-secondary border-grey"
                                        data-toggle="tooltip" data-original-title="{{ __('modules.taskCategory.addTaskCategory') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-12 col-lg-6">
                        <x-forms.label class="my-3" fieldId="project-id" :fieldLabel="__('app.project')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="project_id" id="project-id"
                                    data-toggle="tooltip"
                                    title="@lang('modules.tasks.notFinishedProjects')"
                                data-live-search="true" data-size="8">
                                <option value="">--</option>
                                @if($viewProjectPermission != 'none' && (in_array('employee', user_roles()) || in_array('client', user_roles())))
                                @foreach ($projects as $project)
                                    <option @selected($project->id == $task->project_id) value="{{ $project->id }}"
                                            data-content="{!! '<strong>'.$project->project_short_code."</strong> ".$project->project_name !!}"
                                    >
                                    </option>
                                @endforeach
                                @endif
                            </select>
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-4 col-lg-6 pt-5" id='clientDetails' >
                    </div>


                    <div class="col-md-5 col-lg-4">
                        <x-forms.datepicker fieldId="task_start_date" fieldRequired="true"
                            :fieldLabel="__('modules.projects.startDate')" fieldName="start_date"
                            :fieldValue="(($task->start_date) ? $task->start_date->format(company()->date_format) : '')"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-5 col-lg-4 dueDateBox" @if(is_null($task->due_date)) style="display: none" @endif>
                        <x-forms.datepicker fieldId="due_date" fieldRequired="true" :fieldLabel="__('app.dueDate')"
                                            fieldName="due_date" :fieldPlaceholder="__('placeholders.date')"
                                            :fieldValue="(($task->due_date) ? $task->due_date->format(company()->date_format) : '')"  />
                    </div>

                    <div class="col-md-2 col-lg-2 pt-5">
                        <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :checked="is_null($task->due_date)" :fieldLabel="__('app.withoutDueDate')"
                                          fieldName="without_duedate" fieldId="without_duedate" fieldValue="yes" />
                    </div>

                    @if ($changeStatusPermission == 'all'
                    || ($changeStatusPermission == 'added' && $task->added_by == user()->id)
                    || ($changeStatusPermission == 'owned' && in_array(user()->id, $taskUsers))
                    || ($changeStatusPermission == 'both' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id || $task->project->project_admin == user()->id))
                    )
                        <div class="col-lg-3 col-md-6">
                            @if ($task->board_column_id === $waitingApprovalTaskBoardColumn->id)
                                @foreach ($taskboardColumns as $item)
                                    @if($item->id == $waitingApprovalTaskBoardColumn->id)
                                        <label class="f-14 text-dark-grey mb-12 mt-3">@lang('app.status')</label>
                                        <x-forms.input-group>
                                            <input type="text" name="board_column_id" id="board_column_id"
                                                class="form-control height-35 f-15 readonly-background" disabled
                                                value="{{ $item->column_name }}">
                                        </x-forms.input-group>
                                    @endif
                                @endforeach
                            @else
                            <x-forms.select fieldId="board_column_id" :fieldLabel="__('app.status')"
                                fieldName="board_column_id" search="true" :disabled="$task->board_column_id === $waitingApprovalTaskBoardColumn->id ? 'disabled' : ''">
                                @foreach ($taskboardColumns as $item)
                                    @php
                                        if ($item->slug == 'completed' || $item->slug == 'incomplete') {
                                            if ($item->slug == 'completed') {
                                                $icon = "<i class='fa fa-circle mr-2 text-dark-green'></i>".__('app.' . $item->slug);
                                            }
                                            elseif($item->slug == 'incomplete'){
                                                $icon = "<i class='fa fa-circle mr-2 text-red'></i>".__('app.' . $item->slug);
                                            }
                                        }
                                        else {
                                            if ($item->slug == 'to_do') {
                                                $icon = "<i class='fa fa-circle mr-2 text-yellow'></i>".$item->column_name;
                                            }
                                            elseif($item->slug == 'doing'){
                                                $icon = "<i class='fa fa-circle mr-2 text-blue'></i>".$item->column_name;
                                            }
                                            else {
                                                $icon = "<i class='fa fa-circle mr-2' style='color: " . ($item->label_color ?? '#000000') . "'></i>". $item->column_name;
                                            }
                                        }
                                    @endphp
                                    @if ($item->id !== $waitingApprovalTaskBoardColumn->id)
                                        <option @selected($task->board_column_id == $item->id) value="{{ $item->id }}" data-item="{{ $item->column_name }}" data-content = "{{$icon}}">
                                        </option>
                                    @endif
                                @endforeach
                            </x-forms.select>
                            @endif
                        </div>
                    @endif
                    <div class="col-md-12 col-lg-12">
                    </div>

                    <div class="col-md-12 col-lg-8">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="selectAssignee" :fieldLabel="__('modules.tasks.assignTo')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control multiple-users" multiple name="user_id[]"
                                    id="selectAssignee" data-live-search="true" data-size="8">
                                    @foreach ($employees as $employee)
                                        @php
                                            $selected = '';
                                            $isMember = false;
                                        @endphp

                                        @foreach ($task->users as $item)
                                            @if ($item->id == $employee->id)
                                                @php
                                                    $selected = 'selected';
                                                    $isMember = true;
                                                @endphp
                                            @endif
                                        @endforeach

                                        @if ($employee->status === 'active' || $isMember)
                                            <x-user-option :user="$employee" :pill=true :selected="$selected"/>
                                        @endif
                                    @endforeach
                                </select>

                                @if ($addEmployeePermission == 'all' || $addEmployeePermission == 'added')
                                    <x-slot name="append">
                                        <button id="add-employee" type="button"
                                            class="btn btn-outline-secondary border-grey"
                                            data-toggle="tooltip" data-original-title="{{ __('modules.employees.addNewEmployee') }}">@lang('app.add')</button>
                                    </x-slot>
                                @endif
                            </x-forms.input-group>
                        </div>
                    </div>

                    <div class="col-md-12 show-leave">
                        @if(isset($leaveData))
                            <label id="leave-date"> {{ __("modules.tasks.leaveMessage") }} <i class="fa fa-question-circle" title="{{$leaveData}}" id="leave-tooltip"></i></label>
                        @endif
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="description" :fieldLabel="__('app.description')">
                            </x-forms.label>
                            <div id="description">{!! $task->description !!}</div>
                            <textarea name="description" id="description-text" class="d-none"></textarea>
                        </div>
                    </div>

                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-top-grey">
                    @lang('modules.client.clientOtherDetails')
                </h4>

                <div class="row p-20">
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-md-12 col-lg-4">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="task_labels" :fieldLabel="__('app.label')">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="select-picker form-control" multiple name="task_labels[]"
                                            id="task_labels" data-live-search="true" data-size="8">
                                            @foreach ($taskLabels as $label)
                                                @php
                                                    $selected = '';
                                                @endphp

                                                @foreach ($task->label as $item)
                                                    @if ($item->label_id == $label->id)
                                                        @php
                                                            $selected = 'selected';
                                                        @endphp
                                                    @endif
                                                @endforeach
                                                <option {{ $selected }}
                                                    data-content="<span class='badge badge-secondary' style='background-color: {{ $label->label_color }}'>{{ $label->label_name }}</span>"
                                                    value="{{ $label->id }}">{{ $label->label_name }}</option>
                                            @endforeach
                                        </select>


                                        @if (user()->permission('task_labels') == 'all')
                                            <x-slot name="append">
                                                <button id="createTaskLabel" type="button"
                                                    class="btn btn-outline-secondary border-grey"
                                                    data-toggle="tooltip" data-original-title="{{ __('modules.taskLabel.addLabel') }}">@lang('app.add')</button>
                                            </x-slot>
                                        @endif
                                    </x-forms.input-group>
                                </div>
                            </div>

                            @php
                                $showDropdown = (($editTaskPermission == 'all'
                                                    || ($editTaskPermission == 'owned' && in_array(user()->id, $taskUsers))
                                                    || ($editTaskPermission == 'added' && $task->added_by == user()->id)
                                                    || ($task->project && ($task->project->project_admin == user()->id))
                                                    || ($editTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
                                                    || ($editTaskPermission == 'owned' && (in_array('client', user_roles()) && $task->project && ($task->project->client_id == user()->id)))
                                                    || ($editTaskPermission == 'both' && (in_array('client', user_roles()) && ($task->project && ($task->project->client_id == user()->id)) || $task->added_by == user()->id))
                                                    ) &&(
                                                        $editMilestonePermission == 'all'
                                                        || ($editMilestonePermission == 'added' && $task->added_by == user()->id)
                                                        || ($editMilestonePermission == 'owned' && in_array(user()->id, $taskUsers))
                                                        || ($editMilestonePermission == 'owned' && (in_array('client', user_roles()) && $task->project && ($task->project->client_id == user()->id)))
                                                    ));

                                $selectedMilestone = '--';
                                if ($task->project && count($task->project->incompleteMilestones) > 0) {
                                    foreach ($task->project->incompleteMilestones as $milestone) {
                                        if ($milestone->id == $task->milestone_id) {
                                            $selectedMilestone = $milestone->milestone_title;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            <div class="col-md-12 col-lg-4">
                                @if($showDropdown)
                                    <x-forms.select fieldName="milestone_id" fieldId="milestone-id"
                                        :fieldLabel="__('modules.projects.milestones')">
                                        <option value="">--</option>

                                        @if ($task->project && count($task->project->completedMilestones) > 0)
                                            @foreach ($task->project->completedMilestones as $milestone)
                                                @if($milestone->id == $task->milestone_id)
                                                    <option @selected($milestone->id == $task->milestone_id) value="{{ $milestone->id }}">{{ $milestone->milestone_title }}</option>
                                                @endif
                                            @endforeach
                                        @endif

                                        @if ($task->project && count($task->project->incompleteMilestones) > 0)
                                            @foreach ($task->project->incompleteMilestones as $milestone)
                                                <option @selected($milestone->id == $task->milestone_id) value="{{ $milestone->id }}">
                                                    {{ $milestone->milestone_title }}</option>
                                            @endforeach
                                        @endif
                                    </x-forms.select>
                                @else
                                    <label class="f-14 text-dark-grey mb-12 mt-3">@lang('modules.projects.milestones')</label>
                                    <x-forms.input-group>
                                        <input type="text" name="milestone_id" id="milestone-id"
                                            class="form-control height-35 f-15 readonly-background" disabled
                                            value="{{ $selectedMilestone }}">
                                    </x-forms.input-group>
                                @endif
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="priority" :fieldLabel="__('modules.tasks.priority')"
                                    fieldName="priority">
                                    <option @selected($task->priority == 'high')
                                        data-content="<i class='fa fa-circle mr-2' style='color: #dd0000'></i> @lang('modules.tasks.high')"
                                        value="high">@lang('modules.tasks.high')</option>
                                    <option @selected($task->priority == 'medium')
                                        data-content="<i class='fa fa-circle mr-2' style='color: #ffc202'></i> @lang('modules.tasks.medium')"
                                        value="medium">
                                        @lang('modules.tasks.medium')</option>
                                    <option @selected($task->priority == 'low')
                                        data-content="<i class='fa fa-circle mr-2' style='color: #0a8a1f'></i> @lang('modules.tasks.low')"
                                        value="low">@lang('modules.tasks.low')</option>
                                </x-forms.select>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.makePrivate')" fieldName="is_private"
                                    fieldId="is_private" :popover="__('modules.tasks.privateInfo')"
                                    :checked="$task->is_private" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.billable')" fieldName="billable"
                                    fieldId="billable" :popover="__('modules.tasks.billableInfo')"
                                    :checked="$task->billable" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.setTimeEstimate')"
                                    fieldName="set_time_estimate" fieldId="set_time_estimate"
                                    :checked="($task->estimate_hours > 0 || $task->estimate_minutes > 0)" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 {{ $task->estimate_hours == 0 && $task->estimate_minutes == 0 ? 'd-none' : '' }}"
                        id="set-time-estimate-fields">
                        <div class="form-group mt-5">
                            <input type="number" min="0" class="w-25 border rounded p-2 height-35 f-14"
                                name="estimate_hours" value="{{ $task->estimate_hours }}">
                            @lang('app.hrs')
                            &nbsp;&nbsp;
                            <input type="number" min="0" name="estimate_minutes"
                                value="{{ $task->estimate_minutes }}" class="w-25 height-35 f-14 border rounded p-2">
                            @lang('app.mins')
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <div class="d-flex">
                                <x-forms.checkbox :fieldLabel="__('modules.events.repeat')" fieldName="repeat" :checked="$task->repeat"
                                    fieldId="repeat-task" />
                            </div>
                        </div>

                        <div class="form-group my-3 {{$task->repeat ? '' : 'd-none'}}" id="repeat-fields">
                            <div class="row">
                                <div class="col-md-6 mt-3">
                                    <x-forms.label fieldId="repeatEvery" fieldRequired="true"
                                        :fieldLabel="__('modules.events.repeatEvery')">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <input type="number" min="1" value="{{$task->repeat_count}}" name="repeat_count"
                                            class="form-control f-14">

                                        <x-slot name="append">
                                            <select name="repeat_type" class="select-picker form-control">
                                                <option @selected($task->repeat_type == 'day') value="day">
                                                    @lang('app.day')</option>
                                                <option @selected($task->repeat_type == 'week') value="week">
                                                    @lang('app.week')</option>
                                                <option @selected($task->repeat_type == 'month') value="month">
                                                    @lang('app.month')</option>
                                                <option @selected($task->repeat_type == 'year') value="year">
                                                    @lang('app.year')</option>
                                            </select>
                                        </x-slot>
                                    </x-forms.input-group>
                                </div>
                                <div class="col-md-6">
                                    <x-forms.number :fieldLabel="__('modules.events.cycles')" fieldName="repeat_cycles"
                                        fieldRequired="true" :fieldValue="$task->repeat_cycles" minValue="1" fieldId="repeat_cycles"
                                        :fieldPlaceholder="__('modules.tasks.cyclesToolTip')"
                                        :popover="__('modules.tasks.cyclesToolTip')" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <div class="d-flex">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.dependent')" fieldName="dependent"
                                    fieldId="dependent-task" :checked="$task->dependent_task_id" :popover="__('modules.tasks.dependentTaskInfo')"/>
                            </div>
                        </div>

                        <div class="{{ !$task->dependent_task_id ? 'd-none' : '' }}" id="dependent-fields">
                            <x-forms.select fieldId="dependent_task_id" :fieldLabel="__('modules.tasks.dependentTask')"
                                fieldName="dependent_task_id" search="true">
                                <option value="">--</option>
                                @foreach ($allTasks as $item)
                                    <option @selected($item->id == $task->dependent_task_id) value="{{ $item->id }}">
                                        {{ $item->heading }}
                                        (@lang('app.dueDate'):
                                        @if(!is_null($item->due_date))
                                            {{ $item->due_date->translatedFormat(company()->date_format) }})
                                        @endif
                                    </option>
                                @endforeach
                            </x-forms.select>
                        </div>
                    </div>
                    <input type = "hidden" name = "mention_user_ids" id = "mentionUserId" class ="mention_user_ids">

                    @if ($addTaskFilePermission == 'all' || $addTaskFilePermission == 'added')
                        <div class="col-lg-12">
                            <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2"
                                :fieldLabel="__('app.menu.addFile')" fieldName="file"
                                fieldId="task-files-upload-dropzone" />
                            <input type="hidden" name="image_url" id="image_url">
                        </div>
                        <input type="hidden" name="addedFiles" id="addedFiles">
                    @endif

                </div>
                <x-forms.custom-field :fields="$fields" :model="$task"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="save-task-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('tasks.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script>
    var add_task_files = "{{ $addTaskFilePermission }}";

    $(document).ready(function() {

        $('#board_column_id').selectpicker();

        document.querySelectorAll('input[name="estimate_hours"], input[name="estimate_minutes"]').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/^0+(?!$)/, ''); // Remove leading zeros
            });
        });

        // Remove title attribute from selectpicker button
        $('#board_column_id').on('loaded.bs.select', function () {
            // Find the button element and remove the title attribute
            $(this).siblings('button').removeAttr('title');
        });

        var rolesJson = `{!! addslashes(json_encode(user()->roles)) !!}`;
        var roles = JSON.parse(rolesJson); // Parse JSON string to JavaScript object

        function isAdmin() {
            for (var i = 0; i < roles.length; i++) {
                if (roles[i].name === 'admin') {
                    return true;
                }
            }
        }

        $('#board_column_id').on('change', function() {

            var select = $(this);
            var selectedOption = select.find('option:selected');
            var newValue = selectedOption.attr('data-item');
            handleChangeStatus(select, newValue); // Pass the select element to handleChangeStatus

        });

        function handleChangeStatus(select, newValue) {

            var needApproval = "{{ optional($task->project)->need_approval_by_admin ?? 0 }}";
            var projectAdmin = "{{ optional($task->project)->project_admin ?? null }}";
            var loggedUser = "{{ user()->id }}";

            if(!isAdmin() && needApproval == 1 && newValue == 'Completed' && projectAdmin != loggedUser){
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.approvalmsgsent')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('app.yes')",
                    cancelButtonText: "@lang('app.no')",
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
                        updateStatus(select);
                    }else{
                        select.selectpicker('val', '{{$task->board_column_id}}');
                    }
                });
            }
        }

        function updateStatus(select) {
            changeToInput(select);
        }

        function changeToInput(select) {
            var divToHide = $('[data-id="board_column_id"]');
            if (divToHide.length > 0) {
                divToHide.hide();
            }

            var inputElement = $('<input>').attr({
                type: 'text',
                name: 'select_value',
                id: 'board_column_id',
                class: 'form-control height-35 f-15',
                value: 'Waiting Approval',
                autocomplete: 'off'
            });
            select.replaceWith(inputElement);
        }

        let projectId = document.getElementById('project-id').value;

        (projectId != 'all' && projectId != '') ? projectClient(projectId) : '';

        $(".select-picker").selectpicker();

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        $(document).on('change', '#project-id', function () {

            ($(this).val() != '') ? $('#clientDetails').show() : $('#clientDetails').hide();

            let id = $(this).val();
            if (id === '') {
                return '';
            }
            projectClient(id);

        });

        function projectClient(id) {

            let url = "{{ route('tasks.clientDetail') }}";

            $.easyAjax({
                url: url,
                type: "GET",
                data: {
                    id: id,
                },
                success: function (response) {
                    $('#clientDetails').html(response.data);
                }
            });
        }

        if (add_task_files == "all" || add_task_files == "added") {

            Dropzone.autoDiscover = false;
            //Dropzone class
            taskDropzone = new Dropzone("div#task-files-upload-dropzone", {
                dictDefaultMessage: "{{ __('app.dragDrop') }}",
                url: "{{ route('task-files.store') }}",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                paramName: "file",
                maxFilesize: DROPZONE_MAX_FILESIZE,
                maxFiles: DROPZONE_MAX_FILES,
                autoProcessQueue: false,
                uploadMultiple: true,
                addRemoveLinks: true,
                parallelUploads: DROPZONE_MAX_FILES,
                acceptedFiles: DROPZONE_FILE_ALLOW,
                init: function() {
                    taskDropzone = this;
                }
            });
            taskDropzone.on('sending', function(file, xhr, formData) {
                var ids = "{{ $task->id }}";
                formData.append('task_id', ids);
                $.easyBlockUI();
            });
            taskDropzone.on('uploadprogress', function() {
                $.easyBlockUI();
            });
            taskDropzone.on('queuecomplete', function() {
                var msgs = "@lang('messages.recordSaved')";
                window.location.href = "{{ route('tasks.index') }}"
            });
            taskDropzone.on('removedfile', function () {
                var grp = $('div#file-upload-dropzone').closest(".form-group");
                var label = $('div#file-upload-box').siblings("label");
                $(grp).removeClass("has-error");
                $(label).removeClass("is-invalid");
            });
            taskDropzone.on('error', function (file, message) {
                taskDropzone.removeFile(file);
                var grp = $('div#file-upload-dropzone').closest(".form-group");
                var label = $('div#file-upload-box').siblings("label");
                $(grp).find(".help-block").remove();
                var helpBlockContainer = $(grp);

                if (helpBlockContainer.length == 0) {
                    helpBlockContainer = $(grp);
                }

                helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
                $(grp).addClass("has-error");
                $(label).addClass("is-invalid");

            });
        }


        $("#selectAssignee").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });
        const atValues = @json($userData);

        quillMention(atValues, '#description');

        const dp1 = datepicker('#task_start_date', {
            position: 'bl',
            dateSelected: new Date("{{ $task->start_date ? str_replace('-', '/', $task->start_date) : str_replace('-', '/', now()) }}"),
            onSelect: (instance, date) => {
                if (typeof dp2.dateSelected !== 'undefined' && dp2.dateSelected.getTime() < date
                    .getTime()) {
                    dp2.setDate(date, true)
                }
                if (typeof dp2.dateSelected === 'undefined') {
                    dp2.setDate(date, true)
                }
                dp2.setMin(date);

                var dueDate = $('#due_date').val();
                var startDate = $('#task_start_date').val();
                var userId = $('#selectAssignee').val();

                $.easyAjax({
                    url:"{{ route('tasks.checkLeaves')}}",
                    type:'GET',
                    data:{due_date:dueDate, start_date:startDate, user_id:userId},
                    success:function(response) {
                    $('.show-leave').removeClass('d-none');
                    var rData = [];
                    var leaveData = [];
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var data = '';
                            data = index + " {{ __('modules.tasks.leaveOn') }} "  + value + "\n";
                            leaveData.push(data);
                            var label = '<label id="leave-date"> {{ __("modules.tasks.leaveMessage") }} <i class="fa fa-question-circle" title="'+leaveData+'" id="leave-tooltip"></i></label>'
                            $(".show-leave").html(label);
                        });
                }
                });
            },
            ...datepickerConfig
        });

        const dp2 = datepicker('#due_date', {
            position: 'bl',
            dateSelected: new Date("{{ $task->due_date ? str_replace('-', '/', $task->due_date) : str_replace('-', '/', now()) }}"),
            onSelect: (instance, date) => {
                dp1.setMax(date);

                var dueDate = $('#due_date').val();
                var startDate = $('#task_start_date').val();
                var userId = $('#selectAssignee').val();

                $.easyAjax({
                    url:"{{ route('tasks.checkLeaves')}}",
                    type:'GET',
                    data:{start_date:startDate, due_date:dueDate, user_id:userId},
                    success:function(response) {
                    $('.show-leave').removeClass('d-none');
                    var rData = [];
                    var leaveData = [];
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var data = '';
                            data = index + " {{ __('modules.tasks.leaveOn') }} "  + value + "\n";
                            leaveData.push(data);
                            var label = '<label id="leave-date"> {{ __("modules.tasks.leaveMessage") }} <i class="fa fa-question-circle" title="'+leaveData+'" id="leave-tooltip"></i></label>'
                            $(".show-leave").html(label);
                        });
                }
                });
            },
            ...datepickerConfig
        });

        $('#selectAssignee').change(function(){
            var dueDate = $('#due_date').val();
            var startDate = $('#task_start_date').val();
            var userId = $('#selectAssignee').val();

            $.easyAjax({
                url:"{{ route('tasks.checkLeaves')}}",
                type:'GET',
                data:{start_date:startDate, due_date:dueDate, user_id:userId},
                success:function(response) {
                    $('.show-leave').removeClass('d-none');
                    var rData = [];
                    var leaveData = [];
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var data = '';
                            data = index + " {{ __('modules.tasks.leaveOn') }} "  + value + "\n";
                            leaveData.push(data);
                            var label = '<label id="leave-date"> {{ __("modules.tasks.leaveMessage") }} <i class="fa fa-question-circle" title="'+leaveData+'" id="leave-tooltip"></i></label>'
                            $(".show-leave").html(label);
                        });
                }
            });
        })

        $('#save-task-form').click(function() {
            var note = document.getElementById('description').children[0].innerHTML;

            document.getElementById('description-text').value = note;
            var usesr = $('#description span[data-id]').map(function(){
                            return $(this).attr('data-id')
                        }).get();

            var mention_user_id  =  $.makeArray(usesr);
            $('#mentionUserId').val(mention_user_id.join(','));

            var taskData = $('#save-task-data-form').serialize();

            var data = taskData+='&mention_user_id=' + mention_user_id;

            const url = "{{ route('tasks.update', $task->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-task-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-task-form",
                data: data,
                success: function(response) {
                    if ((add_task_files == "all" || add_task_files == "added") &&
                        taskDropzone.getQueuedFiles().length > 0) {
                        taskDropzone.processQueue();
                    } else if ($(RIGHT_MODAL).hasClass('in')) {
                        document.getElementById('close-task-detail').click();
                        if($('#unassigned-task').length) {
                            @if(isset($task->project->id))
                                window.location.href = "{{ route('projects.show', $task->project->id)}}" + "?tab=tasks";
                            @endif
                        }
                        else if ($('#allTasks-table').length) {
                            window.LaravelDataTables["allTasks-table"].draw(true);
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    } else {
                        window.location.href = response.redirectUrl;
                    }

                }
            });
        });

        $('#create_task_category').click(function() {
            const url = "{{ route('taskCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#department-setting').click(function() {
            const url = "{{ route('departments.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#client_view_task').change(function() {
            $('#clientNotification').toggleClass('d-none');
        });

        $('#without_duedate').click(function() {
            $('.dueDateBox').toggle();
        });

        $('#set_time_estimate').change(function() {
            $('#set-time-estimate-fields').toggleClass('d-none');
        });

        $('#repeat-task').change(function() {
            $('#repeat-fields').toggleClass('d-none');
        });

        $('#dependent-task').change(function() {
            $('#dependent-fields').toggleClass('d-none');
        });


        $('#project-id').change(function() {
            var id = $(this).val();
            if (id == '') {
                id = 0;
            }
            var url = "{{ route('milestones.by_project', ':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                url: url,
                container: '#save-task-data-form',
                type: "GET",
                blockUI: true,
                success: function(response) {
                    if (response.status == 'success') {
                        $('#milestone-id').html(response.data);
                        $('#milestone-id').selectpicker('refresh');
                    }
                }
            });
        });

        $('#project-id').change(function() {
            let id = $(this).val();
            if (id === '') {
                id = 0;
            }
            let url = "{{ route('projects.members', ':id') }}";
            url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                type: "GET",
                container: '#save-task-data-form',
                blockUI: true,
                redirect: true,
                success: function (data) {
                    $('#selectAssignee').html(data.data);
                    $('.projectId').text(data.unique_id);
                    $('#selectAssignee').selectpicker('refresh');
                }
            })
        });

        $('#project-id').change(function() {
            let id = $(this).val();
            if (id === '') {
                id = 0;
            }
            let url = "{{ route('tasks.project_tasks', ':id') }}";
            url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                type: "GET",
                container: '#save-task-data-form',
                blockUI: true,
                redirect: true,
                success: function (data) {
                    $('#dependent_task_id').html(data.data);
                    $('.projectId').text(data.unique_id);
                    $('#dependent_task_id').selectpicker('refresh');
                }
            })
        });

        $('#save-task-data-form').on('change', '#project_id', function () {
            let id = $(this).val();
            if (id === '') {
                id = 0;
            }
            let url = "{{ route('projects.labels', ':id') }}";
            url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                type: "GET",
                container: '#save-task-data-form',
                blockUI: true,
                redirect: true,
                success: function (data) {
                    var atValues = data.userData;
                    destory_editor('#description')
                    quillMention(atValues, '#description');
                    $('#task_labels').html(data.data);
                    $('#task_labels').selectpicker('refresh');
                }
            })
        });


        $('#createTaskLabel').click(function() {
            const url = "{{ route('task-label.create') }}?task_id={{$task ? $task->id : ''}}&project_id=" + projectId;
            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        $('#add-project').click(function() {
            $(MODAL_XL).modal('show');

            const url = "{{ route('projects.create') }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: MODAL_XL,
                success: function(response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        $('#add-employee').click(function() {
            $(MODAL_XL).modal('show');

            const url = "{{ route('employees.create') }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: MODAL_XL,
                success: function(response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        <x-forms.custom-field-filejs/>

        init(RIGHT_MODAL);
    });

</script>
