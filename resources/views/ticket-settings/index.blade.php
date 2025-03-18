@extends('layouts.app')

@php
    $manageGroupPermission = user()->permission('manage_ticket_groups');
@endphp
@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-6 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">

                            <a class="nav-item nav-link f-15 active agent" href="{{ route('ticket-settings.index') }}"
                                role="tab" aria-controls="nav-ticketAgents"
                                aria-selected="true">@lang('app.menu.ticketAgents')
                            </a>

                            @if($manageGroupPermission == 'all')
                                <a class="nav-item nav-link f-15 group-manage"
                                    href="{{ route('ticket-settings.index') }}?tab=group-manage" role="tab"
                                    aria-controls="nav-groupManage" aria-selected="true">@lang('app.menu.groupManage')
                                </a>
                            @endif

                            <a class="nav-item nav-link f-15 type" href="{{ route('ticket-settings.index') }}?tab=type"
                                role="tab" aria-controls="nav-ticketTypes"
                                aria-selected="true">@lang('app.menu.ticketTypes')
                            </a>

                            <a class="nav-item nav-link f-15 channel"
                                href="{{ route('ticket-settings.index') }}?tab=channel" role="tab"
                                aria-controls="nav-ticketChannel" aria-selected="true">@lang('app.menu.ticketChannel')
                            </a>

                            <a class="nav-item nav-link f-15 reply-template"
                                href="{{ route('ticket-settings.index') }}?tab=reply-template" role="tab"
                                aria-controls="nav-replyTemplates" aria-selected="true">@lang('app.menu.replyTemplates')
                            </a>

                            <a class="nav-item nav-link f-15 method"
                                href="{{ route('ticket-settings.index') }}?tab=round-robin" role="tab"
                                aria-controls="nav-leadAgent" aria-selected="true">@lang('modules.deal.dealMethod')
                            </a>

                            <a class="nav-item nav-link f-15 ticket-agent-setting"
                                href="{{ route('ticket-settings.index') }}?tab=ticket-agent-setting" role="tab"
                                aria-controls="nav-ticketSetting" aria-selected="true">@lang('modules.deal.ticketVisibilitySetting')
                            </a>
{{--
                            <a class="nav-item nav-link f-15 email-sync"
                                href="{{ route('ticket-settings.index') }}?tab=email-sync" role="tab"
                                aria-controls="nav-emailSync" aria-selected="true">@lang('app.menu.emailSync')
                            </a> --}}

                        </div>
                    </nav>
                </div>
            </x-slot>

            <x-slot name="buttons">
                <div class="row">

                    <div class="col-md-12 mb-2">
                        <x-forms.button-primary icon="plus" id="addTicketType" class="type-btn mb-2 d-none actionBtn">
                            @lang('app.addNewTicketType')
                        </x-forms.button-primary>

                        <x-forms.button-primary icon="plus" id="addAgent" class="agent-btn mb-2 d-none actionBtn">
                            @lang('app.addNewAgents')
                        </x-forms.button-primary>

                        <x-forms.button-primary icon="plus" id="addChannel" class="channel-btn mb-2 d-none actionBtn">
                            @lang('app.addNewTicketChannel')
                        </x-forms.button-primary>

                        <x-forms.button-primary icon="plus" id="addReplyTemplate"
                            class="reply-template-btn mb-2 d-none actionBtn">
                            @lang('modules.projectTemplate.addNewTemplate')
                        </x-forms.button-primary>

                        <x-forms.button-primary icon="plus" id="addGroup"
                            class="group-manage-btn mb-2 d-none actionBtn">
                            @lang('app.addNewGroup')
                        </x-forms.button-primary>
                    </div>

                </div>
            </x-slot>

            {{-- include tabs here --}}
            @include($view)

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')
    <script>
        /* manage menu active class */
        $('.nav-item').removeClass('active');
        const activeTab = "{{ $activeTab }}";
        $('.' + activeTab).addClass('active');

        showBtn(activeTab);

        function showBtn(activeTab) {
            $('.actionBtn').addClass('d-none');
            $('.' + activeTab + '-btn').removeClass('d-none');
        }

        $(document).on('show.bs.dropdown', '.table-responsive', function() {
            $('.table-responsive').css( "overflow", "inherit" );
        });

       $("body").on("click", "#editSettings .nav a", function(event) {
            event.preventDefault();

            $('.nav-item').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: "#nav-tabContent",
                historyPush: true,
                success: function(response) {
                    if (response.status == "success") {
                        showBtn(response.activeTab);

                        $('#nav-tabContent').html(response.html);
                        init('#nav-tabContent');
                    }
                }
            });
        });

        /* delete agent */
        $('body').on('click', '.delete-agents', function() {
            var id = $(this).data('agent-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.removeAgentText')",
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
                    var url = "{{ route('ticket-agents.destroy', ':id') }}";
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
                                $('.row' + id).fadeOut(100);
                            }
                        }
                    });
                }
            });
        });

        /* change agent status */
        $('body').on('change', '.change-agent-status', function() {

            var agentId = $(this).data('agent-id');
            var status = $(this).val();

            var token = '{{ csrf_token() }}';
            var url = "{{ route('ticket-agents.update', ':id') }}";
            url = url.replace(':id', agentId);

            if (typeof agentId !== 'undefined') {
                $.easyAjax({
                    type: 'PUT',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': token,
                        'status': status
                    }
                });
            }
        });


        $('body').on('click', '.delete-type', function() {
            var id = $(this).data('type-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.removeTicketText')",
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
                    var url = "{{ route('ticketTypes.destroy', ':id') }}";
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
                                $('.row' + id).fadeOut(100);
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '.edit-type', function() {
            var typeId = $(this).data('type-id');
            var url = "{{ route('ticketTypes.edit', ':id') }}";
            url = url.replace(':id', typeId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '.delete-channel', function() {
            var id = $(this).data('channel-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.removeChannelText')",
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
                    var url = "{{ route('ticketChannels.destroy', ':id') }}";
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
                                $('.row' + id).fadeOut(100);
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '.edit-channel', function() {
            var typeId = $(this).data('channel-id');
            var url = "{{ route('ticketChannels.edit', ':id') }}";
            url = url.replace(':id', typeId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* open edit reply template modal */
        $('body').on('click', '.edit-template', function() {
            var templateId = $(this).data('template-id');
            var url = "{{ route('replyTemplates.edit', ':id') }}";
            url = url.replace(':id', templateId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* delete template */
        $('body').on('click', '.delete-template', function() {
            var id = $(this).data('template-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.removeTemplateText')",
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
                    var url = "{{ route('replyTemplates.destroy', ':id') }}";
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
                                $('.row' + id).fadeOut(100);
                            }
                        }
                    });
                }
            });
        });

        /* open add agent modal */
        $('body').on('click', '#addAgent', function() {
            var url = "{{ route('ticket-agents.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* open add agent modal */
        $('body').on('click', '#addChannel', function() {
            var url = "{{ route('ticketChannels.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* open creat reply template modal */
        $('body').on('click', '#addReplyTemplate', function() {
            var url = "{{ route('replyTemplates.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* open add agent modal */
        $('body').on('click', '#addTicketType', function() {
            var url = "{{ route('ticketTypes.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* open add group modal */
        $('body').on('click', '#addGroup', function() {
            var url = "{{ route('ticket-groups.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* change agent group */
        $(".change-agent-group").selectpicker({
            multipleSeparator: ", ",
            selectedTextFormat: "count > 6",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.groupSelected') }} ";
            }
        });
        $('.change-agent-group').change(function() {

            var agentId = $(this).data('agent-id');
            var groupId = $(this).val();
            var token = '{{ csrf_token() }}';
            var url = "{{ route('ticket_agents.update_group', ':id') }}";
            url = url.replace(':id', agentId);

            $.easyAjax({
                type: 'POST',
                url: url,
                blockUI: true,
                data: {
                    '_token': token,
                    'groupId': groupId
                }
            });
        });

        $('body').on('click', '.delete-group', function() {
            var id = $(this).data('group-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.removeGroupText')",
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
                    var url = "{{ route('ticket-groups.destroy', ':id') }}";
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
                                $('.row' + id).fadeOut(100);
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '.edit-group', function() {
            var groupId = $(this).data('group-id');
            var url = "{{ route('ticket-groups.edit', ':id') }}";
            url = url.replace(':id', groupId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

    </script>
@endpush
