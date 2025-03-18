@php
$editPermission = user()->permission('edit_appreciation');
$deletePermission = user()->permission('delete_appreciation');
@endphp
<div id="notice-detail-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey  justify-content-between p-20">
                    <div class="row">
                        <div class="col-lg-10 col-10">
                            <h3 class="heading-h1 mb-3">@lang('app.appreciationDetails')</h3>
                        </div>
                        <div class="col-lg-2 col-2 text-right">

                            @if ($editPermission == 'all' ||
                                ($editPermission == 'added' && $appreciation->added_by == user()->id) ||
                                ($editPermission == 'owned' && $appreciation->added_by == user()->id))
                                <div class="dropdown">
                                    <button
                                        class="btn btn-lg f-14 px-2 py-1 text-dark-grey  rounded  dropdown-toggle"
                                        type="button" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                        aria-labelledby="dropdownMenuLink" tabindex="0">

                                        @if ($editPermission == 'all' ||
                                            ($editPermission == 'added' && $appreciation->added_by == user()->id) ||
                                            $editPermission == 'owned')
                                            <a class="dropdown-item openRightModal"
                                                href="{{ route('appreciations.edit', $appreciation->id) }}">@lang('app.edit')</a>
                                        @endif
                                        @if ($deletePermission == 'all' ||
                                            ($deletePermission == 'added' && $appreciation->added_by == user()->id) ||
                                            $deletePermission == 'owned')
                                            <a class="dropdown-item delete-appre">@lang('app.delete')</a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">@lang('modules.appreciations.appreciationType')</p>
                        <div class="mb-0 text-dark-grey f-14 w-70 text-wrap ql-editor p-0">
                            @if(isset($appreciation->award->awardIcon->icon))
                                <x-award-icon :award="$appreciation->award" />
                            @endif

                        </div>
                    </div>

                    <x-cards.data-row :label="__('app.awardDate')" :value="$appreciation->award_date->translatedFormat(company()->date_format)" />
                    <x-cards.data-row :label="__('app.summary')" :value="!empty($appreciation->summary) ? $appreciation->summary : '--'" html="true" />

                    <div class="col-12 px-0 pb-3 d-flex">
                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                            @lang('modules.appreciations.awardTo')</p>
                        <x-employee :user="$appreciation->awardTo" />
                    </div>
                    @if ($appreciation->image_url)
                        <div class="col-12 px-0 pb-3 d-flex">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                @lang('modules.appreciations.photo')</p>
                            <img src="{{ $appreciation->image_url }}" class="logo mw-250 img-thumbnail" />
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').on('click', '.delete-appre', function() {
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
                var url = "{{ route('appreciations.destroy', $appreciation->id) }}";
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
                            window.location.href = response.redirectUrl;
                        }
                    }
                });
            }
        });
    });
</script>
