@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <!-- FILTER START -->
    <div class="d-flex filter-box project-header bg-white">

        <div class="mobile-close-overlay w-100 h-100" id="close-client-overlay"></div>
        <div class="project-menu d-lg-flex" id="mob-client-detail">
            <a class="d-none close-it" href="javascript:;" id="close-client-detail">
                <i class="fa fa-times"></i>
            </a>
            <x-tab :href="route('lead-report.profile') . '?tab=profile'" :text="__('modules.deal.profile')" class="profile" />
            <x-tab :href="route('lead-report.chart') . '?tab=chart'" :text="__('modules.leadContact.leadReport')" class="files active-tab" ajax="false" />
        </div>
        <a class="mb-0 d-block d-lg-none text-dark-grey ml-auto mr-2 border-left-grey"
            onclick="openClientDetailSidebar()"><i class="fa fa-ellipsis-v "></i></a>
    </div>

    @if ($activeTab == 'profile')
        <x-filters.filter-box>
            <!-- DATE START -->
            <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
                <div class="select-status d-flex">
                    <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                        id="datatableRange2" placeholder="@lang('placeholders.dateRange')">
                </div>
            </div>

            <!-- DATE END -->

            <!-- CLIENT START -->
            <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.leadAgent')</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="agent" id="agent" data-live-search="true"
                        data-size="8">
                        <option value="all">@lang('app.all')</option>
                    @php  $uniqueAgents = $agents->unique('user_id'); @endphp


                        @foreach ($uniqueAgents as $agent) {
                        <x-user-option :user="$agent->user" />

                        }
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- CLIENT END -->

            <!-- RESET START -->
            <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
                <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                    @lang('app.clearFilters')
                </x-forms.button-secondary>
            </div>
            <!-- RESET END -->

        </x-filters.filter-box>
    @endif

    <!-- FILTER END -->
@endsection

@section('content')
    <div class="content-wrapper border-top-0 client-detail-wrapper">
        @include($view)
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>

    @include('sections.datatable_js')

    <script>
        $("body").on("click", ".project-menu .ajax-tab", function(event) {
            event.preventDefault();

            $('.project-menu .p-sub-menu').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: ".content-wrapper",
                historyPush: true,
                success: function(response) {
                    if (response.status == "success") {
                        $('.content-wrapper').html(response.html);
                        init('.content-wrapper');
                    }
                }
            });
        });

        const activeTab = "{{ $activeTab }}";
        $('.project-menu .' + activeTab).addClass('active');

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
                                window.location.href = "{{ route('deals.index') }}";
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush
