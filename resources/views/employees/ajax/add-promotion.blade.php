<x-form id="save-promotion-form">
    <div class="modal-header">
        <h5 class="modal-title">{{ $pageTitle }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    </div>
    <div class="modal-body">
        <div class="portlet-body">

            <div class="add-client bg-white rounded">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-sm-12">
                        <input type="hidden" name="user_id" value="{{ $userId }}">
                        <input type="hidden" name="previous_designation_id" value="{{ $currentDesignation->id ?? null }}">
                        <input type="hidden" name="previous_department_id" value="{{ $currentDepartment->id ?? null }}">

                        <div class="row">
                            <div class="col-md-6 col-sm-6">
                                <x-forms.label class="my-3" fieldId="old_designation_id"
                                    :fieldLabel="__('modules.incrementPromotion.oldDesignation')"></x-forms.label>
                                    <span class="input-group-text" id="old_designation_id">{{ $currentDesignation->name }}</span>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <x-forms.label class="my-3" fieldId="old_department_id"
                                    :fieldLabel="__('modules.incrementPromotion.oldDepartment')"></x-forms.label>
                                    <span class="input-group-text" id="old_department_id">{{ $currentDepartment->team_name }}</span>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <x-forms.select fieldId="current_designation_id" :fieldLabel="__('modules.incrementPromotion.newDesignation')" fieldName="current_designation_id" search="true"
                                    fieldRequired="true" class="select-picker">
                                    @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <x-forms.select fieldId="current_department_id" :fieldLabel="__('modules.incrementPromotion.newDepartment')" fieldName="current_department_id" search="true"
                                    fieldRequired="true" class="select-picker">
                                    @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @if($department->id == $employeeDetail->department_id) selected @endif>{{ $department->team_name }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>
                            <div class="col-md-6">
                                <x-forms.text :fieldLabel="__('app.date')" fieldName="date" fieldId="date" :fieldPlaceholder="__('app.date')"
                                    :fieldValue="now(company()->timezone)->translatedFormat(company()->date_format)" fieldRequired />
                            </div>

                            <div class="col-md-6 mt-5">
                                <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.sendNotification')"
                                    fieldName="send_notification" fieldId="send_notification" fieldValue="yes"
                                    fieldRequired="true" :checked='true'/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-promotion" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>

<script>
    datepicker('#date', {
        position: 'bl',
        ...datepickerConfig
    });

    $('.select-picker').selectpicker('refresh');

    $('#save-promotion').click(function() {
        const url = "{{ route('promotions.store') }}";

        $.easyAjax({
            url: url,
            container: '#save-promotion-form',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-promotion",
            data: $('#save-promotion-form').serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    window.location.reload();
                }
            }
        })
    });
</script>
