<div class="col-xl-12 col-lg-12 col-md-12 w-100 p-20">
    <div class="table-responsive">
        <x-table class="table-bordered">
            <x-slot name="thead">
                <th>#</th>
                <th width="20%">@lang('app.qrCode')</th>
                <th width="20%">@lang('app.menu.method')</th>
                <th width="30%">@lang('app.description')</th>
                <th class="text-right">@lang('app.action')</th>
            </x-slot>
            @forelse($payments as $method)
                <tr class="row{{ $method->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>@if($method->image) <img src="{{$method->image_url}}" height="100px" width="100px">@else - @endif</td>
                    <td>{{ $method->title }}</td>
                    <td class="text-break">{!! nl2br($method->payment_details) !!} </td>

                    <td class="text-right">
                        <div class="task_view">
                            <a href="javascript:;" data-payment-id="{{ $method->id }}"
                               class="task_view_more d-flex align-items-center justify-content-center edit-payment"
                               {{-- data-toggle="tooltip" --}}
                               data-original-title="@lang('app.edit')">
                                <i class="fa fa-edit icons"></i>
                            </a>
                        </div>
                        <div class="task_view">
                            <a href="javascript:;" data-payment-id="{{ $method->id }}"
                               class="task_view_more d-flex align-items-center justify-content-center delete-payment"
                               {{-- data-toggle="tooltip" --}}
                               data-original-title="@lang('app.delete')">
                                <i class="fa fa-trash icons"></i>
                            </a>

                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">
                        <x-cards.no-record icon="file" :message="__('messages.noPaymentAdded')" />
                    </td>
                </tr>
            @endforelse
        </x-table>
    </div>
</div>

<script>

    $('.edit-payment').click(function() {
        var paymentID = $(this).data('payment-id');
        var url = "{{ route('invoices-payment-details.edit', ':id') }}";
        url = url.replace(':id', paymentID);

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('.delete-payment').click(function() {
        var id = $(this).data('payment-id');
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
                var url = "{{ route('invoices-payment-details.destroy', ':id') }}";
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

    $('.set_default_unit').click(function() {
        var unitID = $(this).data('unit-id');
        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: "{{ route('unit-type.set_default') }}",
            type: "POST",
            data: {
                unitID: unitID,
                _token: token
            },
            blockUI: true,
            container: '#editSettings',
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        });
    });
</script>
