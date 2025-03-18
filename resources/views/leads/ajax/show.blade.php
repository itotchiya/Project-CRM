@php
$viewClientNote = user()->permission('view_deal_note');
$viewProposalPermission = user()->permission('view_lead_proposals');
$viewLeadFilePermission = user()->permission('view_lead_files');
$viewLeadFollowupPermission = user()->permission('view_lead_follow_up');

@endphp
 
<div id="task-detail-section">

    <h3 class="heading-h1 mb-3">{{ $deal->name }}</h3>

    <div class="row">
        <!--  USER CARDS START -->
        <div class="col-sm-9 mb-4 mb-xl-0 mb-lg-4 mb-md-0">

            <x-cards.data :title="__('modules.deal.dealInfo')">

                <x-slot name="action">
                    <div class="dropdown">
                        <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                aria-labelledby="dropdownMenuLink" tabindex="0">
                            <a class="dropdown-item openRightModal"
                                href="{{ route('deals.edit', $deal->id).'?tab=overview' }}">@lang('app.edit')</a>
                            @if (
                                $deleteLeadPermission == 'all'
                                || ($deleteLeadPermission == 'added' && user()->id == $deal->added_by)
                                || ($deleteLeadPermission == 'owned' && ((!is_null($deal->agent_id) && user()->id == $deal->leadAgent->user->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)))
                                || ($deleteLeadPermission == 'both' &&  (((!is_null($deal->agent_id) && user()->id == $deal->leadAgent->user->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)) || user()->id == $deal->added_by))
                            )
                                <a class="dropdown-item delete-table-row" href="javascript:;" data-id="{{ $deal->id }}">
                                    @lang('app.delete')
                                </a>
                            @endif

                        </div>
                    </div>
                </x-slot>

                <p class="f-w-500">
                    <x-status style="color: {{ $deal->pipeline->label_color }}" color="yellow"
                                :value="$deal->pipeline->name"/>
                    <i class="bi bi-arrow-right mx-"></i>
                    <x-status style="color: {{ $deal->leadStage->label_color }}" color="yellow"
                                :value="$deal->leadStage->name"/>
                </p>
                <x-cards.data-row :label="__('modules.deal.dealName')" :value="$deal->name ?? '--'"/>



                <x-cards.data-row :label="__('modules.leadContact.leadContact')"
                                    :value="$deal->contact->client_name_salutation ?? '--'"/>

                <x-cards.data-row :label="__('app.email')" :value="$deal->contact->client_email ?? '--'"/>

                <x-cards.data-row :label="__('modules.lead.companyName')"
                                    :value="!empty($deal->contact->company_name) ? $deal->contact->company_name : '--'"/>

                <div class="col-12 px-0 pb-3 d-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                        @lang('modules.deal.dealAgent')</p>
                    <p class="mb-0 text-dark-grey f-14">
                        @if (!is_null($deal->leadAgent))
                            <x-employee :user="$deal->leadAgent->user"/>
                        @else
                            --
                        @endif
                    </p>
                </div>

                <div class="col-12 px-0 pb-3 d-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">{{ __('app.dealWatcher') }}</p>
                    <p class="mb-0 text-dark-grey f-14">
                        @if (!is_null($deal->dealWatcher))
                            <x-employee :user="$deal->dealWatcher"/>
                        @else
                            --
                        @endif
                    </p>
                </div>

                @if ($deal->leadStatus)
                    <div class="col-12 px-0 pb-3 d-flex">
                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">@lang('app.status')</p>
                        <p class="mb-0 text-dark-grey f-14">
                            <x-status :value="$deal->leadStatus->type"
                                        :style="'color:'.$deal->leadStatus->label_color"/>
                        </p>

                    </div>
                @endif

                <x-cards.data-row :label="__('modules.deal.closeDate')"
                                    :value="($deal->close_date) ? $deal->close_date->translatedFormat(company()->date_format) : '--'"/>
                <x-cards.data-row :label="__('modules.deal.dealValue')"
                                    :value="($deal->value) ? currency_format($deal->value, $deal->currency_id) : '--'"/>

                <x-cards.data-row :label="__('modules.lead.products')"
                                    :value="($productNames) ? implode(', ' , $productNames) : '--'"/>

                {{-- Custom fields data --}}
                <x-forms.custom-field-show :fields="$fields" :model="$deal"></x-forms.custom-field-show>

            </x-cards.data>

            <div class="bg-additional-grey rounded my-3">
                <div class="s-b-inner s-b-notifications bg-white b-shadow-4 rounded">
                    <x-tab-section class="deal-tabs">
                        @if($viewLeadFilePermission != 'none')
                            <x-tab-item class="ajax-tab files" :active="(request('tab') === 'files' || !request('tab'))"
                                            :link="route('deals.show', $deal->id).'?tab=files'">@lang('modules.lead.file')</x-tab-item>
                        @endif
                        @if($viewLeadFollowupPermission != 'none')
                            <x-tab-item class="ajax-tab follow-up" :active="request('tab') === 'follow-up'"
                                            :link="route('deals.show', $deal->id).'?tab=follow-up'">@lang('modules.lead.followUp')</x-tab-item>
                        @endif


                        @if($viewProposalPermission != 'none')
                            <x-tab-item class="ajax-tab proposals" :active="request('tab') === 'proposals'"
                                            :link="route('deals.show', $deal->id).'?tab=proposals'">@lang('modules.lead.proposal')</x-tab-item>
                        @endif

                        @if ($viewClientNote != 'none')
                            <x-tab-item class="ajax-tab notes" :active="request('tab') === 'notes'"
                                            :link="route('deals.show', $deal->id).'?tab=notes'">@lang('app.notes')</x-tab-item>
                        @endif

                        @if ($gdpr->enable_gdpr)
                            <x-tab-item class="ajax-tab gdpr" :active="request('tab') === 'gdpr'"
                                        :link="route('deals.show', $deal->id).'?tab=gdpr'">@lang('app.menu.gdpr')</x-tab-item>
                        @endif

                        <x-tab-item class="ajax-tab history" :active="request('tab') === 'history'"
                                    :link="route('deals.show', $deal->id).'?tab=history'">@lang('modules.tasks.history')</x-tab-item>

                    </x-tab-section>

                    <div class="s-b-n-content">
                        <div class="tab-content" id="nav-tabContent">
                            @include($tab)
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--  USER CARDS END -->

        <div class="col-sm-3">


            <x-cards.data :title="__('modules.leadContact.leadDetails')">

                <x-cards.data-row :label="__('modules.leadContact.leadContact')" otherClasses="pr-1" labelClasses="pr-1"
                                    value="<a href='{{ route('lead-contact.show', $deal->contact->id) }}' class='text-darkest-grey'> {{ $deal->contact->client_name_salutation }}</a>"/>

                <x-cards.data-row :label="__('app.email')" :value="$deal->contact->client_email ?? '--'" otherClasses="pr-1" labelClasses="pr-1"/>
                <x-cards.data-row :label="__('modules.lead.mobile')" :value="$deal->contact->mobile ?? '--'" otherClasses="pr-1" labelClasses="pr-1"/>

                <x-cards.data-row :label="__('modules.lead.companyName')"
                                    :value="!empty($deal->contact->company_name) ? $deal->contact->company_name : '--'" otherClasses="pr-1" labelClasses="pr-1"/>

                <div class="d-flex">
                    @if ($deal->contact->client_email)
                        <x-forms.link-secondary class="mr-3 pr-1" link='mailto:{{ $deal->contact->client_email }}'
                                                icon="envelope">@lang('app.email')</x-forms.link-secondary>
                    @endif

                    @if ($deal->contact->mobile )
                        <x-forms.button-secondary class="btn-copy pr-1" data-clipboard-text="{{ $deal->contact->mobile }}"
                                                    icon="phone">@lang('app.mobile')</x-forms.button-secondary>
                    @endif
                </div>

            </x-cards.data>
        </div>
    </div>

    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>

    <script>
        var clipboard = new ClipboardJS('.btn-copy');

        clipboard.on('success', function (e) {
            Swal.fire({
                icon: 'success',
                text: '@lang("app.phoneCopied")',
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    confirmButton: 'btn btn-primary',
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
            })
        });
    </script>

    <script>
        $(document).ready(function () {
            $(".ajax-tab").click(function (event) {
                event.preventDefault();

                $('.deal-tabs .ajax-tab').removeClass('active');
                $(this).addClass('active');

                const requestUrl = this.href;

                $.easyAjax({
                    url: requestUrl,
                    blockUI: true,
                    container: "#nav-tabContent",
                    historyPush: ($(RIGHT_MODAL).hasClass('in') ? false : true),
                    data: {
                        'json': true
                    },
                    success: function (response) {
                        if (response.status == "success") {
                            $('#nav-tabContent').html(response.html);
                        }
                    }
                });
            });

        });
    </script>

    <script>
        var fileLayout = 'thumbnail-list';
        function leadFilesView(layout) {
            $('#layout').html('');
            var leadID = "{{ $deal->id }}";
            fileLayout = layout;
            $.easyAjax({
                type: 'GET',
                url: "{{ route('deal-files.layout') }}",
                disableButton: true,
                blockUI: true,
                data: {
                    id: leadID,
                    layout: layout
                },
                success: function(response) {
                    $('#layout').html(response.html);
                    if (layout == 'gridview') {
                        $('#list-tabs').removeClass('btn-active');
                        $('#thumbnail').addClass('btn-active');
                    } else {
                        $('#list-tabs').addClass('btn-active');
                        $('#thumbnail').removeClass('btn-active');
                    }
                }
            });
        }

        // File tab script start
        $('body').on('click', '.delete-lead-file', function() {
            var id = $(this).data('file-id');
            var deleteView = $(this).data('pk');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.removeFileText')",
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
                    var url = "{{ route('deal-files.destroy', ':id') }}";
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
                                leadFilesView(fileLayout);
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '#add-files', function() {
            const url = "{{ route('deal-files.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });
        // File tab script end

        // Follow up tab script start
        $('body').on('click', '#add-lead-followup', function() {
            const url = "{{ route('deals.follow_up', $deal->id) }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        })

        $('body').on('click', '.edit-table-row-lead', function() {
            var id = $(this).data('followup-id');
            var url = "{{ route('deals.follow_up_edit', ':id') }}";
            url = url.replace(':id', id);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '.delete-table-row-lead', function() {
            var id = $(this).data('followup-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
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
                    var url = "{{ route('deals.follow_up_delete', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                location.reload();
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
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
                    var url = "{{ route('deals.destroy', ':id') }}";
                    url = url.replace(':id', id);
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                let dealsIndexUrl = "{{ route('deals.index') }}";
                                window.location.href = dealsIndexUrl;
                            }
                        }
                    });
                }
            });
        });

        // Follow up tab script end

        // Notes tab script start

        $('body').on('click', '.delete-note-lead', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
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
                    var url = "{{ route('deal-notes.destroy', ':id') }}";
                    url = url.replace(':id', id);
                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });

        // Notes tab script end

        // Proposal tab script start

        $('body').on('click', '.delete-proposal-table-row', function() {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
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
                    var id = $(this).data('proposal-id');
                    var url = "{{ route('proposals.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '.sendButton', function() {
            var id = $(this).data('proposal-id');
            var url = "{{ route('proposals.send_proposal', ':id') }}";
            url = url.replace(':id', id);

            var token = "{{ csrf_token() }}";

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#invoices-table',
                blockUI: true,
                data: {
                    '_token': token
                },
                success: function(response) {
                    if (response.status == "success") {
                        window.location.reload();
                    }
                }
            });
        });

        // Proposal tab script end
    </script>
</div>
