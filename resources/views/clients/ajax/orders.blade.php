@php
$addOrderPermission = user()->permission('add_order');
@endphp
<!-- ROW START -->
<div class="row pb-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex" id="table-actions">

            @if (!in_array('client', user_roles()) && ($addOrderPermission == 'all' || $addOrderPermission == 'added'))
                <x-forms.link-primary :link="route('orders.create').'?client_id='.$client->id"
                    class="mr-3 openRightModal" icon="plus" data-redirect-url="{{ url()->full() }}">
                    @lang('app.addNewOrder')
                </x-forms.link-primary>
            @endif

        </div>
        <!-- Add Task Export Buttons End -->

        <form action="" id="filter-form">
            <div class="d-block d-lg-flex d-md-flex my-3">
                <!-- STATUS START -->
                <div class="select-box py-2 px-0 mr-3">
                    <x-forms.label :fieldLabel="__('app.status')" fieldId="status" />
                    <select class="form-control select-picker" name="status" id="status" data-live-search="true"
                        data-size="8">
                        <option value="all" data-content="@lang('app.all')">@lang('app.all')</option>

                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : ''}} data-content="<i class='fa fa-circle mr-2 text-yellow'></i> @lang('app.pending') ">@lang('app.pending')</option>

                        <option value="on-hold" {{ request('status') == 'on-hold' ? 'selected' : ''}} data-content="<i class='fa fa-circle mr-2 text-info'></i> @lang('app.on-hold') ">@lang('app.on-hold')</option>

                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : ''}} data-content="<i class='fa fa-circle mr-2 text-muted'></i> @lang('app.failed') ">@lang('app.failed')</option>

                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : ''}} data-content="<i class='fa fa-circle mr-2 text-blue'></i> @lang('app.processing') ">@lang('app.processing')</option>

                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : ''}} data-content="<i class='fa fa-circle mr-2 text-dark-green'></i> @lang('app.completed') ">@lang('app.completed')</option>

                        <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : ''}} data-content="<i class='fa fa-circle mr-2 text-red'></i> @lang('app.canceled') ">@lang('app.canceled')</option>

                        <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : ''}} data-content="<i class='fa fa-circle mr-2'></i> @lang('app.refunded') ">@lang('app.refunded')</option>
                    </select>
                </div>
                <!-- STATUS END -->

                <!-- SEARCH BY TASK START -->
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
                <!-- SEARCH BY TASK END -->

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

        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->

    </div>

</div>
<!-- ROW END -->
@include('sections.datatable_js')

<script>
    $('#orders-table').on('preXhr.dt', function(e, settings, data) {
        var clientID = "{{ $client->id }}";
        var status = $('#status').val();
        var searchText = $('#search-text-field').val();

        data['clientID'] = clientID;
        data['status'] = status;
        data['searchText'] = searchText;
    });
    const showTable = () => {
        window.LaravelDataTables["orders-table"].draw(true);
    }

    function changeOrderStatus(orderID, status) {
        var url = "{{ route('orders.change_status') }}";
        var token = "{{ csrf_token() }}";
        var id = orderID;
        var statusMessage = '';

        if (id != "" && status != "") {

            switch (status) {
                case 'pending':
                    statusMessage = "@lang('messages.orderStatus.pending')";
                    break;
                case 'on-hold':
                    statusMessage = "@lang('messages.orderStatus.onHold')";
                    break;
                case 'failed':
                    statusMessage = "@lang('messages.orderStatus.failed')";
                    break;
                case 'processing':
                    statusMessage = "@lang('messages.orderStatus.processing')";
                    break;
                case 'completed':
                    statusMessage = "@lang('messages.orderStatus.completed')";
                    break;
                case 'canceled':
                    statusMessage = "@lang('messages.orderStatus.canceled')";
                    break;
                case 'refunded':
                    statusMessage = "@lang('messages.orderStatus.refunded')";
                    break;

                default:
                    statusMessage = "@lang('messages.orderStatus.pending')";
                    break;
            }

            Swal.fire({
                title: "@lang('messages.confirmation.orderStatusChange')",
                text: statusMessage,
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('app.yes')",
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
                    $.easyAjax({
                        url: url,
                        type: "POST",
                        container: '.content-wrapper',
                        blockUI: true,
                        data: {
                            '_token': token,
                            orderId: id,
                            status: status,
                        },
                        success: function(data) {
                            showTable();
                        }
                    });
                }
                else {
                    showTable();
                }
            });

        }
    }


    $('#orders-table').on('change', '.order-status', function() {
        var id = $(this).data('order-id');
        var status = $(this).val();

        changeOrderStatus(id, status);
    });

    $('#orders-table').on('click', '.orderStatusChange', function() {
        var id = $(this).data('order-id');
        var status = $(this).data('status');

        changeOrderStatus(id, status);
    });

    $('#clientID, #status')
        .on('change keyup',
            function() {
                if ($('#clientID').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#status').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else {
                    $('#reset-filters').addClass('d-none');
                    showTable();
                }
            });

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }
        });

        $('#reset-filters,#reset-filters-2').click(function() {
            $('#filter-form')[0].reset();
            $('#filter-form #status').val('all');
            $('#filter-form .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('order-id');
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
                    var url = "{{ route('orders.destroy', ':id') }}";
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

</script>
