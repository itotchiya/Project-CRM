@php
    $addProjectCategoryPermission = user()->permission('manage_project_category');
    $addEmployeePermission = user()->permission('add_employees');
    $addProjectFilePermission = user()->permission('add_project_files');
    $addPublicProjectPermission = user()->permission('create_public_project');
    $addProjectMemberPermission = user()->permission('add_project_members');
    $addProjectNotePermission = user()->permission('add_project_note');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<div class="row">
    <div class="col-sm-12">
        <x-form id="save-project-data-form">
            <div class="bg-white rounded add-client">
                <h4 class="p-20 mb-0 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.projectDetails')</h4>
                <input type="hidden" name="template_id" value="{{ $projectTemplate->id ?? '' }}">
                <div class="p-20 row">
                    <div class="col-lg-4 col-md-4">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.taskShortCode')"
                                      fieldName="project_code" fieldRequired="false" fieldId="project_code"
                                      :fieldPlaceholder="__('placeholders.writeshortcode')" :fieldValue="$project ? $project->project_short_code : ''"/>
                    </div>

                    <div class="col-lg-8 col-md-8">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.projects.projectName')"
                                      fieldName="project_name" fieldRequired="true" fieldId="project_name"
                                      :fieldPlaceholder="__('placeholders.project')"
                                      :fieldValue="($project ? $project->project_name : (($projectTemplate) ? $projectTemplate->project_name : ''))"/>
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
                            <div class="mt-5 d-flex">
                                <x-forms.checkbox fieldId="without_deadline"
                                :checked="($project && $project->deadline == null) ? true : false" :fieldLabel="__('modules.projects.withoutDeadline')"  fieldName="without_deadline"/>
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
                                    <option
                                        @if (($projectTemplate && $projectTemplate->category_id == $category->id) || ($project && $project->category_id == $category->id)) selected
                                        @endif
                                        value="{{ $category->id }}">
                                        {{ $category->category_name }}
                                    </option>
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

                    @if (!in_array('client', user_roles()))
                        <div class="col-md-4">
                            <x-forms.label class="my-3" fieldId="department" :fieldLabel="__('app.department')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control multiple-users" multiple name="team_id[]" id="employee_department"
                                        data-live-search="true">
                                    @foreach ($teams as $team)
                                        <option
                                        data-content="<span class='p-2 border badge badge-pill badge-light'>{{ $team->team_name }}</span>"
                                        @if ($project && $project->team_id == $team->id) selected @endif value="{{ $team->id }}">{{ $team->team_name }}</option>
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>
                    @endif

                    <div class="@if(!in_array('clients', user_modules())) d-none @endif col-md-4">
                        @if (isset($client) && !is_null($client))
                            <x-forms.label class="my-3" fieldId="client_id" :fieldLabel="__('app.client')">
                            </x-forms.label>

                            <input type="hidden" name="client_id" id="client_id" value="{{ $client->id }}">
                            <input type="text" value="{{ $client->name_salutation }}"
                                   class="form-control height-35 f-15 readonly-background" readonly>
                        @else
                            <x-client-selection-dropdown labelClass="my-3" :clients="$clients" fieldRequired="false"
                                                         :selected="request('default_client') ?? null"/>
                        @endif
                    </div>

                    @if ($addProjectNotePermission == 'all' || $addProjectNotePermission == 'added')
                        <div class="col-md-12 col-lg-6">
                            <div class="my-3 form-group">
                                <x-forms.label class="my-3" fieldId="project_summary"
                                               :fieldLabel="__('modules.projects.projectSummary')">
                                </x-forms.label>
                                <div id="project_summary">{!! $projectTemplate->project_summary ?? '' !!}{!! ($project) ? $project->project_summary : '' !!}</div>
                                <textarea name="project_summary" id="project_summary-text"
                                          class="d-none">{!! $projectTemplate->project_summary ?? '' !!}{!! ($project) ? $project->project_summary : '' !!}</textarea>
                            </div>
                        </div>
                    @else
                        <div class="col-md-12 col-lg-12">
                            <div class="my-3 form-group">
                                <x-forms.label class="my-3" fieldId="project_summary"
                                               :fieldLabel="__('modules.projects.projectSummary')">
                                </x-forms.label>
                                <div id="project_summary">{!! $projectTemplate->project_summary ?? '' !!}{!! ($project) ? $project->project_summary : '' !!}</div>
                                <textarea name="project_summary" id="project_summary-text"
                                          class="d-none">{!! $projectTemplate->project_summary ?? '' !!} {!! ($project) ? $project->project_summary : '' !!}</textarea>
                            </div>
                        </div>
                    @endif

                    @if ($addProjectNotePermission == 'all' || $addProjectNotePermission == 'added')
                        <div class="col-md-12 col-lg-6">
                            <div class="my-3 form-group">
                                <x-forms.label class="my-3" fieldId="notes"
                                               :fieldLabel="__('modules.projects.note')">
                                </x-forms.label>
                                <div id="notes">{!! $projectTemplate->notes ?? '' !!} {!! ($project) ? $project->notes : '' !!}</div>
                                <textarea name="notes" id="notes-text"
                                          class="d-none">{!! $projectTemplate->notes ?? '' !!} {!! ($project) ? $project->notes : '' !!}</textarea>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-12 col-lg-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3"
                                for="usr">@lang('modules.projects.viewPublicGanttChart')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="public_gantt_chart-yes" :fieldLabel="__('app.enable')" fieldName="public_gantt_chart"
                                    fieldValue="enable" checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="public_gantt_chart-no" :fieldLabel="__('app.disable')" fieldValue="disable"
                                    fieldName="public_gantt_chart" ></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3"
                                for="usr">@lang('app.public') @lang('modules.tasks.taskBoard')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="public_taskboard-yes" :fieldLabel="__('app.enable')" fieldName="public_taskboard"
                                    fieldValue="enable" checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="public_taskboard-no" :fieldLabel="__('app.disable')" fieldValue="disable"
                                    fieldName="public_taskboard" ></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3"
                                for="usr">@lang('modules.projects.needApproval')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="need_approval_by_admin-yes" :fieldLabel="__('app.enable')" fieldName="need_approval_by_admin"
                                    fieldValue="1">
                                </x-forms.radio>
                                <x-forms.radio fieldId="need_approval_by_admin-no" :fieldLabel="__('app.disable')" fieldValue="0"
                                    fieldName="need_approval_by_admin" checked="true"></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    @if ($addPublicProjectPermission == 'all')
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="mt-2 d-flex">
                                    <x-forms.checkbox fieldId="is_public"
                                                      :fieldLabel="__('modules.projects.createPublicProject')"
                                                      fieldName="public"/>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($addProjectMemberPermission == 'all' || $addProjectMemberPermission == 'added')
                        <div class="col-md-12" id="add_members">
                            <div class="my-3 form-group">
                                <x-forms.label class="my-3" fieldId="selectEmployee" fieldRequired="true"
                                               :fieldLabel="__('modules.projects.addMemberTitle')">
                                </x-forms.label>

                                <x-forms.input-group>
                                    <select class="form-control multiple-users" multiple name="user_id[]"
                                            id="selectEmployee" data-live-search="true" data-size="8">
                                        @foreach ($employees as $item)
                                            {{-- @if ($item->status === 'active' || (request()->has('default_assign') && request('default_assign') == $item->id) || (isset($projectTemplateMembers) && in_array($item->id, $projectTemplateMembers)) || (isset($projectMembers) && in_array($item->id, $projectMembers))) --}}
                                            <x-user-option
                                                :user="$item"
                                                :pill="true"
                                                :selected="(request()->has('default_assign') && request('default_assign') == $item->id) || (isset($projectTemplateMembers) && in_array($item->id, $projectTemplateMembers)) || (isset($projectMembers) && in_array($item->id, $projectMembers))"
                                            />
                                            {{-- @endif --}}
                                        @endforeach
                                    </select>

                                    @if ($addEmployeePermission == 'all' || $addEmployeePermission == 'added')
                                        <x-slot name="append">
                                            <button id="add-employee" type="button"
                                                    class="btn btn-outline-secondary border-grey"
                                                    data-toggle="tooltip" data-original-title="{{ __('modules.projects.addMemberTitle') }}">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>
                        </div>
                    @elseif(in_array('employee', user_roles()))
                        <input type="hidden" name="user_id[]" value="{{ user()->id }}">
                    @endif

                </div>

                <h4 class="p-20 mb-0 f-21 font-weight-normal  border-top-grey">
                    <a href="javascript:;" class="text-dark toggle-project-other-details"><i
                            class="fa fa-chevron-down"></i>
                        @lang('modules.client.clientOtherDetails')</a>
                </h4>

                <div class="p-20 row d-none" id="other-project-details">
                    @if ($addProjectFilePermission == 'all' || $addProjectFilePermission == 'added')
                        <div class="col-lg-12">
                            <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2"
                                                   :fieldLabel="__('app.menu.addFile')" fieldName="file"
                                                   fieldId="file-upload-dropzone"/>
                            <input type="hidden" name="projectID" id="projectID">
                        </div>
                    @endif

                    <div class="col-lg-4">
                        <x-forms.select fieldId="currency_id" :fieldLabel="__('modules.invoices.currency')"
                                        fieldName="currency_id" search="true">
                            @foreach ($currencies as $currency)
                                <option @if (company()->currency_id == $currency->id) selected @endif
                                value="{{ $currency->id }}">
                                    {{ $currency->currency_symbol . ' (' . $currency->currency_code . ')' }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.projects.projectBudget')"
                                        fieldName="project_budget" fieldId="project_budget"
                                        :fieldValue="$project ? $project->project_budget : ''"
                                        :fieldPlaceholder="__('placeholders.price')"/>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2"
                                        :fieldLabel="__('modules.projects.hours_allocated')" fieldName="hours_allocated" :fieldValue="$project ? $project->hours_allocated : ''"
                                        fieldId="hours_allocated" :fieldPlaceholder="__('placeholders.hourEstimate')"/>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="mt-5 d-flex">
                                <x-forms.checkbox fieldId="manual_timelog" :checked="($project ? $project->manual_timelog == 'enable' : ($projectTemplate ? $projectTemplate->manual_timelog == 'enable' : ''))" :fieldLabel="__('modules.projects.manualTimelog')"  fieldName="manual_timelog"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="mt-5 d-flex">
                                <x-forms.checkbox fieldId="miroboard_checkbox" :checked="($project ? $project->enable_miroboard : '')"
                                                  :fieldLabel="__('modules.projects.enableMiroboard')"
                                                  fieldName="miroboard_checkbox"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-6 {{!is_null($project) && $project->enable_miroboard ? '' : 'd-none'}}" id="miroboard_detail">
                        <div class="my-3 form-group">
                            <div class="row">
                                <div class="mt-6 col-md-6">
                                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2"
                                                  :fieldLabel="__('modules.projects.miroBoardId')"
                                                  fieldName="miro_board_id" fieldRequired="true"
                                                  fieldId="miro_board_id" :fieldValue="$project ? $project->miro_board_id : ''"/>
                                </div>
                                <div class="col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <div class="mt-5 d-flex">
                                            <x-forms.checkbox fieldId="client_access"
                                            :fieldLabel="__('modules.projects.clientMiroAccess')" :checked="$project ? $project->client_access : ''"
                                            fieldName="client_access"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type = "hidden" name = "mention_user_ids" id = "mentionUserId" class ="mention_user_ids">

                    <div class="col-md-6 col-lg-4" id="clientNotification">
                        <div class="form-group">
                            <div class="mt-5 d-flex">
                                <x-forms.checkbox fieldId="client_task_notification" :checked="($project ? $project->allow_client_notification == 'enable' : '')"
                                                  :fieldLabel="__('modules.projects.clientTaskNotification')"
                                                  fieldName="client_task_notification"/>
                            </div>
                        </div>
                    </div>
                        <x-forms.custom-field :fields="$fields" class="col-md-12"></x-forms.custom-field>
                </div>


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

    var add_project_files = "{{ $addProjectFilePermission }}";
    var add_project_note_permission = "{{ $addProjectNotePermission }}";

    $(document).ready(function () {

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        $('#without_deadline').click(function() {
            var check = $('#without_deadline').is(":checked") ? true : false;
            if (check == true) {
                $('#deadlineBox').hide();

            } else {
                $('#deadlineBox').show();
            }
        });

        if (add_project_files == "all") {

            let checkSize = true;
            Dropzone.autoDiscover = false;

            //Dropzone class
            myDropzone = new Dropzone("div#file-upload-dropzone", {
                dictDefaultMessage: "{{ __('app.dragDrop') }}",
                url: "{{ route('files.multiple_upload') }}",
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
                init: function () {
                    myDropzone = this;
                }
            });
            myDropzone.on('sending', function (file, xhr, formData) {
                checkSize = true;
                var ids = $('#projectID').val();
                formData.append('project_id', ids);
            });
            myDropzone.on('uploadprogress', function () {
                $.easyBlockUI();
            });
            myDropzone.on('queuecomplete', function () {
                var msgs = "@lang('messages.updateSuccess')";
                var redirect_url = $('#redirect_url').val();
                if (redirect_url != '' && checkSize == true) {
                    window.location.href = decodeURIComponent(redirect_url);
                }

                if (checkSize == true) {
                    window.location.href = "{{ route('projects.index') }}"
                }
            });
            myDropzone.on('removedfile', function () {
                var grp = $('div#file-upload-dropzone').closest(".form-group");
                var label = $('div#file-upload-box').siblings("label");
                $(grp).removeClass("has-error");
                $(label).removeClass("is-invalid");
            });
            myDropzone.on('error', function (file, message) {
                myDropzone.removeFile(file);
                var grp = $('div#file-upload-dropzone').closest(".form-group");
                var label = $('div#file-upload-box').siblings("label");
                $(grp).find(".help-block").remove();
                var helpBlockContainer = $(grp);

                if (helpBlockContainer.length == 0) {
                    helpBlockContainer = $(grp);
                }

                checkSize = false;

                helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
                $(grp).addClass("has-error");
                $(label).addClass("is-invalid");
            });
        }

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
        $("#save-project-data-form .multiple-users").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function (selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });
        var userValues = @json($userData);
        quillMention(userValues, '#project_summary');

        if (add_project_note_permission == 'all' || add_project_note_permission == 'added') {

            quillImageLoad('#notes');
        }


        const dp1 = datepicker('#start_date', {
            position: 'bl',
            onSelect: (instance, date) => {
                dp2.setMin(date);
            },
            ...datepickerConfig
        });

        const dp2 = datepicker('#deadline', {
            position: 'bl',
            onSelect: (instance, date) => {
                dp1.setMax(date);
            },
            ...datepickerConfig
        });

        @if ($project && $project->deadline == null)
            $('#deadlineBox').hide();
        @endif

        $('#without_deadline').click(function () {
            const check = $('#without_deadline').is(":checked") ? true : false;
            if (check == true) {
                $('#deadlineBox').hide();
                $('#without_deadline').closest('.col-md-6.col-lg-4').removeClass('col-md-6 col-lg-4').addClass('col-md-8');
            } else {
                $('#deadlineBox').show();
                $('#without_deadline').closest('.col-md-8').removeClass('col-md-8').addClass('col-md-6 col-lg-4');
            }
        });

        $('#save-project-form').click(function () {
            let note = document.getElementById('project_summary').children[0].innerHTML;
            document.getElementById('project_summary-text').value = note;
            var mention_user_id = $('#project_summary span[data-id]').map(function(){
                            return $(this).attr('data-id')
                        }).get();
            $('#mentionUserId').val(mention_user_id.join(','));

            if (add_project_note_permission == 'all' || add_project_note_permission == 'added') {

                note = document.getElementById('notes').children[0].innerHTML;
                document.getElementById('notes-text').value = note;
            }
            const url = "{{ route('projects.store') }}";
            var data = $('#save-project-data-form').serialize() + "&projectID={{$project ? $project->id : ''}}";

            $.easyAjax({
                url: url,
                container: '#save-project-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-project-form",
                data: data,
                success: function (response) {
                    if ((add_project_files === "all") &&
                        myDropzone.getQueuedFiles().length > 0) {
                        $('#projectID').val(response.projectID);
                        myDropzone.processQueue();
                    } else if (typeof response.redirectUrl !== 'undefined') {
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        });

        $('#addProjectCategory').click(function () {
            const url = "{{ route('projectCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#department-setting').click(function () {
            const url = "{{ route('departments.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#client_view_task').change(function () {
            $('#clientNotification').toggleClass('d-none');
        });

        $('.toggle-project-other-details').click(function () {
            $(this).find('svg').toggleClass('fa-chevron-down fa-chevron-up');
            $('#other-project-details').toggleClass('d-none');
        });

        $('#is_public').change(function () {
            $('#add_members').toggleClass('d-none');
        });

        $('#miroboard_checkbox').change(function () {
            $('#miroboard_detail').toggleClass('d-none');
        });

        $('#add-employee').click(function () {
            $(MODAL_XL).modal('show');

            const url = "{{ route('employees.create') }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: MODAL_XL,
                success: function (response) {
                    if (response.status === "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });

    $('#save-project-data-form').on('change', '#employee_department', function () {
        let id = $(this).val();
        if (id === '' || id.length === 0) {
            id = 0;
        }
        let userId = @json($projectTemplateMembers ?? []);
        let url = "{{ route('departments.members', ':id') }}";
        if (userId.length > 0) {
            url += "?userId=" + userId.join(",");
        }
        url = url.replace(':id', id);

        $.easyAjax({
            url: url,
            type: "GET",
            container: '#save-project-data-form',
            blockUI: true,
            redirect: true,
            success: function (data) {
                var atValues = data.userData;
                destory_editor('#project_summary');
                quillMention(atValues, '#project_summary');
                $('#selectEmployee').html(data.data);
                $('#selectEmployee').selectpicker('refresh');
            }
        })
    });

</script>
