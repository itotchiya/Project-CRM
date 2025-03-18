@php
    $addProjectCategoryPermission = user()->permission('manage_project_category');
    $addEmployeePermission = user()->permission('add_employees');
    $addProjectFilePermission = user()->permission('add_project_files');
    $addPublicProjectPermission = user()->permission('create_public_project');
    $addProjectMemberPermission = user()->permission('add_project_members');
    $addProjectNotePermission = user()->permission('add_project_note');
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.copyProject')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">

    <x-form id="createDuplicateProject">
        <input type="hidden" name="duplicateProjectID" value="{{$project->id}}">
        <input type="hidden" name="type" value="duplicateProject">
        <div class="row">
            <div class="col-sm-12 mt-2">
                <x-forms.checkbox fieldId="task"
                    :fieldLabel="__('app.task')"
                    fieldName="task"/>
                    <div class="ml-3 mt-2 d-none" id="task-options">
                        <x-forms.checkbox fieldId="sub_task"
                        :fieldLabel="__('app.copy').' '.__('modules.tasks.subTask')"
                        fieldName="sub_task"/>
                        <x-forms.checkbox fieldId="same_assignee"
                        :fieldLabel="__('modules.projects.sameAssignee')"
                        fieldName="same_assignee"/>
                    </div>
            </div>
            <div class="col-sm-12 mt-2">
                <x-forms.checkbox fieldId="milestone"
                    :fieldLabel="__('app.milestone')"
                    fieldName="milestone"/>
            </div>
            <div class="col-sm-12 mt-2">
                <x-forms.checkbox fieldId="file"
                    :fieldLabel="__('app.file')"
                    fieldName="file"/>
            </div>
            <div class="col-sm-12 mt-2">
                <x-forms.checkbox fieldId="time_sheet"
                    :fieldLabel="__('app.menu.timeLogs')"
                    fieldName="time_sheet"/>
            </div>
            <div class="col-sm-12 mt-2">
                <x-forms.checkbox fieldId="note"
                    :fieldLabel="__('modules.projects.note')"
                    fieldName="note"/>
            </div>
        </div>
        <div class="row border-top-grey mt-3 d-none" id="task-status">
            <div class="col-md-12">
                <div class="form-group my-3">
                    <label class="f-14 text-dark-grey mb-12 w-100" for="usr">@lang('app.taskStatus')</label>
                    @foreach ($taskboardColumns as $item)
                        <x-forms.checkbox  :fieldId="$item->slug" :fieldLabel="$item->column_name" fieldName="task_status[]"
                            :fieldValue="$item->id" :checked="($item->slug == 'incomplete') ? true : false">
                        </x-forms.checkbox>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row border-top-grey mt-3">
            <div class="col-md-12">
                <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.taskShortCode')"
                            fieldName="project_code" fieldRequired="false" fieldId="project_code"
                            :fieldPlaceholder="__('placeholders.writeshortcode')" :fieldValue="$project ? $project->project_short_code : ''"/>
            </div>
            <div class="col-md-12">
                <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.projects.projectName')"
                            fieldName="project_name" fieldRequired="true" fieldId="project_name"
                            :fieldPlaceholder="__('placeholders.project')"
                            :fieldValue="$project ? $project->project_name : '' "/>
            </div>
            <div class="col-md-6 col-lg-4">
                <x-forms.datepicker fieldId="start_date" fieldRequired="true"
                                    :fieldLabel="__('modules.projects.startDate')" fieldName="start_date"
                                    :fieldPlaceholder="__('placeholders.date')" :fieldValue="$project ? $project->start_date->format(company()->date_format) : ''"/>
            </div>

            <div class="col-md-6 col-lg-4" id="deadlineBox">
                <x-forms.datepicker fieldId="deadline" fieldRequired="true"
                                    :fieldLabel="__('modules.projects.deadline')" fieldName="deadline"
                                    :fieldPlaceholder="__('placeholders.date')"
                                    :fieldValue="($project ? (($project->deadline) ?$project->deadline->format(company()->date_format) : '') : '')" />
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="form-group">
                    <div class="d-flex mt-5">
                        <x-forms.checkbox fieldId="without_deadline"
                        :checked="($project && $project->deadline == null) ? true : false" :fieldLabel="__('modules.projects.withoutDeadline')"  fieldName="without_deadline"/>
                    </div>
                </div>
            </div>
            @if ($addPublicProjectPermission == 'all')
                <div class="col-sm-12">
                    <div class="form-group">
                        <div class="d-flex mt-2">
                            <x-forms.checkbox fieldId="is_public"
                                            :fieldLabel="__('modules.projects.createPublicProject')"
                                            fieldName="public"/>
                        </div>
                    </div>
                </div>
            @endif
            @if (!in_array('client', user_roles()))
                <div class="col-md-4">
                    <x-forms.label class="my-3" fieldId="department" :fieldLabel="__('app.department')">
                    </x-forms.label>
                    <x-forms.input-group>
                        <select class="form-control multiple-users" multiple name="team_id[]" id="employee_department"
                            data-live-search="true">
                            @foreach ($teams as $team)
                                <option data-content="<span class='badge badge-pill badge-light border p-2'>{{ $team->team_name }}</span>"
                                @selected(in_array($team->id, $departmentIds)) value="{{ $team->id }}">
                                    {{ $team->team_name }}
                                </option>
                            @endforeach
                        </select>
                    </x-forms.input-group>
                </div>
            @endif
            @if ($addProjectMemberPermission == 'all' || $addProjectMemberPermission == 'added')
                <div class="col-md-12">
                    <div class="form-group my-3">
                        <x-forms.label class="my-3" fieldId="selectEmployee" fieldRequired="true"
                                        :fieldLabel="__('modules.projects.addMemberTitle')">
                        </x-forms.label>

                        <x-forms.input-group>
                            <select class="form-control multiple-users" multiple name="user_id[]"
                                    id="selectEmployee" data-live-search="true" data-size="8">
                                    @foreach ($employees as $item)
                                        <x-user-option
                                            :user="$item"
                                            :pill="true"
                                            :selected="request()->has('default_assign') && request('default_assign') == $item->id ||(isset($memberIds) && in_array($item->id, $memberIds))"
                                        />
                                    @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                </div>
            @elseif(in_array('employee', user_roles()))
                <input type="hidden" name="user_id[]" value="{{ user()->id }}">
            @endif
            <div class="@if(!in_array('clients', user_modules())) d-none @endif col-md-12 @if (!isset($client) && is_null($client)) py-3 @endif">
                @if (isset($client) && !is_null($client))
                    <x-forms.label class="my-3" fieldId="client_id" :fieldLabel="__('app.client')">
                    </x-forms.label>

                    <input type="hidden" name="client_id" id="client_id" value="{{ $client->id }}">
                    <input type="text" value="{{ $client->name_salutation }}"
                           class="form-control height-35 f-15 readonly-background" readonly>
                @else
                    <x-forms.select fieldName="client_id" fieldId="client_id"
                        :fieldLabel="__('app.client')">
                        <option value="">--</option>
                        @foreach ($clients as $clientOpt)
                            <option data-content="<x-client-search-option :user='$clientOpt' />"
                            value="{{ $clientOpt->id }}">{{ $clientOpt->name }} </option>
                        @endforeach
                    </x-forms.select>
                @endif
            </div>
        </div>
        <div class="modal-footer">
            <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
            <x-forms.button-primary id="save-copy-project" icon="check">@lang('app.copyProject')</x-forms.button-primary>
        </div>
    </x-form>
</div>

<script>

    $('.select-picker').selectpicker();

    @if ($project->deadline == null)
        $('#deadlineBox').hide();
    @endif

    var startDate = datepicker('#start_date', {
        position: 'bl',
        onSelect: (instance, date) => {
            endDate.setMin(date);
        },
        ...datepickerConfig
    });

    var endDate = datepicker('#deadline', {
        position: 'bl',
        onSelect: (instance, date) => {
            startDate.setMax(date);
        },
        ...datepickerConfig
    });

    $('#task').change(function () {
        if($(this).is(':checked')){
            $('#task-options').removeClass('d-none');
            $('#task-status').removeClass('d-none');
        } else {
            $('#task-options').addClass('d-none');
            $('#task-status').addClass('d-none');
        }
    });

    $('#without_deadline').click(function() {
        var check = $('#without_deadline').is(":checked") ? true : false;
        if (check == true) {
            $('#deadlineBox').hide();
        } else {
            $('#deadlineBox').show();
        }
    });

    $('#is_public').change(function () {
        $('#add_members').toggleClass('d-none');
    });

    $('#save-copy-project').click(function() {
        var url = "{{ route('projects.store') }}";
        $.easyAjax({
            url: url,
            container: '#createDuplicateProject',
            type: "POST",
            blockUI: true,
            disableButton: true,
            data: $('#createDuplicateProject').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    $(MODAL_LG).modal('hide');
                    showTable();
                }
            }
        })
    });

    $("#selectEmployee").selectpicker({
        actionsBox: true,
        selectAllText: "{{ __('modules.permission.selectAll') }}",
        deselectAllText: "{{ __('modules.permission.deselectAll') }}",
        multipleSeparator: " ",
        selectedTextFormat: "count > 8",
        countSelectedText: function (selected, total) {
            return selected + " {{ __('app.membersSelected') }} ";
        }
    });

    $(document).ready(function() {
        $(".multiple-users").selectpicker({
                actionsBox: true,
                selectAllText: "{{ __('modules.permission.selectAll') }}",
                deselectAllText: "{{ __('modules.permission.deselectAll') }}",
                multipleSeparator: " ",
                selectedTextFormat: "count > 8",
                countSelectedText: function(selected, total) {
                    return selected + " {{ __('app.membersSelected') }} ";
                }
        });
    });
    $('#createDuplicateProject').on('change', '#employee_department', function () {
        let id = $(this).val();
        if (id === '' || id.length === 0) {
            id = 0;
        }
        let userId = '{{ $project->members->pluck("user.id")->implode(",") }}';
        let url = "{{ route('departments.members', ':id') }}?userId="+userId;
        url = url.replace(':id', id);

        $.easyAjax({
            url: url,
            type: "GET",
            container: '#save-project-data-form',
            blockUI: true,
            redirect: true,
            success: function (data) {
                $('#selectEmployee').html(data.data);
                $('#selectEmployee').selectpicker('refresh');
            }
        })
    });
</script>
