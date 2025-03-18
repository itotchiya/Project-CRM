@php
    $manageTypePermission = user()->permission('manage_ticket_type');
    $manageAgentPermission = user()->permission('manage_ticket_agent');
    $manageChannelPermission = user()->permission('manage_ticket_channel');
    $manageGroupPermission = user()->permission('manage_ticket_groups');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-ticket-data-form">
            <input type="hidden" id="replyID">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.tickets.ticketDetail')</h4>
                <div class="row p-20">
                    @if (!in_array('client', user_roles()))
                        @if ($addPermission == 'all')
                            @if (!(isset($client)) && !(isset($employee)) && in_array('clients', user_modules()) && in_array('employees', user_modules()))
                                <div class="col-md-4">
                                    <div class="form-group my-3">
                                        <x-forms.label fieldId="requester-client"
                                                       :fieldLabel="__('modules.tickets.requester')"/>
                                        <div class="d-flex">
                                            <x-forms.radio fieldId="requester-client" :fieldLabel="__('app.client')"
                                                           fieldName="requester_type" fieldValue="client"
                                                           checked="true">
                                            </x-forms.radio>
                                            <x-forms.radio fieldId="requester-employee" :fieldLabel="__('app.employee')"
                                                           fieldValue="employee"
                                                           fieldName="requester_type"></x-forms.radio>
                                        </div>
                                    </div>
                                </div>
                            @elseif(!in_array('employees', user_modules()))
                                <input type="hidden" name="requester_type" id="requester_type" value="client">
                            @elseif(!in_array('clients', user_modules()))
                                <input type="hidden" name="requester_type" id="requester_type" value="employee">
                            @endif
                            <input type = "hidden" name = "mention_user_ids" id = "mentionUserId" class ="mention_user_ids">

                            <div class="col-md-4  @if (isset($employee) || !in_array('clients', user_modules())) d-none @endif " id="client-requester">
                                @if (isset($client) && !is_null($client))
                                    <x-forms.label fieldId="requester-client" class="mt-3"
                                                   :fieldLabel="__('modules.tickets.requesterName')"/>
                                    <input type="hidden" name="requester_type" id="requester_type" value="client">
                                    <input type="hidden" name="client_id" id="client_id" value="{{ $client->id }}">
                                    <input type="text" value="{{ $client->name_salutation }}"
                                           class="form-control height-35 f-15 readonly-background" readonly>
                                @else
                                    <x-forms.select fieldId="client_id"
                                                    :fieldLabel="__('modules.tickets.requesterName')"
                                                    fieldName="client_id"
                                                    search="true" alignRight="true" fieldRequired="true">
                                        <option value="">--</option>
                                        @foreach ($clients as $client)
                                            <x-user-option :user="$client" :additionalText="$client->clientDetails->company_name" />
                                        @endforeach
                                    </x-forms.select>
                                @endif
                            </div>

                            <div class="col-md-4 @if (!(isset($employee)) && in_array('clients', user_modules())) d-none @endif" id="employee-requester">
                                @if(isset($employee) && !is_null($employee))
                                    <x-forms.label class="my-3" fieldId="requestuser_id"
                                                   :fieldLabel="__('modules.tickets.requesterName')"
                                                   fieldRequired="true">
                                    </x-forms.label>
                                    <input type="hidden" name="requester_type" id="requester_type" value="employee">
                                    <input type="hidden" name="user_id" id="user_id" value="{{ $employee->id }}">
                                    <input type="text" value="{{ $employee->name }}"
                                           class="form-control height-35 f-15 readonly-background" readonly>
                                @else
                                    <x-forms.label class="my-3" fieldId="requestuser_id"
                                                   :fieldLabel="__('modules.tickets.requesterName')"
                                                   fieldRequired="true">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" name="user_id" id="user_id"
                                                data-live-search="true" data-size="8">
                                            <option value="">--</option>
                                            @foreach ($employees as $employee)
                                                <x-user-option :user="$employee" />
                                            @endforeach
                                        </select>
                                    </x-forms.input-group>
                                @endif
                            </div>
                        @else
                            <input type="hidden" name="requester_type" value="employee">
                            <input type="hidden" id="user_id" name="user_id" value="{{ user()->id }}">
                        @endif
                    @else
                        <input type="hidden" name="requester_type" value="client">
                        <input type="hidden" id="client_id" name="client_id" value="{{ user()->id }}">
                    @endif
                    <div class="col-md-4 assign_group">
                        <x-forms.label class="mt-3" fieldId="ticket_group" fieldRequired="true"
                            :fieldLabel="__('modules.tickets.assignGroup')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" id="ticket_group" name="group_id"
                                data-live-search="true">
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                @endforeach
                            </select>
                            @if($manageGroupPermission == 'all')
                                <x-slot name="append">
                                    <button id="manage-groups" type="button"
                                        class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    @if (!in_array('client', user_roles()))
                        <div class="col-md-6 col-lg-4">
                            <x-forms.label class="mt-3" fieldId="ticket_agent_id" :fieldLabel="__('modules.tickets.agent')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="agent_id" id="ticket_agent_id"
                                        data-live-search="true" data-size="8">
                                    <option value="">--</option>
                                    @foreach ($groups as $group)
                                        @if (count($group->enabledAgents) > 0)
                                            <optgroup label="{{ $group->group_name }}">
                                                @foreach ($group->enabledAgents as $agent)

                                                    <x-user-option :user="$agent->user" :agent="true"></x-user-option>

                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                                @if ($manageAgentPermission == 'all')
                                    <x-slot name="append">
                                        <button id="add-agent" type="button"
                                                class="btn btn-outline-secondary border-grey"
                                                data-toggle="tooltip" data-original-title="{{ __('app.addNew').' '.__('modules.tickets.agent') }}">@lang('app.add')</button>
                                    </x-slot>
                                @endif
                            </x-forms.input-group>
                        </div>

                    @endif
                    <div class="col-md-4">
                        <x-forms.select fieldId="project_id" :fieldLabel="__('app.project')"
                                        fieldName="project_id"
                                        search="true" alignRight="true">
                            <option value="">--</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.label class="my-3" fieldId="ticket_type_id" :fieldLabel="__('modules.invoices.type')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="type_id" id="ticket_type_id"
                                    data-live-search="true" data-size="8">
                                <option value="">--</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->type }}</option>
                                @endforeach
                            </select>
                            @if ($manageTypePermission == 'all')
                                <x-slot name="append">
                                    <button id="add-type" type="button"
                                            class="btn btn-outline-secondary border-grey"
                                            data-toggle="tooltip" data-original-title="{{ __('app.addNew').' '.__('modules.tickets.ticketType') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-12">
                        <x-forms.text :fieldLabel="__('modules.tickets.ticketSubject')" fieldName="subject"
                                      fieldRequired="true" fieldId="subject"/>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="description" :fieldLabel="__('app.description')"
                                           fieldRequired="true">
                            </x-forms.label>
                            <div id="description"></div>
                            <textarea name="description" id="description-text" class="d-none"></textarea>
                        </div>
                        <div class="my-3">
                            <a class="f-15 f-w-500" href="javascript:;" id="add-file"><i
                                    class="fa fa-paperclip font-weight-bold mr-1"></i>@lang('modules.projects.uploadFile')
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row px-4">
                    <div class="col-md-12">
                        <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2 upload-section d-none"
                                               :fieldLabel="__('app.menu.addFile')"
                                               fieldName="file" fieldId="ticket-file-upload-dropzone"/>
                        <input type="hidden" name="image_url" id="image_url">
                    </div>

                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-top-grey">
                    <a href="javascript:;" class="text-dark toggle-other-details"><i class="fa fa-chevron-down"></i>
                        @lang('modules.client.clientOtherDetails')</a>
                </h4>

                <div class="row p-20 d-none" id="other-details">

                    <div class="col-md-6 col-lg-4">
                        <x-forms.select fieldId="priority" :fieldLabel="__('modules.tasks.priority')"
                                        fieldName="priority">
                            <option data-content="<i class='fa fa-circle mr-2 text-dark-green'></i> {{ __('app.low')}}"
                                value="low">{{ __('app.low') }}</option>
                            <option data-content="<i class='fa fa-circle mr-2 text-blue'></i> {{ __('app.medium')}}"
                                value="medium">@lang('app.medium')</option>
                            <option data-content="<i class='fa fa-circle mr-2 text-warning'></i> {{ __('app.high')}}"
                                value="high">@lang('app.high')</option>
                            <option data-content="<i class='fa fa-circle mr-2 text-red'></i> {{ __('app.urgent')}}"
                                value="urgent">@lang('app.urgent')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.label class="my-3" fieldId="ticket_channel_id"
                                       :fieldLabel="__('modules.tickets.channelName')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="channel_id" id="ticket_channel_id"
                                    data-live-search="true" data-size="8">
                                <option value="">--</option>
                                @foreach ($channels as $channel)
                                    <option value="{{ $channel->id }}">{{ $channel->channel_name }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($manageChannelPermission == 'all')
                                <x-slot name="append">
                                    <button id="add-channel" type="button"
                                            class="btn btn-outline-secondary border-grey"
                                            data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('modules.tickets.ticketChannel') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-12">
                        <x-forms.text fieldId="tags" :fieldLabel="__('modules.tickets.tags')" fieldName="tags"/>
                    </div>

                    <x-forms.custom-field :fields="$fields" class="col-md-12"></x-forms.custom-field>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-ticket-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('tickets.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script src="{{ asset('vendor/jquery/tagify.min.js') }}"></script>
<script>
    $(document).ready(function () {

        $('#add-file').click(function () {
            $('.upload-section').removeClass('d-none');
            $('#add-file').addClass('d-none');
            window.scrollTo(0, document.body.scrollHeight);
        });

        getAgents($('#ticket_group').val());

        function getAgents(groupId, exceptThis = null){
            var url = "{{ route('tickets.agent_group', [':id', ':exceptThis'])}}";
            url = url.replace(':id', groupId);
            url = url.replace(':exceptThis', exceptThis);

            $.easyAjax({
                url: url,
                type: "GET",
                success: function(response)
                {
                    var userValues = (response.groupData);
                    destory_editor('#description');
                    quillMention(userValues, '#description');
                    var options = [];
                    var rData = [];
                    if($.isArray(response.data))
                    {
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var selectData = '';
                            options.push(value);
                        });

                        $('#ticket_agent_id').html('<option value="">--</option>' + options);
                    }
                    else
                    {
                        $('#ticket_agent_id').html(response.data);
                    }
                    $('#ticket_agent_id').selectpicker('refresh');
                }
            });
        }

        $('#ticket_group').change(function(){
            var id = $(this).val();
            let requesterName = $('#user_id').val() || $('#client_id').val();
            getAgents(id, requesterName)
        })

        $('#user_id').change(function(){
            let id = $('#ticket_group').val();
            let requesterName =  $('#user_id').val();
            getAgents(id, requesterName)
        })

        $('#client_id').change(function(){
            let id = $('#ticket_group').val();
            let requesterName =  $('#client_id').val();
            getAgents(id, requesterName)
        })

        //Dropzone class
        var ticketDropzone = new Dropzone("div#ticket-file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('ticket-files.store') }}",
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
                ticketDropzone = this;
            }
        });
        ticketDropzone.on('sending', function (file, xhr, formData) {
            var ids = $('#replyID').val();
            formData.append('ticket_reply_id', ids);
            $.easyBlockUI();
        });
        ticketDropzone.on('uploadprogress', function () {
            $.easyBlockUI();
        });
        ticketDropzone.on('queuecomplete', function () {
            var msgs = "@lang('messages.addDiscussion')";
            window.location.href = "{{ route('tickets.index') }}";
        });
        ticketDropzone.on('removedfile', function () {
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).removeClass("has-error");
            $(label).removeClass("is-invalid");
        });
        ticketDropzone.on('error', function (file, message) {
            ticketDropzone.removeFile(file);
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

        var input = document.querySelector('input[name=tags]'),
            // init Tagify script on the above inputs
            tagify = new Tagify(input);


        $('body').on('change', "input[name=requester_type]", function () {
            let value = $(this).val();
            if (value == 'client')
            {
                $('#client-requester').removeClass('d-none');
                $('#employee-requester').addClass('d-none');
            } else {
                $('#client-requester').addClass('d-none');
                $('#employee-requester').removeClass('d-none');
            }
        });

        /* open add agent modal */
        $('body').on('click', '#add-agent', function () {
            var url = "{{ route('ticket-agents.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* open add agent modal */
        $('body').on('click', '#add-channel', function () {
            var url = "{{ route('ticketChannels.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* open add agent modal */
        $('body').on('click', '#add-type', function () {
            var url = "{{ route('ticketTypes.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#save-ticket-form').click(function () {
            var note = document.getElementById('description').children[0].innerHTML;
            document.getElementById('description-text').value = note;
            var mention_user_id = $('#description span[data-id]').map(function(){
                            return $(this).attr('data-id')
                        }).get();
            $('#mentionUserId').val(mention_user_id.join(','));

            const url = "{{ route('tickets.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-ticket-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-ticket-form",
                data: $('#save-ticket-data-form').serialize(),
                success: function (response) {
                    if (response.status == 'success') {
                        if (ticketDropzone.getQueuedFiles().length > 0) {
                            $('#replyID').val(response.replyID);
                            ticketDropzone.processQueue();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }

                }
            });
        });

        $('#create_task_category').click(function () {
            const url = "{{ route('taskCategory.create') }}";
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

        $('#set_time_estimate').change(function () {
            $('#set-time-estimate-fields').toggleClass('d-none');
        });

        $('#repeat-task').change(function () {
            $('#repeat-fields').toggleClass('d-none');
        });

        $('#dependent-task').change(function () {
            $('#dependent-fields').toggleClass('d-none');
        });

        $('.toggle-other-details').click(function () {
            $(this).find('svg').toggleClass('fa-chevron-down fa-chevron-up');
            $('#other-details').toggleClass('d-none');
        });

        $('#createTaskLabel').click(function () {
            const url = "{{ route('task-label.create') }}";
            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        $('#add-project').click(function () {
            $(MODAL_XL).modal('show');

            const url = "{{ route('projects.create') }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: MODAL_XL,
                success: function (response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        $('#add-employee').click(function () {
            $(MODAL_XL).modal('show');

            const url = "{{ route('employees.create') }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: MODAL_XL,
                success: function (response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        $('#manage-groups').click(function() {
            var url = "{{ route('ticket-groups.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        let isProjectModuleEnabled = {{ in_array('projects', user_modules()) ? 'true' : 'false' }};

        $('body').on('change', "input[name=requester_type], #client_id, #user_id", function () {
            if (isProjectModuleEnabled) {
                getProjects();
            }
        });

        function getProjects() {
            let requester_type = $("input[name=requester_type]:checked").val();
            if (!requester_type) {
                requester_type = $("input[name=requester_type]").val();
            }

            let client_id = $("#client_id").val();
            let user_id = $("#user_id").val();

            if ((requester_type == 'client' && client_id) || (requester_type == 'employee' && user_id)) {
                let url = "{{ route('get.projects') }}";
                $.easyAjax({
                    url: url,
                    type: "GET",
                    data: {
                        "requesterType": requester_type,
                        "clientId": client_id,
                        "userId": user_id
                    },
                    success: function(response) {
                        let options = [];
                        let rData = [];
                        rData = response.projects;
                        $.each(rData, function(index, value) {
                            let selectData = '';
                            selectData = '<option value="' + value.id + '">' + value.project_name + '</option>';
                            options.push(selectData);
                        });

                        $('#project_id').html('<option value="">--</option>' +
                            options);
                        $('#project_id').selectpicker('refresh');
                    }
                })
            } else {
                $('#project_id').html('<option value="">--</option>');
                $('#project_id').selectpicker('refresh');
            }

        }

        if (isProjectModuleEnabled) {
            getProjects();
        }

        init(RIGHT_MODAL);
    });
</script>
