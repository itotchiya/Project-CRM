@if (!in_array('clients', user_modules()) && !in_array('client', user_roles()))
    <x-alert class="mb-3" type="danger" icon="exclamation-circle"><span>@lang('messages.enableClientModule')</span>
        <x-forms.link-secondary icon="arrow-left" :link="route('estimate-request.index')">@lang('app.back')</x-forms.link-secondary>
    </x-alert>
@else

<!-- CREATE ESTIMATE REQUEST START -->
<div class="bg-white rounded b-shadow-4 create-inv">
    <!-- HEADING START -->
    <div class="px-lg-4 px-md-4 px-3 py-3">
        <h4 class="mb-0 f-21 font-weight-normal ">@lang('app.estimateDetails')</h4>
    </div>
    <!-- HEADING END -->
    <hr class="m-0 border-top-grey">
    <!-- FORM START -->
    <x-form class="c-inv-form" id="saveInvoiceForm">
        <!-- CURRENCY START -->
        <div class="row px-lg-4 px-md-4 px-3 py-3">

            @if (in_array('client', user_roles()))
                <input type="hidden" name="client_id" id="client_id" value="{{ user()->id }}">

                <div class="col-md-6 col-lg-4">
                    <x-forms.select fieldId="project_id" fieldName="project_id" :fieldLabel="__('app.project')"
                        search="true">
                        <option value="">--</option>
                        @foreach ($projects as $project)
                            <option data-currency-id="{{ $project->currency_id }}" value="{{ $project->id }}">
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </x-forms.select>
                </div>
            @else
                <div class="col-md-6 col-lg-4 my-3" id="client_list_ids">
                    <x-client-selection-dropdown :clients="$clients"/>
                </div>

                <div class="col-md-6 col-lg-4">
                    <x-forms.select fieldId="project_id" fieldName="project_id" :fieldLabel="__('app.project')"
                        search="true">
                    </x-forms.select>
                </div>
            @endif

            <div class="col-md-6 col-lg-4">
                <x-forms.number fieldId="estimated_budget" :fieldLabel="__('modules.estimateRequest.estimatedBudget')" fieldName="estimated_budget"
                    :fieldPlaceholder="__('placeholders.price')" fieldRequired="false"></x-forms.number>
            </div>

            <!-- CURRENCY START -->
            <div class="col-md-6 col-lg-4">
                <div class="form-group c-inv-select my-3">
                    <x-forms.label fieldId="currency_id" :fieldLabel="__('modules.invoices.currency')">
                    </x-forms.label>

                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" name="currency_id" id="currency_id">
                            @foreach ($currencies as $currency)
                                <option
                                    @selected ($currency->id == company()->currency_id)
                                    value="{{ $currency->id }}">
                                    {{ $currency->currency_code . ' (' . $currency->currency_symbol . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <!-- CURRENCY END -->

            <div class="col-lg-4 col-md-6">
                <x-forms.text :fieldLabel="__('modules.estimateRequest.earlyRequest')" fieldName="early_requirement" fieldId="early_requirement"
                        :fieldPlaceholder="__('placeholders.days')" />
            </div>

            <div class="col-md-12 my-3">
                <div class="form-group">
                    <x-forms.label fieldId="description" :fieldLabel="__('modules.estimateRequest.description')" :fieldRequired='true'>
                    </x-forms.label>
                    <div id="description"></div>
                    <textarea name="description" id="description-text" class="d-none"></textarea>
                </div>
            </div>

        </div>

        <!-- CANCEL SAVE SEND START -->
        <x-form-actions class="c-inv-btns">
            <div class="d-flex mb-3">
                <x-forms.button-primary id="save-estimate-request" class="mr-3" icon="check">@lang('app.save')
                </x-forms.button-primary>
                <x-forms.button-cancel :link="route('estimates.index')" class="border-0">@lang('app.cancel')
                </x-forms.button-cancel>
            </div>

        </x-form-actions>
        <!-- CANCEL SAVE SEND END -->

    </x-form>
    <!-- FORM END -->
</div>
<!-- CREATE ESTIMATE REQUEST END -->

@endif
<script>
    $(document).ready(function() {
        quillMention(null, '#description');

        $('#save-estimate-request').click(function() {
            let note = document.getElementById('description').children[0].innerHTML;
            document.getElementById('description-text').value = note;

            if (KTUtil.isMobileDevice()) {
                $('.desktop-description').remove();
            } else {
                $('.mobile-description').remove();
            }

            $.easyAjax({
                url: "{{ route('estimate-request.store') }}",
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                file: true,
                data: $('#saveInvoiceForm').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            })
        });

        $('body').on('change', "#client_list_id", function () {
            let clientId = $(this).val();
            let requesterType = 'client';
            if (requesterType == 'client' && clientId) {
                let url = "{{ route('get.projects') }}";
                $.easyAjax({
                    url: url,
                    type: "GET",
                    data: {
                        "requesterType": requesterType,
                        "clientId": clientId,
                    },
                    success: function(response) {
                        let options = [];
                        let rData = [];
                        rData = response.projects;
                        $.each(rData, function(index, value) {
                            let selectData = '';
                            selectData = '<option value="' + value.id + '">' + value.project_name + '</option>';
                            options.push(selectData);
                        });

                        $('#project_id').html('<option value="">--</option>' +
                            options);
                        $('#project_id').selectpicker('refresh');
                    }
                })
            } else {
                $('#project_id').html('<option value="">--</option>');
                $('#project_id').selectpicker('refresh');
            }
        });
        init(RIGHT_MODAL);
    });

</script>
