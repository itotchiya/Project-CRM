<x-form id="save-mark-holiday-form">
    <div class="modal-header">
        <h5 class="modal-title" id="modelHeading">@lang('modules.holiday.markHoliday')</h5>
        <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">×</span></button>
    </div>
    <div class="modal-body">

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="f-14 text-dark-grey mb-12 w-100" for="usr">@lang('modules.holiday.officeHolidayMarkDays')</label>
                        <div class="d-flex mt-2 flex-wrap">
                            <x-forms.weeks fieldName="office_holiday_days[]" fieldRequired="true"></x-forms.weeks>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="form-group my-3">
                        <x-forms.text :fieldLabel="__('modules.holiday.occasion')" fieldName="occassion"
                            fieldId="occassion" :fieldPlaceholder="__('modules.holiday.occasion')"
                            fieldValue="" data-toggle="tooltip" data-original-title="{{ __('messages.selectOccassion')}}" />
                    </div>
                </div>
                <input type="hidden" name="notification_sent" value="no">
            </div>

    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-mark-holiday" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>
<script>

    $('body').on('click', '#save-mark-holiday', function() {
            Swal.fire({
                title: "@lang('messages.markHolidayTitle')",
                text: "@lang('messages.noteHolidayText')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmSave')",
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
                    var url = "{{ route('holidays.mark_holiday_store') }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        blockUI: true,
                        disableButton: true,
                        buttonSelector: "#save-mark-holiday",
                        data: $('#save-mark-holiday-form').serialize(),
                        success: function(response) {
                            if (response.status == "success") {
                                if (response.redirectUrl == 'table-view') {
                                    window.LaravelDataTables["holiday-table"].draw(true);
                                    $(MODAL_LG).modal('hide');
                                } else {
                                    location.href = response.redirectUrl;
                                }
                            }
                        }
                    });
                }
            });
        });

</script>
