
@php
    $addInvoicePermission = user()->permission('add_lead_proposals');
    $addLeadPermission = user()->permission('add_deals');
@endphp

<!-- ROW START -->
<div class="row">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-grid d-lg-flex d-md-flex action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addLeadPermission == 'all' || $addLeadPermission == 'added')
                <x-forms.link-primary :link="route('deals.create').'?contact_id='.$leadContact->id" class="mr-3 float-left mb-2 mb-lg-0 mb-md-0 openRightModal" icon="plus" data-redirect-url="{{ url()->full() }}">
                    @lang('modules.deal.addDeal')
                </x-forms.link-primary>
            @endif
                {{-- @if ($addLeadPermission == 'all' || $addLeadPermission == 'added')
                    <x-forms.link-secondary :link="route('deals.import')" class="mr-3 openRightModal float-left mb-2 mb-lg-0 mb-md-0 d-none d-lg-block" icon="file-upload">
                        @lang('app.importExcel')
                    </x-forms.link-secondary>
                @endif --}}
            </div>
        </div>
        <div class="d-flex justify-content-between">
            <form action="" class="flex-grow-1 " id="filter-form">
                <div class="d-flex mt-3">
                    <!-- PIPELINE START -->
                    <div class="select-box py-2 px-0 mr-3">
                        <x-forms.label :fieldLabel="__('modules.deal.pipeline')" fieldId="pipeline" />
                        <div class="select-status">
                            <select class="form-control select-picker pipelineFilter" name="pipeline" id="pipeline">
                                @foreach($pipelines as $pipeline)
                                    <option @if($pipeline->default == 1) selected @endif value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!-- PIPELINE END -->

                    <!-- SEARCH BY DEAL START -->
                    <div class="select-box py-2 px-lg-2 px-md-2 px-0 mr-3">
                        <x-forms.label fieldId="status" />
                        <div class="input-group bg-grey rounded">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-additional-grey">
                                    <i class="fa fa-search f-13 text-dark-grey"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control f-14 p-1 height-35 border" id="search-text-field"
                                placeholder="@lang('app.startTyping')">
                        </div>
                    </div>
                    <!-- SEARCH BY DEAL END -->

                    <!-- RESET START -->
                    <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 mt-4">
                        <x-forms.button-secondary class="btn-xs d-none height-35 mt-2" id="reset-filters"
                            icon="times-circle">
                            @lang('app.clearFilters')
                        </x-forms.button-secondary>
                    </div>
                    <!-- RESET END -->
                </div>
            </form>

            <x-datatable.actions class="mt-5">
                <!-- QUICK ACTION START -->
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.deal.changeStage')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>

                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" id="change-stage-action" class="form-control select-picker">
                        @foreach ($stages as $st)
                            <option data-content="<i class='fa fa-circle' style='color:{{ $st->label_color }}'></i> {{ $st->name }} " value="{{ $st->id}}"> {{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
                 <!-- QUICK ACTION END -->
            </x-datatable.actions>
        </div>
        <!-- Add DEAL Export Buttons End -->
        <!-- DEAL Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- DEAL Box End -->

    </div>

</div>
<!-- ROW END -->
@include('sections.datatable_js')

<script>
    $('#leads-table').on('preXhr.dt', function(e, settings, data) {

        var leadId = "{{ $leadContact->id }}";
        var pipeline = $('#pipeline').val();
        data['leadId'] = leadId;
        data['pipeline'] = pipeline;
        var searchText = $('#search-text-field').val();
        data['searchText'] = searchText;
    });
    const showTable = () => {
        window.LaravelDataTables["leads-table"].draw(true);
    }

    $('#pipeline')
        .on('change keyup',
            function() {
                getStages ()
                showTable();
            });

    $('#search-text-field').on('keyup', function() {
        if ($('#search-text-field').val() != "") {
            $('#reset-filters').removeClass('d-none');
            showTable();
        }
    });

    function getStages () {
        var pipelineId = $('#pipeline').val();
        var url = "{{ route('deals.get-stage', ':id') }}";
        url = url.replace(':id', pipelineId);

        $.easyAjax({
            url: url,
            type: "GET",
            success: function (response) {
                if (response.status == 'success') {
                    var options = [];
                    var rData = [];
                    rData = response.data;
                    $.each(rData, function (index, value) {
                        var selectData = '';
                        var contect = `data-content="<i class='fa fa-circle' style='color:`+value.label_color+`'></i> `+value.name+`"`;
                        selectData = `<option `+contect+` value="` + value.id + `">' + value
                            .name + '</option>`;
                        options.push(selectData);
                    });

                    $('#change-stage-action').html(options);
                    $('#change-stage-action').selectpicker('refresh');
                }
            }
        })
    }

    $('#reset-filters,#reset-filters-2').click(function() {
        $('#filter-form')[0].reset();
        $('#filter-form #status').val('not finished');
        $('#filter-form .select-picker').selectpicker("refresh");
        $('#reset-filters').addClass('d-none');
        showTable();
    });

    $('body').on('click', '.delete-table-row', function() {
        var id = $(this).data('proposal-id');
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
                            showTable();
                        }
                    }
                });
            }
        });
    });

    $('#quick-action-type').change(function() {
            const actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');

                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else if (actionValue == 'change-agent') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-agent-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        $('#quick-action-apply').click(function() {
            const actionValue = $('#quick-action-type').val();
            if (actionValue == 'delete') {
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
                        applyQuickAction();
                    }
                });

            } else {
                applyQuickAction();
            }
        });

        const applyQuickAction = () => {
            var rowdIds = $("#leads-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('deals.apply_quick_action') }}?row_ids=" + rowdIds;

            $.easyAjax({
                url: url,
                container: '#quick-action-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#quick-action-apply",
                data: $('#quick-action-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        showTable();
                        resetActionButtons();
                        deSelectAll();
                        $('#quick-action-form').hide();
                    }
                }
            })
        };

        function changeStage(leadID, elem) {
            var statusID = $(elem).find(':selected').attr('data-id');
            var statusSlug = $(elem).find(':selected').attr('data-slug');

            var url = "{{ route('deals.change_stage') }}";
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                type: 'POST',
                url: url,
                data: {
                    '_token': token,
                    'leadID': leadID,
                    'statusID': statusID
                },
                success: function(response) {
                    if (response.status == "success") {

                        if (statusSlug === 'win' || statusSlug === 'lost') {
                            var modalUrl = "{{ route('deals.stage_change', ':id')}}?via=deal&leadID=" + leadID + "&statusID=" + statusID;
                            modalUrl = modalUrl.replace(':id', leadID);
                            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                            $.ajaxModal(MODAL_LG, modalUrl);
                            return;
                        }
                        $.easyBlockUI('#leads-table');
                        $.easyUnblockUI('#leads-table');
                        showTable();
                        resetActionButtons();
                        deSelectAll();
                    }
                }
            });
        }

        function followUp(leadID) {
            var url = '{{ route('deals.follow_up', ':id') }}';
            url = url.replace(':id', leadID);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        }


</script>
