<div class="modal-header">
    <h5 class="modal-title">@lang('app.runShiftRotation')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>

<div class="modal-body">
    <x-alert type="warning" class="">
        @lang('messages.selectRunRotation')
    </x-alert>
    <x-forms.button-primary icon="sync-alt" id="run-rotation-btn" class="mb-4">
        @lang('modules.attendance.runRotation')
    </x-forms.button-primary>

    <form id="run-rotation-form">
        <div class="mb-3">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th> <input type="checkbox" id="select-all"></th>
                        <th>@lang('app.rotationName')</th>
                        <th>@lang('app.noOfEmp')</th>
                        <th>@lang('app.replacePreAssignedShift')</th>
                        <th>@lang('app.sendRotationNotification')</th>
                    </tr>
                </thead>
                <tbody>
                    @if($rotations->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center">
                                @lang('messages.noData')
                            </td>
                        </tr>
                    @else
                        @foreach($rotations as $rotation)
                            <tr>
                                <td>
                                    <input type="checkbox" name="rotation_ids[]" value="{{ $rotation->id }}">
                                </td>
                                <td>{{ $rotation->rotation_name }}</td>
                                <td>
                                    <a href="javascript:;" class="text-darkest-grey" id="manageEmployees" data-rotation-id="{{ $rotation->id }}" data-toggle="tooltip" data-original-title="@lang('Manage Employees')">
                                        {{ $rotation->employees_count }}
                                    </a>
                                </td>
                                <td>
                                    @if($rotation->override_shift == 'yes')
                                        <span class="badge badge-primary">@lang('Yes')</span>
                                    @else
                                        <span class="badge badge-secondary">@lang('No')</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rotation->send_mail == 'yes')
                                        <span class="badge badge-primary">@lang('Yes')</span>
                                    @else
                                        <span class="badge badge-secondary">@lang('No')</span>
                                    @endif
                                </td>
                                
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </form>
</div>

<script>
    $(document).ready(function () {

        $('#select-all').on('click', function () {
            $('input[name="rotation_ids[]"]').prop('checked', this.checked);
        });

        $('#run-rotation-btn').on('click', function () {
            let rotationIds = [];

            $('input[name="rotation_ids[]"]:checked').each(function () {
                rotationIds.push($(this).val());
            });

                $.easyAjax({
                    url: '{{ route("shift-rotations.run_rotation_post") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        rotation_ids: rotationIds
                    },
                    success: function (response) {
                        if (rotationIds.length > 0) {
                            if (response.status == 'success') {
                                window.location.reload();
                            }
                        }else{

                        }
                    }
                });
          

        });
    });
</script>