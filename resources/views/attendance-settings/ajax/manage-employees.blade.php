<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.manageEmployee')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-table class="table-bordered" id="empRecordTable" headType="thead-light">
        <x-slot name="thead">
            <th>@lang('app.employee') </th>
            <th>@lang('app.rotationName')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse ($employees as $employee)
            <tr id="emp-{{ $employee->id }}">
                <td><x-employee :user="$employee" /></td>
                <td class="border-bottom-0 btrr-mbl btlr">
                    <div class="input-group">
                        <select name="rotation" id="rotation" class="form-control select-picker rotation"
                            data-live-search="true" data-emp-id="{{ $employee->id }}" search="true">
                            @foreach ($rotations as $key => $rotation)
                                <option data-content="<i class='fa fa-circle mr-2' style='color: {{ $rotation->color_code }}'></i>
                                    {{ $rotation->rotation_frequency != 'monthly' ? $rotation->rotation_name . ' [ ' . $rotation->rotation_frequency . ' ' . __('app.on') . ' ' . $rotation->schedule_on . ' ]' : $rotation->rotation_name . ' [ ' . $rotation->rotation_frequency . ' ' . __('app.onDate') . ' ' . $rotation->rotation_date .' ]' }}" value="{{ $rotation->id }}" id="rotation{{ $rotation }}"
                                    @selected($rotaionId == $rotation->id)>

                                    {{ $rotation->rotation_frequency != 'monthly' ? $rotation->rotation_name . ' [ ' . $rotation->rotation_frequency . ' ' . __('app.on') . ' ' . $rotation->schedule_on . ' ]' : $rotation->rotation_name . ' [ ' . $rotation->rotation_frequency . ' ' . __('app.onDate') . ' ' . $rotation->rotation_date .' ]' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-right">
                    <x-forms.button-secondary data-employee-id="{{ $employee->id }}" icon="times"
                        class="remove-employee">
                        @lang('app.remove')
                    </x-forms.button-secondary>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">
                    <x-cards.no-record icon="users" :message="__('messages.noRecordFound')" />
                </td>
            </tr>
        @endforelse
    </x-table>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
</div>

<script>
    $(document).ready(function() {

        $(".rotation").selectpicker();

        $('#empRecordTable').on('change', '.rotation', function(e) {
            e.preventDefault();
            let newRotationId = $(this).val();
            let empId = $(this).data('emp-id');
            let rotationId = '{{ $rotaionId }}';

            var url = "{{ route('shift-rotations.change_employee_rotation') }}";
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                type: 'POST',
                blockUI: true,
                data: {
                    '_token': token,
                    empId: empId,
                    rotationId: rotationId,
                    newRotationId: newRotationId
                },
            });

            return false;
        });

        $('.remove-employee').click(function() {

            let empId = $(this).data('employee-id');
            let rotationId = '{{ $rotaionId }}';
            var url = "{{ route('shift-rotations.remove_employee') }}";

            var token = "{{ csrf_token() }}";

            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.rotationEmpRemove')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmRemove')",
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
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            'empId': empId,
                            'rotationId': rotationId
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                $('#emp-' + empId).fadeOut(function() {
                                    $(this).remove();

                                    if ($('#empRecordTable tbody tr').length === 0) {
                                        let noRecordMessage = "{{ __('messages.noRecordFound') }}";
                                        $('#empRecordTable tbody').html(`
                                            <tr>
                                                <td colspan="3">
                                                    <div class="align-items-center d-flex flex-column text-lightest p-20 w-100">
                                                        <i class="fa fa-users f-21 w-100"></i>
                                                        <div class="f-15 mt-4">
                                                            ${noRecordMessage}
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        `);
                                    }
                                });
                            }
                        }
                    });
                }
            });
        });

        $(MODAL_LG).on('hidden.bs.modal', function () {
            showTable();
        });
    });
</script>
