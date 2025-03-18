@php
    $manageContractTypePermission = user()->permission('manage_contract_type');
@endphp

@if (!in_array('clients', user_modules()))
    <x-alert class="mb-3" type="danger" icon="exclamation-circle"><span>@lang('messages.enableClientModule')</span>
        <x-forms.link-secondary icon="arrow-left" :link="route('contracts.index')">@lang('app.back')</x-forms.link-secondary>
    </x-alert>
@else

<div class="row">
    <div class="col-sm-12">

        <x-form id="save-contract-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.contractDetails')</h4>
                <input type="hidden" name="template_id" value="{{ $contractTemplate->id ?? '' }}">

                <div class="row p-20">

                                    <!-- CONTRACT NUMBER START -->
                        <div class="col-md-2">
                            <div class="form-group mb-lg-0 mb-md-0 mb-4">
                                <x-forms.label class="mb-12" fieldId="contract_number"
                                    :fieldLabel="__('modules.contracts.contractNumber')" fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <x-slot name="prepend">
                                        <span
                                            class="input-group-text">{{ invoice_setting()->contract_prefix }}{{ invoice_setting()->contract_number_separator }}{{ $zero }}</span>
                                    </x-slot>
                                    <input type="number" name="contract_number" id="contract_number" class="form-control height-35 f-15"
                                        value="{{ is_null($lastContract) ? 1 : $lastContract }}">
                                </x-forms.input-group>
                            </div>
                        </div>

                                        <!-- CONTRACT NUMBER END -->
                        <div class="col-md-10" style="margin-top: -16px">
                                <x-forms.text fieldId="subject" :fieldLabel="__('app.subject')" fieldName="subject"
                                :fieldValue="($contractTemplate ? $contractTemplate->subject : '')"
                                fieldRequired="true"></x-forms.text>
                        </div>
                    <div class="col-md-6">
                            <div class="form-group c-inv-select mb-4">
                                <x-forms.label fieldId="project_id" :fieldLabel="__('app.project')">
                                </x-forms.label>
                                <div class="select-others height-35 rounded">
                                    <select class="form-control select-picker" data-live-search="true" data-size="8"
                                        name="project_id" id="project_id">
                                        <option value="">--</option>
                                            @foreach ($projects as $project)
                                                <option  value="{{ $project->id }}">
                                                    {{ $project->project_name }}</option>
                                            @endforeach
                                    </select>
                                </div>
                            </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="description" :fieldLabel="__('app.description')">
                            </x-forms.label>
                            <div
                                id="description">{!! $contractTemplate ? $contractTemplate->contract_detail : '' !!}</div>
                            <textarea name="description" id="description-text" class="d-none"></textarea>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.datepicker fieldId="start_date" fieldRequired="true"
                                            :fieldLabel="__('modules.projects.startDate')" fieldName="start_date"
                                            :fieldValue="($contract ? $contract->start_date->timezone(company()->timezone)->format(company()->date_format) : '')"
                                            :fieldPlaceholder="__('placeholders.date')"/>
                    </div>

                    <div class="col-md-6 col-lg-3 due-date-box"
                         @if($contract && is_null($contract->end_date)) style="display: none" @endif>
                        <x-forms.datepicker fieldId="end_date"
                                            :fieldValue="($contract ? ($contract->end_date==null ? $contract->end_date : $contract->end_date->timezone(company()->timezone)->format(company()->date_format)) : '')"
                                            :fieldLabel="__('modules.timeLogs.endDate')" fieldName="end_date"
                                            :fieldPlaceholder="__('placeholders.date')"/>
                    </div>

                    <div class="col-md-2 col-lg-2 pt-5">
                        <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2"
                                          :checked="$contract ? is_null($contract->end_date) : ''"
                                          :fieldLabel="__('app.withoutDueDate')"
                                          fieldName="without_duedate" fieldId="without_duedate" fieldValue="yes"/>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.label class="mt-3" fieldId="contractType"
                                       :fieldLabel="__('modules.contracts.contractType')"
                                       fieldRequired="true"></x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="contract_type" id="contractType"
                                    data-live-search="true">
                                <option value="">--</option>
                                @foreach ($contractTypes as $item)
                                    <option
                                        @if ($contractTemplate && $item->id == $contractTemplate->contract_type_id) selected
                                        @endif value="{{ $item->id }}">
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($manageContractTypePermission == 'all')
                                <x-slot name="append">
                                    <button id="createContractType" type="button"
                                            class="btn btn-outline-secondary border-grey"
                                            data-toggle="tooltip" data-original-title="{{ __('modules.contracts.addContractType') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.label class="mt-3" fieldId="amount"
                                       :fieldLabel="__('modules.contracts.contractValue')"
                                       :popover="__('modules.contracts.setZero')" fieldRequired="true"></x-forms.label>
                        <x-forms.input-group>
                            <input type="number" min="0" name="amount" value="{{ $contractTemplate->amount ?? '' }}"
                                   class="form-control height-35 f-14"/>
                        </x-forms.input-group>
                    </div>

                    <!-- CURRENCY START -->
                    <div class="col-md-6 col-lg-3">
                        <div class="form-group c-inv-select mb-lg-0 mb-md-0 mb-4">
                            <x-forms.label class="mt-3" fieldId="currency_id"
                                           :fieldLabel="__('modules.invoices.currency')">
                            </x-forms.label>

                            <div class="select-others height-35 rounded">
                                <select class="form-control select-picker" name="currency_id" id="currency_id">
                                    @foreach ($currencies as $currency)
                                        <option @if(isset($contractTemplate))
                                            @if ($contractTemplate && $currency->id == $contractTemplate->currency_id) selected @endif
                                            @elseif ($currency->id == company()->currency_id) selected
                                            @endif
                                            value="{{ $currency->id }}">
                                            {{ $currency->currency_code . ' (' . $currency->currency_symbol . ')' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- CURRENCY END -->

                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-top-grey">
                    @lang('modules.client.clientDetails')</h4>
                <div class="row p-20">
                    <div class="col-md-6 col-lg-4 my-3" id="client_list_ids">
                        <x-client-selection-dropdown :clients="$clients" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.text fieldId="cell" :fieldLabel="__('modules.client.cell')"
                                      :fieldValue="($contract ? $contract->cell: '')" fieldName="cell">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.text fieldId="office" :fieldPlaceholder="__('placeholders.mobileWithPlus')"
                                      :fieldValue="($contract ? $contract->office: '')"
                                      :fieldLabel="__('modules.client.officePhoneNumber')" fieldName="office">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.text fieldId="city" :fieldValue="($contract ? $contract->city: '')"
                                      :fieldLabel="__('modules.stripeCustomerAddress.city')"
                                      :fieldPlaceholder="__('placeholders.city')"
                                      fieldName="city">
                        </x-forms.text>
                    </div>


                    <div class="col-md-6 col-lg-3">
                        <x-forms.text fieldId="state" :fieldValue="($contract ? $contract->state: '')"
                                      :fieldLabel="__('modules.stripeCustomerAddress.state')" fieldName="state"
                                      :fieldPlaceholder="__('placeholders.state')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.text fieldId="country" :fieldValue="($contract ? $contract->country: '')"
                                      :fieldLabel="__('modules.stripeCustomerAddress.country')" fieldName="country">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.text fieldId="postal_code" :fieldValue="($contract ? $contract->postal_code: '')"
                                        :fieldPlaceholder="__('placeholders.postalCode')"
                                      :fieldLabel="__('modules.stripeCustomerAddress.postalCode')"
                                      fieldName="postal_code">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6">
                        <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                                          :fieldLabel="__('modules.contracts.alternateAddress')"
                                          fieldName="alternate_address"
                                          fieldId="alternate_address" :fieldPlaceholder="__('placeholders.address')">
                            {{ $contract ? $contract->alternate_address : '' }}
                        </x-forms.textarea>
                    </div>
                    <div class="col-md-6">
                        <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.contracts.notes')"
                                          fieldName="note" fieldId="note">
                            {{ $contract->description ?? '' }}
                        </x-forms.textarea>
                    </div>
                        <x-forms.custom-field :fields="$fields" class="col-md-12"></x-forms.custom-field>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-contract-form" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('contracts.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

@endif
<script>
    $(document).ready(function () {
        const dp1 = datepicker('#start_date', {
            position: 'bl',
            onSelect: (instance, date) => {
                if (typeof dp2.dateSelected !== 'undefined' && dp2.dateSelected.getTime() < date
                    .getTime()) {
                    dp2.setDate(date, true)
                }
                if (typeof dp2.dateSelected === 'undefined') {
                    dp2.setDate(date, true)
                }
                dp2.setMin(date);
            },
            ...datepickerConfig
        });

        const dp2 = datepicker('#end_date', {
            position: 'bl',
            onSelect: (instance, date) => {
                dp1.setMax(date);
            },
            ...datepickerConfig
        });

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        $('#add-client').click(function () {
            $(MODAL_XL).modal('show');

            const url = "{{ route('clients.create') }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: MODAL_XL,
                success: function (response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        $('#save-contract-form').click(function () {
            const note = document.getElementById('description').children[0].innerHTML;
            document.getElementById('description-text').value = note;

            const url = "{{ route('contracts.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-contract-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-contract-form",
                file: true,
                data: $('#save-contract-data-form').serialize(),
                redirect: true
            })
        });

        quillMention(null, '#description');

        $('#createContractType').click(function () {
            const url = "{{ route('contractTypes.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#contract_client_id').change(function () {
            const id = $(this).val();
            let url = "{{ route('clients.ajax_details', ':id') }}";
            url = url.replace(':id', id);
            const token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                container: '#save-contract-data-form',
                type: "POST",
                blockUI: true,
                data: {
                    _token: token
                },
                success: function (response) {
                    if (response.status == 'success') {
                        if (response.data !== null) {
                            $('#office').val(response.data.client_details.office);
                            $('#postal_code').val(response.data.client_details.postal_code);
                            $('#state').val(response.data.client_details.state);
                            $('#city').val(response.data.client_details.city);
                            if (response.data.country) {
                                $('#country').val(response.data.country.nicename);
                            }
                        }
                    }
                }
            });

        });
        $('#client_list_id').change(function() {
            var id = $(this).val();
            if (id == '') {
                id = 0;
            }
            var url = "{{ route('contracts.project_detail', ':id') }}";
            url = url.replace(':id', id);
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                container: '#save-contract-data-form',
                type: "POST",
                blockUI: true,
                data: {
                    _token: token
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#project_id').html(response.data);
                        $('#project_id').selectpicker('refresh');
                    }
                }
            });
        });

        $('#save-contract-data-form').on('change', '#project_id', function () {
            let id = $(this).val();
            if (id === '' || id == null) {
                id = 0;
            }
            let url = "{{ route('clients.client_details', ':id') }}";
            url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                type: "GET",
                container: '#save-contract-data-form',
                blockUI: true,
                redirect: true,
                success: function (data) {
                        $('#client_list_id').html(data.teamData);
                        $('#client_list_id').selectpicker('refresh');
                }
            })
        });

        $('#without_duedate').click(function () {
            $('.due-date-box').toggle();
        });

        init(RIGHT_MODAL);
    });
</script>
