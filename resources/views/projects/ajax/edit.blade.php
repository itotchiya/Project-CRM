@php
$addProjectCategoryPermission = user()->permission('manage_project_category');
$addClientPermission = user()->permission('add_clients');
$editProjectMemberPermission = user()->permission('edit_project_members');
$addEmployeePermission = user()->permission('add_employees');
$addProjectMemberPermission = user()->permission('add_project_members');
$addProjectMemberPermission = user()->permission('add_project_members');
$createPublicProjectPermission = user()->permission('create_public_project');

@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-project-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.projects.projectInfo')</h4>
                <div class="row p-20">
                    <div class="col-lg-4 col-md-4">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.taskShortCode')"
                            fieldName="project_code" fieldRequired="false" fieldId="project_code"
                            :fieldPlaceholder="__('placeholders.writeshortcode')" :fieldValue="$project->project_short_code" />
                    </div>
                    <div class="col-lg-8 col-md-8">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.projects.projectName')"
                            fieldName="project_name" fieldRequired="true" fieldId="project_name"
                            :fieldValue="$project->project_name" :fieldPlaceholder="__('placeholders.project')" />
                    </div>
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <div class="col-md-6 col-lg-4">
                        <x-forms.datepicker fieldId="start_date" fieldRequired="true"
                            :fieldLabel="__('modules.projects.startDate')" fieldName="start_date"
                            :fieldValue="($project->start_date ? $project->start_date->format(company()->date_format) : '')"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-6 col-lg-4" id="deadlineBox">
                        <x-forms.datepicker fieldId="deadline" fieldRequired="true"
                            :fieldLabel="__('modules.projects.deadline')" fieldName="deadline"
                            :fieldValue="($project->deadline ? $project->deadline->format(company()->date_format) : '')"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="{{ $project->deadline == null ? 'col-md-8' : 'col-md-6 col-lg-4' }}">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="without_deadline"
                                    :checked="($project->deadline == null) ? true : false"
                                    :fieldLabel="__('modules.projects.withoutDeadline')" fieldName="without_deadline" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <x-forms.label class="my-3" fieldId="category_id"
                            :fieldLabel="__('modules.projects.projectCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="project_category_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($categories as $category)
                                    <option @selected($project->category_id == $category->id) value="{{ $category->id }}">
                                        {{ $category->category_name }}</option>
                                @endforeach
                            </select>

                            @if ($addProjectCategoryPermission == 'all' || $addProjectCategoryPermission == 'added')
                                <x-slot name="append">
                                    <button id="addProjectCategory" type="button"
                                        class="btn btn-outline-secondary border-grey"
                                        data-toggle="tooltip" data-original-title="{{__('modules.projectCategory.addProjectCategory') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    @if (!in_array('client', user_roles()) && ($editProjectMembersPermission == 'all' || $editPermission == 'all'))
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

                    <div class="col-md-4 @if(!in_array('clients', user_modules())) d-none @endif">

                        <x-forms.input-group>
                            <x-client-selection-dropdown labelClass="my-3" :clients="$clients" fieldRequired="false"
                                                         :selected="$project->client_id ?? null"/>
{{--                            @if ($addClientPermission == 'all' || $addClientPermission == 'added')--}}
{{--                                <x-slot name="append">--}}
{{--                                    <button id="add-client" type="button"--}}
{{--                                        class="btn btn-outline-secondary border-grey"--}}
{{--                                        data-toggle="tooltip" data-original-title="{{__('modules.client.addNewClient') }}">@lang('app.add')</button>--}}
{{--                                </x-slot>--}}
{{--                            @endif--}}
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-12 col-lg-12">
                        <div class="form-group my-3">
                            <x-forms.label class="my-3" fieldId="project_summary"
                                :fieldLabel="__('modules.projects.projectSummary')">
                            </x-forms.label>
                            <div id="project_summary">{!! $project->project_summary !!}</div>
                            <textarea name="project_summary" id="project_summary-text"
                                class="d-none">{!! $project->project_summary !!}</textarea>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3"
                                for="usr">@lang('modules.projects.viewPublicGanttChart')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="public_gantt_chart-yes" :fieldLabel="__('app.enable')" fieldName="public_gantt_chart"
                                    fieldValue="enable" :checked="$project->public_gantt_chart == 'enable'"  checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="public_gantt_chart-no" :fieldLabel="__('app.disable')" fieldValue="disable"
                                    fieldName="public_gantt_chart" :checked="$project->public_gantt_chart == 'disable'"></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3"
                                for="usr">@lang('app.public') @lang('modules.tasks.taskBoard')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="public_taskboard-yes" :fieldLabel="__('app.enable')" fieldName="public_taskboard"
                                    fieldValue="enable" :checked="$project->public_taskboard == 'enable'"  checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="public_taskboard-no" :fieldLabel="__('app.disable')" fieldValue="disable"
                                    fieldName="public_taskboard" :checked="$project->public_taskboard == 'disable'"></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3"
                                for="usr">@lang('modules.projects.needApproval')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="need_approval_by_admin-yes" :fieldLabel="__('app.enable')" fieldName="need_approval_by_admin"
                                    fieldValue="1" :checked="$project->need_approval_by_admin == '1'"  checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="need_approval_by_admin-no" :fieldLabel="__('app.disable')" fieldValue="0"
                                    fieldName="need_approval_by_admin" :checked="$project->need_approval_by_admin == '0'"></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    @if ($project->public == 1 && $createPublicProjectPermission == 'all')
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="d-flex mt-2">
                                    <x-forms.checkbox fieldId="is_private"
                                        :fieldLabel="__('modules.projects.createPrivateProject')" fieldName="private" />
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($project->public == 0 && $createPublicProjectPermission == 'all')
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="d-flex mt-2">
                                    <x-forms.checkbox fieldId="is_public"
                                        :fieldLabel="__('modules.projects.changeToPublicProject')" fieldName="public" />
                                </div>
                            </div>
                        </div>
                    @endif


                    @if ($editProjectMembersPermission == 'all' || $editPermission == 'all')
                        <div class="col-md-12 @if ($project->public == 1) d-none @endif" id="edit_members">
                           <div class="form-group my-3">

                            @php
                                // Retrieve selected employee IDs and their statuses
                                $selectedEmployeeIds = $project->members->pluck('user_id')->toArray();
                                $selectedEmployees = $employees->filter(function ($employee) use ($selectedEmployeeIds) {
                                    return in_array($employee->id, $selectedEmployeeIds);
                                });

                                // Get the active employees list
                                $activeEmployees = $employees->filter(function ($employee) {
                                    return $employee->status === 'active';
                                });

                                $employeesToShow = $activeEmployees->merge($selectedEmployees->filter(function ($employee) {
                                    return !$employee->is_active;
                                }));
                            @endphp
                                <x-forms.label fieldId="selectAssignee"
                                fieldRequired="true" :fieldLabel="__('modules.tasks.assignTo')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control multiple-users" multiple name="member_id[]"
                                        id="selectEmployee" data-live-search="true" data-size="8">
                                        @foreach ($employeesToShow as $item)
                                            @php
                                                $selected = '';
                                                $isMember = false;
                                            @endphp

                                            @foreach ($project->members as $member)
                                                @if ($member->user->id == $item->id)
                                                    @php
                                                        $selected = 'selected';
                                                        $isMember = true;
                                                    @endphp
                                                @endif
                                            @endforeach
                                            @if ($item->status === 'active' || $isMember)
                                                <x-user-option :user="$item" :selected="$selected" :pill="true"/>
                                            @endif
                                        @endforeach
                                    </select>
                                    @if ($addEmployeePermission == 'all' || $addEmployeePermission == 'added')
                                        <x-slot name="append">
                                            <button id="add-employee" type="button"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>
                        </div>
                    @endif

                    @if ($project->public == 1 && $editProjectMembersPermission || $editPermission == 'all')
                        <div class="col-md-12 d-none" id="add_members">
                            <div class="form-group my-3">
                                <x-forms.label class="my-3" fieldId="selectEmployee" fieldRequired="true"
                                    :fieldLabel="__('modules.projects.addMemberTitle')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control multiple-users" multiple name="user_id[]"
                                        id="selectEmployee" data-live-search="true" data-size="8">
                                        @if ($employees != '')

                                            @foreach ($employees as $item)
                                                <x-user-option
                                                    :user="$item"
                                                    :pill="true"
                                                    :selected="request()->has('default_assign') && request('default_assign') == $item->id ||(isset($projectTemplateMembers) && in_array($item->id, $projectTemplateMembers))"
                                                />

                                            @endforeach
                                        @endif
                                    </select>

                                    @if ($addEmployeePermission == 'all' || $addEmployeePermission == 'added')
                                        <x-slot name="append">
                                            <button id="add-employee" type="button"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>
                        </div>
                    @elseif(in_array('employee', user_roles()))
                        <input type="hidden" name="user_id[]" value="{{ user()->id }}">
                    @endif

                    <div class="col-md-12 col-lg-4">
                        <x-forms.select fieldId="project_status"
                            :fieldLabel="__('app.project') . ' ' . __('app.status')" fieldName="status" search="true">
                            @foreach ($projectStatus as $status)
                                <option
                                data-content="<i class='fa fa-circle mr-1 f-15' style='color:{{$status->color}}'></i>{{ $status->status_name }}"
                                @selected($project->status == $status->status_name)
                                value="{{$status->status_name}}">
                                </option>

                            @endforeach


                        </x-forms.select>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <x-forms.range class="mr-0 mr-lg-2 mr-md-2"
                            :disabled="($project->calculate_task_progress == 'true' ? 'true' : 'false')"
                            :fieldLabel="__('modules.projects.projectCompletionStatus')" fieldName="completion_percent"
                            fieldId="completion_percent" :fieldValue="$project->completion_percent" />
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="calculate-task-progress"
                                    :checked="($project->calculate_task_progress == 'true') ? true : false"
                                    :fieldLabel="__('modules.projects.calculateTasksProgress')"
                                    fieldName="calculate_task_progress" />
                                    <i class="fa fa-question-circle mt-2" title="{{__('messages.calculateTaskProgress')}}" data-toggle="tooltip"></i>
                            </div>
                        </div>
                    </div>


                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-top-grey">
                    @lang('modules.client.clientOtherDetails')</h4>

                <div class="row p-20">
                    <div class="col-lg-4">
                        <x-forms.select fieldId="currency_id" :fieldLabel="__('modules.invoices.currency')"
                            fieldName="currency_id" search="true">
                            @foreach ($currencies as $currency)
                                <option @selected($currency->id == $project->currency_id) value="{{ $currency->id }}">
                                    {{ $currency->currency_symbol . ' (' . $currency->currency_code . ')' }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.projects.projectBudget')"
                            fieldName="project_budget" fieldId="project_budget" :fieldValue="$project->project_budget"
                            :fieldPlaceholder="__('placeholders.price')" />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.projects.hours_allocated')"
                            fieldName="hours_allocated" fieldId="hours_allocated"
                            :fieldValue="$project->hours_allocated" :fieldPlaceholder="__('placeholders.hourEstimate')" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="manual_timelog"
                                    :fieldLabel="__('modules.projects.manualTimelog')" :checked="($project->manual_timelog
                                    == 'enable')" fieldName="manual_timelog" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4" id="clientNotification">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="client_task_notification" :checked="($project->allow_client_notification
                                == 'enable')"
                                    :fieldLabel="__('modules.projects.clientTaskNotification')"
                                    fieldName="client_task_notification" />
                            </div>
                        </div>
                    </div>

                    @if ($editPermission == 'all')
                        <div class="col-lg-3 col-md-6">
                            @php
                                $activeEmployees = $employees->filter(function ($employee) {
                                    return $employee->status !== 'deactive';
                                });

                                $selectedEmployee = $employees->firstWhere('id', $project->added_by);

                                if ($selectedEmployee && $selectedEmployee->status === 'deactive') {
                                    $employeesToShow = $activeEmployees->push($selectedEmployee);
                                } else {
                                    $employeesToShow = $activeEmployees;
                                }
                            @endphp
                            <x-forms.select fieldId="added_by" :fieldLabel="__('app.added').' '.__('app.by')"
                                fieldName="added_by">
                                <option value="">--</option>
                                @foreach ($employeesToShow as $item)
                                    <x-user-option :user="$item" :selected="$project->added_by == $item->id" />
                                @endforeach
                            </x-forms.select>
                        </div>
                    @endif



                </div>

                <div class="row p-20">
                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="miroboard_checkbox"
                                    :fieldLabel="__('modules.projects.enableMiroboard')" fieldName="miroboard_checkbox"
                                    :checked="$project ? $project->enable_miroboard : ''"/>
                            </div>
                        </div>
                    </div>
                    <input type = "hidden" name = "mention_user_ids" id = "mentionUserId" class ="mention_user_ids">

                    <div class="col-md-6 col-lg-6 {{!is_null($project) && $project->enable_miroboard ? '' : 'd-none'}}" id="miroboard_detail">
                        <div class="form-group my-3">
                            <div class="row">
                                <div class="col-md-6 mt-6">
                                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.projects.miroBoardId')"
                                        fieldName="miro_board_id" fieldRequired="true" fieldId="miro_board_id" :fieldValue="$project->miro_board_id"/>
                                </div>
                                <div class="col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <div class="d-flex mt-5">
                                    <x-forms.checkbox fieldId="client_access"
                                        :fieldLabel="__('modules.projects.clientMiroAccess')" fieldName="client_access"
                                        :checked="$project ? $project->client_access : ''"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <x-forms.custom-field :fields="$fields" :model="$project"></x-forms.custom-field>


                <x-form-actions>
                    <x-forms.button-primary id="save-project-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('projects.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script>
    $(document).ready(function() {

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

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

        const dp1 = datepicker('#start_date', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', $project->start_date) }}"),
            onSelect: (instance, date) => {
                dp2.setMin(date);
            },
            ...datepickerConfig
        });

        const dp2 = datepicker('#deadline', {
            position: 'bl',
            dateSelected: new Date("{{ $project->deadline ? str_replace('-', '/', $project->deadline) : str_replace('-', '/', now(company()->timezone)) }}"),
            onSelect: (instance, date) => {
                dp1.setMax(date);
            },
            ...datepickerConfig
        });

        @if ($project->deadline == null)
            $('#deadlineBox').hide();
        @endif

        $('#without_deadline').click(function() {
            var check = $('#without_deadline').is(":checked") ? true : false;
            if (check == true) {
                $('#deadlineBox').hide();
                $('#without_deadline').closest('.col-md-6.col-lg-4').removeClass('col-md-6 col-lg-4').addClass('col-md-8');
            } else {
                $('#deadlineBox').show();
                $('#without_deadline').closest('.col-md-8').removeClass('col-md-8').addClass('col-md-6 col-lg-4');
            }
        });
        const atValues = @json($userData);

        quillMention(atValues, '#project_summary');

        $('#save-project-form').click(function() {
            var note = document.getElementById('project_summary').children[0].innerHTML;
            document.getElementById('project_summary-text').value = note;

            var user = $('#project_summary span[data-id]').map(function(){
                            return $(this).attr('data-id')
                        }).get();

            var mention_user_id  =  $.makeArray(user);
            $('#mentionUserId').val(mention_user_id.join(','));
            const url = "{{ route('projects.update', $project->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-project-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file:true,
                buttonSelector: "#save-project-form",
                data: $('#save-project-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        });

        $('#addProjectCategory').click(function() {
            const url = "{{ route('projectCategory.create') }}";
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

        $('#is_private').change(function() {
            $('#add_members').toggleClass('d-none');
            $('#edit_members').addClass('d-none');
        });

        $('#is_public').change(function() {
            $('#edit_members').toggleClass('d-none');
            $('#add_members').addClass('d-none');
        });

        $('#miroboard_checkbox').change(function() {
            $('#miroboard_detail').toggleClass('d-none');
        });

        $('#add-client').click(function() {
            $(MODAL_XL).modal('show');

            const url = "{{ route('clients.create') }}";

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

        $('#calculate-task-progress').change(function() {
            if ($(this).is(':checked')) {
                $('#completion_percent').attr('disabled', 'true');
            } else {
                $('#completion_percent').removeAttr('disabled');
            }
        });

        <x-forms.custom-field-filejs/>

        init(RIGHT_MODAL);
    });

    $('#save-project-data-form').on('change', '#employee_department', function () {
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
