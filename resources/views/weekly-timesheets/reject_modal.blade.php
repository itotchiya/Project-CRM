<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.timesheetRejectReason')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="followUpForm" method="POST" class="ajax-form">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.reason')"
                                fieldName="reason" fieldId="reason" fieldRequired="true">
                            </x-forms.textarea>
                        </div>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary class="change-weektimesheet-status" data-status="draft" data-timesheet-id="{{ $weeklyTimesheet->id }}" icon="check">@lang('app.reject')</x-forms.button-primary>
</div>

<script>
    $('.change-weektimesheet-status').on('click', function() {
        let status = $(this).data('status');
        let timesheetId = $(this).data('timesheet-id');
        let reason = $('#reason').val();

        var url = "{{ route('weekly-timesheets.change_status') }}";

        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.changeWeeklyTimesheetStatusConfirmation')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirm')",
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
                    blockUI: true,
                    data: {
                        'status': status,
                        'timesheetId': timesheetId,
                        '_token': '{{ csrf_token() }}',
                        'reason': reason
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            window.location.reload(); 
                        }
                    }
                });
            }
        });

    });
</script>
