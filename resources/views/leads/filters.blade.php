<x-filters.filter-box>
    <!-- DATE START -->
    <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
        <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
        <div class="select-status d-flex">
            <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                id="datatableRange" placeholder="@lang('placeholders.dateRange')">
        </div>
    </div>
    <!-- DATE END -->
    <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
        <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('modules.deal.pipeline')</p>
        <div class="select-status">
            <select class="form-control select-picker pipelineFilter" name="pipeline" id="pipeline">
                {{-- <option value="all">@lang('modules.lead.all')</option> --}}
                @foreach($pipelines as $pipeline)
                    <option @selected($pipeline->default == 1) value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <!-- CATEGORY START -->
    <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
        <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('modules.deal.category')</p>
        <div class="select-status">
            <select class="form-control select-picker categoryFilter" name="category" id="category">
                <option value="all">@lang('app.all')</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    <!-- CATEGORY END -->

    <!-- PRODUCT START -->
    <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
        <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('modules.deal.product')</p>
        <div class="select-status">
            <select class="form-control select-picker productFilter" name="product" id="product">
                <option value="all">@lang('app.all')</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <!-- PRODUCT END -->

    <!-- SEARCH BY TASK START -->
    <div class="task-search d-flex  py-1 px-lg-3 px-0 border-right-grey align-items-center">
        <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
            <div class="input-group bg-grey rounded">
                <div class="input-group-prepend">
                    <span class="input-group-text border-0 bg-additional-grey">
                        <i class="fa fa-search f-13 text-dark-grey"></i>
                    </span>
                </div>
                <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                    placeholder="@lang('app.startTyping')">
            </div>
        </form>
    </div>
    <!-- SEARCH BY TASK END -->

    <!-- RESET START -->
    <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
        <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
            @lang('app.clearFilters')
        </x-forms.button-secondary>
    </div>
    <!-- RESET END -->

    <!-- MORE FILTERS START -->
    <x-filters.more-filter-box>

        <div class="more-filter-items">
            <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.dateFilterOn')</label>
            <div class="select-filter mb-4">
                <select class="form-control select-picker" name="date_filter_on" id="date_filter_on">
                    <option value="created_at">@lang('app.createdOn')</option>
                    <option value="updated_at">@lang('app.updatedOn')</option>
                </select>
            </div>
        </div>
        <div class="more-filter-items">
            <label class="f-14 text-dark-grey mb-12 " for="min">@lang('app.deal') @lang('app.value')</label>
            <div class="select-filter mb-4">
                <div class="select-status d-flex">
                    <input type="number" class="position-relative text-dark form-control border-5 p-2 text-left f-14 f-w-500 border-additional-grey" placeholder="@lang('placeholders.min')" id="min" name="min" min="0">
                    <span class="p-2 m-2">@lang('app.to')</span>
                    <input type="number" class="position-relative text-dark form-control border-5 p-2 text-left f-14 f-w-500 border-additional-grey" placeholder="@lang('placeholders.max')" id="max" name="max" min="0">
                </div>
            </div>
        </div>

        <div class="more-filter-items">
            @if(!isset($viewStageFilter))
            <div class="more-filter-items  ">
                <label class="f-14 text-dark-grey mb-12  " for="usr">@lang('modules.deal.leadStage')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="filter_status_id" data-live-search="true" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($stages as $sts)
                                <option data-content="<span class='fa fa-circle text-red' style='color: {{ $sts->label_color }}'></span> {{ $sts->name }}" value="{{ $sts->id }}">{{ $sts->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @endif

            <div class="more-filter-items  ">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('modules.tickets.agent')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="agent_id" id="agent_id" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($leadAgents as $agent)
                                <option
                                    data-content="<div class='d-inline-block mr-1'><img class='taskEmployeeImg rounded-circle' src='{{ $agent->user->image_url }}' ></div> {{ $agent->user->name }}"
                                    value="{{ $agent->user->id }}">
                                    {{ $agent->user->name . ' [' . $agent->user->email . ']' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items  ">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.dealWatcher')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="deal_watcher_agent_id" id="deal_watcher_agent_id" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($dealWatcher as $agent)
                                <x-user-option :user="$agent" />
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items  ">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.lead')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="lead_agent_id" id="lead_agent_id" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($dealLeads as $agent)
                                <option
                                    data-content="<div class='d-inline-block mr-1'><img class='taskEmployeeImg rounded-circle' src='{{ $agent->image_url }}' ></div> {{ $agent->client_name }}"
                                    value="{{ $agent->id }}">
                                    {{ $agent->name . ' [' . $agent->email . ']' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

    </x-filters.more-filter-box>
    <!-- MORE FILTERS END -->


</x-filters.filter-box>

@push('scripts')
    <script>
        $('#type, #pipeline, #category,#product, #filter_category_id, #filter_source_id, #filter_status_id, #date_filter_on, #min, #max, #agent_id, #deal_watcher_agent_id, #lead_agent_id')
            .on('change keyup', function() {

                if ($('#type').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#category').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#product').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#min').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#max').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#filter_category_id').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#filter_status_id').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#deal_watcher_agent_id').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#lead_agent_id').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#filter_source_id').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#date_filter_on').val() != "created_at") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else {
                    $('#reset-filters').addClass('d-none');
                    showTable();
                }
                getStages ();
            });

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }
        });

        $('#reset-filters,#reset-filters-2').click(function() {
            $('#filter-form')[0].reset();

            $('.filter-box #status').val('not finished');
            $('.filter-box #date_filter_on').val('created_at');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        @if(!isset($viewStageFilter))
        $('.pipelineFilter').change(function (e) {

            let pipelineId = $(this).val();

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
                            var selectData = '';
                            selectData = '<option value="' + value.id + '">' + value
                                .name + '</option>';
                            options.push(selectData);
                        });

                        $('#filter_status_id').html('<option value="all">@lang('modules.lead.all')</option>' +
                            options);
                        $('#filter_status_id').selectpicker('refresh');
                    }
                }
            })

        });
        @endif

            function getStages () {

                if ( $( "#change-stage-action" ).length ) {

                    var pipelineId = $('#pipeline').val();
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
                                    var selectData = '';
                                    var contect = `data-content="<i class='fa fa-circle' style='color:`+value.label_color+`'></i> `+value.name+`"`;
                                    selectData = `<option `+contect+` value="` + value.id + `">' + value
                                        .name + '</option>`;
                                    options.push(selectData);
                                });

                                $('#change-stage-action').html(options);
                                $('#change-stage-action').selectpicker('refresh');
                            }
                        }
                    });

                }
            }


    </script>
@endpush
