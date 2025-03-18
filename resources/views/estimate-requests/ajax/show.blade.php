<div class="row">
    <div class="col-sm-12">
        <x-cards.data :title="__('modules.estimateRequest.estimateRequest')" class="mt-4">
            <x-slot name="action">
                <div class="dropdown">
                    <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-h"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                         aria-labelledby="dropdownMenuLink" tabindex="0">
                        @if ($estimateRequest->status == 'pending' || $estimateRequest->status == 'in process')
                            @if (
                                $editEstimateRequestPermission == 'all'
                                || (($editEstimateRequestPermission == 'added') && $estimateRequest->added_by == user()->id)
                                || (($editEstimateRequestPermission == 'owned') && $estimateRequest->client_id == user()->id)
                                || (($editEstimateRequestPermission == 'both') && ($estimateRequest->client_id == user()->id || $estimateRequest->added_by == user()->id))
                            )
                                <a class="dropdown-item openRightModal"
                                    href="{{ route('estimate-request.edit', $estimateRequest->id) }}">@lang('app.edit')</a>
                            @endif
                            @if ($rejectEstimateRequestPermission == 'all')
                                <a class="dropdown-item change-status" href="javascript:;" data-estimate-request-id="{{ $estimateRequest->id }}">@lang('app.reject')</a>
                            @endif
                        @endif
                        @if ($estimateRequest->status != 'accepted')
                            @if ($addEstimatePermission == 'all' || $addEstimatePermission == 'added')
                                <a class="dropdown-item"
                                    href="{{ route('estimates.create', ['estimate-request' => $estimateRequest->id]) }}">@lang('app.create') @lang('app.estimate')</a>
                            @endif
                        @endif
                        @if (
                            $deleteEstimateRequestPermission == 'all'
                            || (($deleteEstimateRequestPermission == 'added') && $estimateRequest->added_by == user()->id)
                            || (($deleteEstimateRequestPermission == 'owned') && $estimateRequest->client_id == user()->id)
                            || (($deleteEstimateRequestPermission == 'both') && ($estimateRequest->client_id == user()->id || $estimateRequest->added_by == user()->id))
                        )
                            @if (!(in_array('client', user_roles()) && $estimateRequest->status == 'accepted'))
                                <a class="dropdown-item delete-table-row" href="javascript:;" data-estimate-request-id="{{ $estimateRequest->id }}">
                                    @lang('app.delete')
                                </a>
                            @endif
                        @endif

                    </div>
                </div>
            </x-slot>
            <x-cards.data-row :label="__('app.clientName')" :value=" $estimateRequest->client->name_salutation" />

            <x-cards.data-row :label="__('modules.estimateRequest.estimatedBudget')"
                :value="currency_format($estimateRequest->estimated_budget, $estimateRequest->currency_id)" />

            <x-cards.data-row :label="__('app.project')"
                :value="$estimateRequest->project ? $estimateRequest->project->project_name : '--'" />

            <x-cards.data-row :label="__('app.estimate')" :html="true" :value="$estimateLink" />
            <x-cards.data-row :label="__('modules.estimateRequest.earlyRequest')"
                :value="$estimateRequest->early_requirement ?? '--' " />
            @if (isset($estimateRequest->reason) && !empty($estimateRequest->reason) && $estimateRequest->status == 'rejected')
                <x-cards.data-row :label="__('app.reason')" :value="$estimateRequest->reason" />
            @endif

            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                    @lang('app.status')</p>
                <p class="mb-0 text-dark-grey f-14 w-70">
                    @if ($estimateRequest->status == 'accepted')
                        <i class="fa fa-circle mr-1 text-dark-green f-10"></i>
                    @elseif ($estimateRequest->status == 'pending')
                        <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                    @elseif ($estimateRequest->status == 'in process')
                        <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                        @lang('app.pending')
                    @else
                        <i class="fa fa-circle mr-1 text-red f-10"></i>
                    @endif
                    @if ($estimateRequest->status != 'in process')
                        @lang('app.'. $estimateRequest->status)
                    @endif
                </p>
            </div>

            <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                <p class="mb-0 text-lightest f-14 w-30 ">@lang('app.description')</p>
                <div class="mb-0 text-dark-grey f-14 w-70 text-wrap ql-editor2 p-0">{!! nl2br($estimateRequest->description) !!}</div>
            </div>
        </x-cards.data>
    </div>
</div>

<script>
    $('body').on('click', '.delete-table-row', function() {
        var id = $(this).data('estimate-request-id');
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
                var url = "{{ route('estimate-request.destroy', ':id') }}";
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
                            location.href = "{{ route('estimate-request.index') }}";
                            showTable();
                        }
                    }
                });
            }
        });
    });
    $('body').on('click', '.change-status', function() {
        var url = "{{ route('estimate-request.confirm_rejected', ':id') }}";
        var id = $(this).data('estimate-request-id');
        url = url.replace(':id', id);
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });
</script>
