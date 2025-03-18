@php
$viewLeadCategoryPermission = user()->permission('view_lead_category');
$viewLeadSourcesPermission = user()->permission('view_lead_sources');
$addLeadSourcesPermission = user()->permission('add_lead_sources');
$addLeadCategoryPermission = user()->permission('add_lead_category');
$addProductPermission = user()->permission('add_product');
$addLeadAgentPermission = user()->permission('add_lead_agent');
$viewLeadAgentPermission = user()->permission('view_lead_agents');
$addDealPermission = user()->permission('add_deals');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-lead-data-form" >
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.leadContact.leadDetails')</h4>
                <div class="row p-20">

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="salutation" :fieldLabel="__('modules.client.salutation')"
                            fieldName="salutation">
                            <option value="">--</option>
                            @foreach ($salutations as $salutation)
                                <option value="{{ $salutation->value }}">{{ $salutation->label() }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text :fieldLabel="__('app.name')" fieldName="client_name"
                            fieldId="client_name" :fieldPlaceholder="__('placeholders.name')" fieldRequired="true" />
                    </div>

                    <div class="col-lg-4 col-md-6">

                        <x-forms.email fieldId="client_email" :fieldLabel="__('app.email')"
                            fieldName="client_email" :fieldPlaceholder="__('placeholders.email')" :fieldHelp="__('modules.lead.leadEmailInfo')">
                        </x-forms.email>
                    </div>

                    @if ($viewLeadSourcesPermission != 'none')
                        <div class="col-lg-4 col-md-6">
                            <x-forms.label class="my-3" fieldId="source_id" :fieldLabel="__('modules.lead.leadSource')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="source_id" id="source_id"
                                    data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($sources as $source)
                                        <option value="{{ $source->id }}">{{ $source->type }}</option>
                                    @endforeach
                                </select>

                                @if ($addLeadSourcesPermission == 'all' || $addLeadSourcesPermission == 'added')
                                    <x-slot name="append">
                                        <button type="button"
                                            class="btn btn-outline-secondary border-grey add-lead-source"
                                            data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('modules.lead.leadSource') }}">
                                            @lang('app.add')</button>
                                    </x-slot>
                                @endif
                            </x-forms.input-group>
                        </div>
                    @endif

                    @if ($addPermission == 'all')
                        <div class="col-lg-4 col-md-6">
                            <x-forms.select fieldId="added_by" :fieldLabel="__('app.added').' '.__('app.by')"
                                fieldName="added_by">
                                <option value="">--</option>
                                @foreach ($employees as $item)
                                    <x-user-option :user="$item" :selected="user()->id == $item->id" />
                                @endforeach
                            </x-forms.select>
                        </div>
                    @endif

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="lead_owner" :fieldLabel="__('app.owner')" fieldName="lead_owner">
                            <option value="">--</option>
                            @foreach ($employees as $item)
                                <x-user-option :user="$item" :selected="$loop->first" />
                            @endforeach
                        </x-forms.select>
                    </div>

                </div>

                @if (in_array($addDealPermission, ['all', 'added']))
                    <div class="col-md-12 py-20">
                        <div class="form-group">
                            <div class="mt-2 d-flex">
                                <x-forms.checkbox fieldId="create_deal"
                                                :fieldLabel="__('app.create') .' '. __('modules.deal.title')"
                                                fieldName="create_deal" :checked="true"/>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row p-20" id="add_deal">
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.deal.dealName')" fieldName="name"
                                      fieldId="name" :fieldPlaceholder="__('placeholders.name')" fieldRequired="true"
                                      :popover="__('modules.deal.dealnameInfo')"/>
                    </div>
                    <div class="col-lg-4">
                        <x-forms.select fieldId="pipelineData" :fieldLabel="__('modules.deal.pipeline')"
                                        fieldName="pipeline" fieldRequired="true"
                                        :popover="__('modules.lead.pipelineInfo')">
                            @foreach ($leadPipelines as $pipeline)
                                <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-4">
                        <x-forms.select fieldId="stages" :fieldLabel="__('modules.deal.stages')" fieldName="stage_id"
                                        fieldRequired="true">

                        </x-forms.select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.label class="my-3" fieldId="value" :fieldLabel="__('modules.deal.dealValue')"
                                       fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <x-slot name="prepend">
                                <span
                                    class="input-group-text f-14">{{company()->currency->currency_code }} ({{ company()->currency->currency_symbol }})</span>
                            </x-slot>
                            <input type="number" name="value" id="value" class="form-control height-35 f-14" value="0"/>
                        </x-forms.input-group>
                    </div>
                    <div class="col-md-5 col-lg-4 dueDateBox mt-1">
                        <x-forms.datepicker fieldId="close_date" fieldRequired="true"
                                            :fieldLabel="__('modules.deal.closeDate')"
                                            fieldName="close_date" :fieldPlaceholder="__('placeholders.date')"
                                            :fieldValue="( now(company()->timezone)->addDays(30)->translatedFormat(company()->date_format))"/>
                    </div>
                    @if ($viewLeadCategoryPermission != 'none')
                        <div class="col-lg-4 col-md-6">
                            <x-forms.label class="my-3" fieldId="category_id" :fieldLabel="__('modules.deal.dealCategory')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="category_id" id="category_id"
                                    data-live-search="true">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->category_name }}
                                        </option>
                                    @endforeach
                                </select>

                                @if ($addLeadCategoryPermission == 'all' || $addLeadCategoryPermission == 'added')
                                    <x-slot name="append">
                                        <button type="button"
                                            class="btn btn-outline-secondary border-grey add-lead-category"
                                            data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('modules.lead.leadCategory') }}">
                                            @lang('app.add')</button>
                                    </x-slot>
                                @endif
                            </x-forms.input-group>
                        </div>
                    @endif
                    @if ($viewLeadAgentPermission != 'none')
                        <div class="col-lg-4 col-md-6">
                            <x-forms.label class="mt-3" fieldId="deal_agent_id" :fieldLabel="__('modules.deal.dealAgent')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="agent_id" id="deal_agent_id"
                                        data-live-search="true">
                                    <option value="">--</option>
                                </select>

                                @if ($addLeadAgentPermission == 'all' || $addLeadAgentPermission == 'added')
                                    <x-slot name="append">
                                        <button type="button"
                                                class="btn btn-outline-secondary border-grey add-lead-agent"
                                                data-toggle="tooltip"
                                                data-original-title="{{ __('app.add').'  '.__('app.new').' '.__('modules.tickets.agents') }}">@lang('app.add')</button>
                                    </x-slot>
                                @endif
                            </x-forms.input-group>
                        </div>
                    @elseif(in_array(user()->id, $leadAgentArray))
                        <input type="hidden" value="{{ $myAgentId }}" name="agent_id">
                    @endif

                    @if(in_array('products', user_modules()) || in_array('purchase', user_modules()))
                        <div class="col-lg-4 mt-3">
                            <div class="form-group">
                                <x-forms.label fieldId="selectProduct" :fieldLabel="__('app.menu.products')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" data-live-search="true" data-size="8"
                                            name="product_id[]" multiple id="add-products"
                                            title="{{ __('app.menu.selectProduct') }}">
                                        @foreach ($products as $item)
                                            <option data-content="{{ $item->name }}" value="{{ $item->id }}">
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    {{-- @if ($addProductPermission == 'all' || $addProductPermission == 'added')
                                        <x-slot name="append">
                                            <a href="{{ route('products.create') }}" data-redirect-url="no"
                                               class="btn btn-outline-secondary border-grey openRightModal"
                                               data-toggle="tooltip"
                                               data-original-title="{{ __('app.add').' '.__('modules.dashboard.newproduct') }}">@lang('app.add')</a>
                                        </x-slot>
                                    @endif --}}
                                </x-forms.input-group>
                            </div>
                        </div>
                    @endif

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="deal_watcher" :fieldLabel="__('app.dealWatcher')"
                                        fieldName="deal_watcher">
                            <option value="">--</option>
                            @foreach ($employees as $item)
                                <x-user-option :user="$item" :selected="(user()->id == $item->id)"/>
                            @endforeach
                        </x-forms.select>
                    </div>
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-top-grey">
                    <a href="javascript:;" class="text-dark toggle-other-details"><i class="fa fa-chevron-down"></i>
                        @lang('modules.client.companyDetails')</a>
                </h4>


                <div class="row p-20 d-none" id="other-details">

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.lead.companyName')" fieldName="company_name"
                            fieldId="company_name" :fieldPlaceholder="__('placeholders.company')" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.lead.website')" fieldName="website" fieldId="website"
                            :fieldPlaceholder="__('placeholders.website')" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.tel fieldId="mobile" :fieldLabel="__('modules.lead.mobile')" fieldName="mobile"
                           :fieldPlaceholder="__('placeholders.mobile')"></x-forms.tel>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.client.officePhoneNumber')" fieldName="office"
                            fieldId="office" fieldPlaceholder="" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="country" :fieldLabel="__('app.country')" fieldName="country"
                            search="true">
                            <option value="">--</option>
                            @foreach ($countries as $item)
                                <option data-tokens="{{ $item->iso3 }}"
                                    data-content="<span class='flag-icon flag-icon-{{ strtolower($item->iso) }} flag-icon-squared'></span> {{ $item->nicename }}"
                                    value="{{ $item->nicename }}">{{ $item->nicename }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.stripeCustomerAddress.state')" fieldName="state"
                            fieldId="state" :fieldPlaceholder="__('placeholders.state')" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.stripeCustomerAddress.city')" fieldName="city"
                            fieldId="city" :fieldPlaceholder="__('placeholders.city')" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.stripeCustomerAddress.postalCode')"
                            fieldName="postal_code" fieldId="postal_code" :fieldPlaceholder="__('placeholders.postalCode')" />
                    </div>
                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.address')"
                                fieldName="address" fieldId="address" :fieldPlaceholder="__('placeholders.address')">
                            </x-forms.textarea>
                        </div>
                    </div>

                    <x-forms.custom-field :fields="$fields" class="col-md-12"></x-forms.custom-field>


                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-lead-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-secondary class="mr-3" id="save-more-lead-form" icon="check-double">@lang('app.saveAddMore')
                    </x-forms.button-secondary>
                    <x-forms.button-cancel :link="route('lead-contact.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script>


    $(document).ready(function() {

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });


        $('#save-more-lead-form').click(function () {

            $('#add_more').val(true);

            const url = "{{ route('lead-contact.store') }}?add_more=true";

            var data = $('#save-lead-data-form').serialize() + '&add_more=true';

            saveLead(data, url, "#save-more-lead-form");

        });

        $('#save-lead-form').click(function() {
            const url = "{{ route('lead-contact.store') }}";
            var data = $('#save-lead-data-form').serialize();
            saveLead(data, url, "#save-lead-form");

        });

        function saveLead(data, url, buttonSelector) {
            $.easyAjax({
                url: url,
                container: '#save-lead-data-form',
                type: "POST",
                file: true,
                disableButton: true,
                blockUI: true,
                buttonSelector: buttonSelector,
                data: data,
                success: function(response) {
                    if(response.add_more == true) {

                        var right_modal_content = $.trim($(RIGHT_MODAL_CONTENT).html());

                        if(right_modal_content.length) {

                            $(RIGHT_MODAL_CONTENT).html(response.html.html);
                            $('#add_more').val(false);
                        }
                        else {

                            $('.content-wrapper').html(response.html.html);
                            init('.content-wrapper');
                            $('#add_more').val(false);
                        }
                    }
                    else {
                        window.location.href = response.redirectUrl;
                    }

                    if (typeof showTable !== 'undefined' && typeof showTable === 'function') {
                            showTable();
                    }
                }
            });

        }



        $('body').on('click', '.add-lead-source', function() {
            var url = '{{ route('lead-source-settings.create') }}';
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('.toggle-other-details').click(function() {
            $(this).find('svg').toggleClass('fa-chevron-down fa-chevron-up');
            $('#other-details').toggleClass('d-none');
        });

        init(RIGHT_MODAL);
    });

    function checkboxChange(parentClass, id){
        var checkedData = '';
        $('.'+parentClass).find("input[type= 'checkbox']:checked").each(function () {
            checkedData = (checkedData !== '') ? checkedData+', '+$(this).val() : $(this).val();
        });
        $('#'+id).val(checkedData);
    }
</script>

<script>
    //Create deal on lead creation script
    $(document).ready(function () {

        $('#create_deal').change(function () {
            $('#add_deal').toggleClass('d-none');
        });

        var id = $('#category_id').val();
        if(id != '')
        {
            getAgents($('#category_id').val());
        }

        function getAgents(categoryId){
            var url = "{{ route('deals.get_agents', ':id')}}";
            url = url.replace(':id', categoryId);
            $.easyAjax({
                url: url,
                type: "GET",
                success: function(response)
                {
                    var options = [];
                    var rData = [];
                    if($.isArray(response.data))
                    {
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var selectData = '';
                            options.push(value);
                        });

                        $('#deal_agent_id').html('<option value="">--</option>' + options);

                    }
                    else
                    {
                        $('#deal_agent_id').html(response.data);
                    }

                    $('#deal_agent_id').selectpicker('refresh');
                }
            });
        }

        $('#close_date').each(function (ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        getStages($('#pipelineData').val());

        $('#category_id').change(function(){
            var id = $(this).val();
            if(id != '')
            {
                getAgents(id);
            }
        });

        function getStages(pipelineId) {
            var url = "{{ route('deals.get-stage', ':id') }}";
            url = url.replace(':id', pipelineId);
            $.easyAjax({
                url: url,
                type: "GET",
                success: function (response) {
                    if (response.status == 'success') {
                        var options = [];
                        var rData = [];
                        rData = response.data;
                        $.each(rData, function (index, value) {
                            var seleted = '';
                            var stageID = 0;
                            var selectData = '';
                            selectData = `<option data-content="<i class='fa fa-circle' style='color: ${value.label_color}'></i> ${value.name} " value="${value.id}"> ${value.name}</option>`;
                            options.push(selectData);
                        });
                        $('#stages').html(options);
                        $('#stages').selectpicker('refresh');
                    }
                }
            });
        }

        // GET STAGES
        $('#pipelineData').on("change", function (e) {
            let pipelineId = $(this).val();
            getStages(pipelineId)
        });

        $('body').on('click', '.add-lead-agent', function () {
            var categoryId = $('#category_id').val();
            var url = "{{ route('lead-agent-settings.create').'?categoryId='}}"+categoryId;
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '.add-lead-category', function () {
            var url = '{{ route('leadCategory.create') }}';
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });
    });
</script>
