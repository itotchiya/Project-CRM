
<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="modal-header">
    <h5 class="modal-title">@lang('app.editVisa')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="save-visa-data-form" method="PUT" class="ajax-form">

            <input type="hidden" value="{{ request()->empid }}" name="emp_id">

            <div class="row">

                <div class="col-lg-3">
                    <x-forms.text :fieldLabel="__('modules.employees.visaNumber')"
                        fieldName="visa_number" fieldId="visa_number"
                        :fieldValue="$visa->visa_number ?? '' " :fieldRequired="true" />
                </div>

                <div class="col-lg-3">
                    <x-forms.select fieldId="country" :fieldLabel="__('app.country')" fieldName="country"
                        search="true" :fieldRequired="true">
                        <option value="">--</option>
                        @foreach ($countries as $item)
                            <option @if ($visa->country_id == $item->id) selected @endif data-tokens="{{ $item->iso3 }}" data-content="<span
                            class='flag-icon flag-icon-{{ strtolower($item->iso) }} flag-icon-squared'></span>
                        {{ $item->nicename }}" value="{{ $item->id }}">{{ $item->nicename }}</option>
                        @endforeach
                    </x-forms.select>
                </div>

                <div class="col-lg-3">
                    <x-forms.datepicker fieldId="issue_date" fieldRequired="true"
                                        :fieldLabel="__('modules.employees.issueDate')" fieldName="issue_date"
                                        :fieldValue="$visa ? $visa->issue_date->format(company()->date_format) :  now(company()->timezone)->format(company()->date_format)"
                                        :fieldPlaceholder="__('placeholders.date')"/>
                </div>

                <div class="col-lg-3">
                    <x-forms.datepicker fieldId="expiry_date" fieldRequired="true"
                                        :fieldLabel="__('modules.employees.expiryDate')" fieldName="expiry_date"
                                        :fieldValue="$visa ? $visa->expiry_date->format(company()->date_format) :  now(company()->timezone)->format(company()->date_format)"
                                        :fieldPlaceholder="__('placeholders.date')"/>
                </div>

                <div class="col-lg-12">
                    <x-forms.file allowedFileExtensions="png jpg jpeg svg pdf doc docx" class="mr-0 mr-lg-2 mr-md-2"
                        :fieldLabel="__('modules.employees.scanCopy')" fieldName="file"
                        :fieldValue="$visa->file ? $visa->image_url : '' "
                        fieldId="file">
                    </x-forms.file>
                </div>



            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-visa-form" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    datepicker('#issue_date', {
            position: 'bl',
            ...datepickerConfig
        });

    datepicker('#expiry_date', {
        position: 'bl',
        ...datepickerConfig
    });

    init(MODAL_LG);

    $('#save-visa-form').click(function(){
        $.easyAjax({
            url: "{{ route('employee-visa.update', $visa->id) }}",
                    container: '#save-visa-data-form',
                    type: "POST",
                    disableButton: true,
                    blockUI: true,
                    buttonSelector: 'save-visa-form',
                    file: true,
                    data: $('#save-visa-data-form').serialize(),
                    success: function (response) {
                        if (response.status === 'success') {
                            var url = " {{ route('employees.show', $visa->user_id).'?tab=immigration' }}";
                            window.location.href=url;
                        }
                    }
        });
    });
</script>
