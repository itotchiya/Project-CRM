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

    <!-- CLIENT START -->
    <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
        <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('modules.invoices.type')</p>
        <div class="select-status">
            <select class="form-control select-picker" name="type" id="type">
                <option value="all">@lang('modules.lead.all')</option>
                <option {{ request('type') == 'lead' ? 'selected' : '' }} value="lead">@lang('modules.lead.lead')
                </option>
                <option {{ request('type') == 'client' ? 'selected' : '' }} value="client">
                    @lang('modules.lead.client')</option>
            </select>
        </div>
    </div>
    <!-- CLIENT END -->

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
            <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('modules.lead.leadSource')</label>
            <div class="select-filter mb-4">
                <div class="select-others">
                    <select class="form-control select-picker" id="filter_source_id" data-live-search="true" data-container="body" data-size="8">
                        <option value="all">@lang('app.all')</option>
                        @foreach ($sources as $source)
                            <option value="{{ $source->id }}">{{ $source->type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="more-filter-items">
            <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('modules.lead.leadOwner')</label>
            <div class="select-filter mb-4">
                <div class="select-others">
                    <select class="form-control select-picker" id="filter_owner_id" data-live-search="true" data-container="body" data-size="8">
                        <option value="all">@lang('app.all')</option>
                        @foreach ($employees as $item)
                            <x-user-option :user="$item" />
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="more-filter-items">
            <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.addedBy')</label>
            <div class="select-filter mb-4">
                <div class="select-others">
                <select class="form-control select-picker" id="filter_addedBy" data-live-search="true" data-container="body" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($employees as $item)
                        <x-user-option :user="$item"  />
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
        $('#type, #followUp, #agent_id, #filter_source_id, #filter_owner_id, #filter_status_id, #date_filter_on, #min, #max, #filter_addedBy')
            .on('change keyup', function() {
                if ($('#type').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#min').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#max').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#filter_source_id').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#filter_owner_id').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#date_filter_on').val() != "created_at") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#filter_addedBy').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else {
                    $('#reset-filters').addClass('d-none');
                    showTable();
                }
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

    </script>
@endpush
