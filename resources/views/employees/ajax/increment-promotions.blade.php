<!-- TAB CONTENT START -->
<div class="tab-pane fade show active mt-5" role="tabpanel" aria-labelledby="nav-email-tab">
    @if (!in_array('payroll', user_modules()))
        <div class="alert alert-info" type="info"><i class="fa fa-info-circle mr-2"></i> @lang('modules.incrementPromotion.incrementAlert')</div>
    @endif
    @if ($manageIncrementPermission != 'none')
        <div class="d-flex justify-content-between action-bar mb-3">
            <x-forms.link-primary
                class="mr-3 float-left add-promotion"
                link="javascript:;"
                icon="plus">
                @lang('modules.incrementPromotion.addPromotion')
            </x-forms.link-primary>
        </div>
    @endif
    <x-cards.data class="mb-4" :title="__('modules.incrementPromotion.incrementPromotions')" padding="false"
        otherClasses="h-200 p-activity-detail cal-information">
        <!-- Timeline -->
        <div class="list-group lg-alt lg-even-black">
            @forelse ($careerProgress as $progress)
                <div class="list-group-item d-flex align-items-start">
                    <div class="mr-3">
                        <span class="badge badge-dark">{{ $loop->iteration }}</span>
                    </div>
                    <div class="media-body">
                        <div class="row">
                            <div class="col-md-10 mb-2">
                                <span class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($progress['date'])->translatedFormat($company->date_format) }}</span>
                                <span class="text-muted">
                                    @php
                                        $date = \Carbon\Carbon::parse($progress['data']->date);
                                    @endphp
                                    @if($progress['type'] === 'promotion' && isset($progress['data']->date))
                                        @if($date->isToday())
                                            (@lang('modules.incrementPromotion.promotion') @lang('app.fromToday'))
                                        @else
                                            (@lang('modules.incrementPromotion.promotion') {{ $date->diffForHumans() }})
                                        @endif
                                    @elseif($progress['type'] === 'increment' && isset($progress['data']->date) && module_enabled('Payroll') && in_array('payroll', user_modules()))
                                        @if($date->isToday())
                                            (@lang('modules.incrementPromotion.increment') @lang('app.fromToday'))
                                        @else
                                            (@lang('modules.incrementPromotion.increment') {{ $date->diffForHumans() }})
                                        @endif
                                    @endif
                                </span>
                            </div>

                            @if($progress['type'] === 'promotion' && $manageIncrementPermission == 'all')
                                <div class="col-md-2 text-right">
                                    <div class="dropdown">
                                        <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle"
                                            type="button" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                            aria-labelledby="dropdownMenuLink" tabindex="0">
                                            <a class="dropdown-item update-promotion" href="javascript:;" data-promotion-id="{{$progress['data']->id}}">
                                                <i class="fa fa-edit mr-1"></i> @lang('app.edit')
                                            </a>
                                            <a class="dropdown-item delete-promotion" href="javascript:;" data-promotion-id="{{$progress['data']->id}}">
                                                <i class="fa fa-trash mr-1"></i> @lang('app.delete')
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($progress['type'] === 'increment' && module_enabled('Payroll') &&  in_array('payroll', user_modules()))
                            <div class="mb-2">
                                <span class="badge badge-success">@lang('modules.incrementPromotion.increment')</span>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li>@lang('modules.incrementPromotion.salary'): {{ currency_format($progress['netSalary'], $currency, true) }} ({{ $progress['percentage'] }}% @lang('modules.incrementPromotion.increment'))</li>
                            </ul>
                        @elseif($progress['type'] === 'promotion')

                            <div class="mb-2">
                                <span class="badge badge-warning">@lang('modules.incrementPromotion.promotion')</span>
                            </div>

                            <ul class="list-unstyled mb-0">
                                <li class="mt-2">
                                    <span class="text-primary">{{ $progress['data']->previousDesignation->name }}</span>
                                    <i class="fa fa-arrow-right text-success mx-2"></i>
                                    <span class="text-success">{{ $progress['data']->currentDesignation->name }}</span>
                                    (@lang('app.designation'))
                                </li>
                                <li class="mt-2">
                                    @if ($progress['data']->previous_department_id != $progress['data']->current_department_id)
                                        <span class="text-primary">{{ $progress['data']->previousDepartment->team_name }}</span>
                                        <i class="fa fa-arrow-right text-success mx-2"></i>
                                        <span class="text-success">{{ $progress['data']->currentDepartment->team_name }}</span>
                                        (@lang('app.department'))
                                    @else
                                        (@lang('app.inSameDepartment'))
                                    @endif
                                </li>
                            </ul>
                        @endif
                    </div>
                </div>
            @empty
                <div class="list-group-item align-items-center">
                    <x-cards.no-record icon="map-marker-alt" :message="__('messages.noRecordFound')"/>
                </div>
            @endforelse
        </div>
        <!-- End Timeline -->
    </x-cards.data>
</div>
<!-- TAB CONTENT END -->

<script>
    // Add new emergency contact modal
    $('body').on('click', '.add-promotion', function () {
        var url = "{{ route('promotions.create') }}?user_id=" + "{{ $employee->id }}";

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '.update-promotion', function () {
        let id = $(this).data('promotion-id');

        var url = "{{ route('promotions.edit', [':id']) }}";
        url = url.replace(':id', id);

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '.delete-promotion', function () {

        let id = $(this).data('promotion-id');

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

                var url = "{{ route('promotions.destroy', ':id') }}";
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
                    success: function (response) {
                        if (response.status == "success") {
                            window.location.reload();
                        }
                    }
                });
            }
        });
    });

</script>
