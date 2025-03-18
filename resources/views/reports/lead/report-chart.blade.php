@extends('layouts.app')

@section('filter-section')
    <div class="d-flex filter-box project-header bg-white border-bottom">

        <div class="mobile-close-overlay w-100 h-100" id="close-client-overlay"></div>
        <div class="project-menu d-lg-flex" id="mob-client-detail">
            <a class="d-none close-it" href="javascript:;" id="close-client-detail">
                <i class="fa fa-times"></i>
            </a>
            <x-tab :href="route('lead-report.index') . '?tab=profile'" :text="__('modules.deal.profile')" class="profile" />
            <x-tab :href="route('lead-report.chart') . '?tab=chart'" :text="__('modules.leadContact.leadReport')" class="chart" />


        </div>
    </div>

    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('modules.deal.pipeline')</p>
            <div class="select-year">
                <select class="form-control select-picker" name="pipeline" id="pipeline" data-live-search="true"
                    data-size="8">
                    @foreach ($pipelines as $pipeline)
                        <option value="{{ $pipeline->id }}" @if ($pipeline->default == 1) selected @endif>
                            {{ $pipeline->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('modules.deal.category')</p>
            <div class="select-year">
                <select class="form-control select-picker" name="category" id="category" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.year')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="year" id="year" data-live-search="true"
                    data-size="8">
                    @foreach ($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex flex-column w-tables rounded mt-4 bg-white">
            <div id="chartContainer"></div>
        </div>
        <div id="table-actions" class="flex-grow-1 align-items-center mt-4">

            @if (canDataTableExport())
                <x-forms.button-secondary id="export-report" class="mr-3 mb-2 mb-lg-0" icon="file-export">
                    @lang('app.exportExcel')
                </x-forms.button-secondary>
            @endif
        </div>
        <div class="d-flex flex-column w-tables rounded mt-4 bg-white">
            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
                <div class="table-responsive" id="deal-report">
                    <x-table class="table-bordered">
                        <x-slot name="thead">
                            <th>@lang('app.month') </th>
                            <th>@lang('modules.deal.dealsToBeClosed')</th>
                            <th>@lang('modules.deal.totalDealAmount')</th>
                            <th>@lang('modules.deal.averageDealValue')</th>
                            <th>@lang('modules.deal.wonDeals')</th>
                            <th>@lang('modules.deal.dealsWonValue')</th>
                            <th>@lang('modules.deal.lostDeals')</th>
                            <th>@lang('modules.deal.dealsLostValue')</th>
                            <th>@lang('modules.deal.otherDealStages')</th>
                            <th>@lang('modules.deal.otherDealStagesValue')</th>
                        </x-slot>
                        @foreach ($dealDatas as $dealData)
                            <tr>
                                <td>{{ __('app.months.' . $dealData['month']) }}</td>
                                <td>{{ $dealData['deals_closed'] }}</td>
                                <td>{{ $dealData['total_deal_amount'] ? currency_format($dealData['total_deal_amount'], company()->currencyId) : 0 }}</td>
                                <td>{{ $dealData['average_deal_amount'] ? currency_format($dealData['average_deal_amount'], company()->currencyId) : 0 }}</td>
                                <td>{{ $dealData['won_deals'] }}</td>
                                <td>{{ $dealData['deals_won_amount'] ? currency_format($dealData['deals_won_amount'], company()->currencyId) : 0 }}</td>
                                <td>{{ $dealData['lost_deals'] }}</td>
                                <td>{{ $dealData['deals_lost_amount'] ? currency_format($dealData['deals_lost_amount'], company()->currencyId) : 0 }}</td>
                                <td>{{ $dealData['other_stages'] }}</td>
                                <td>{{ $dealData['other_stages_value'] ? currency_format($dealData['other_stages_value'], company()->currencyId) : 0 }}</td>
                            </tr>
                        @endforeach
                    </x-table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/graph/frappechart.js') }}"></script>
    <script>
        var currencyCode = "{{ company()->currency->currency_code }}"

        const activeTab = "{{ $activeTab }}";
        $('.project-menu .' + activeTab).addClass('active');

        $(document).ready(function() {

            var datasetsData = @json($datasets);
            chart(datasetsData);

            function chart(datasetsData) {

                var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov",
                    "Dec"
                ];

                var datasets = [];

                const chart = new frappe.Chart("#chartContainer", {
                    data: {
                        labels: monthNames,
                        datasets: datasetsData.map(function(dataset) {
                            return {
                                name: dataset.name.substr(0, 12),
                                values: dataset.values,
                                chartType: dataset.chartType,
                                color: dataset.color || '#d4f542'
                            };
                        })
                    },
                    title: "Monthly Values by Stage",
                    type: "axis-mixed",
                    height: 300,
                    axisOptions: {
                        yAxisMode: 'tick',
                        xAxisMode: 'tick',
                        xIsSeries: 0
                    },
                    barOptions: {
                        stacked: true,
                        spaceRatio: 0.5
                    },
                    tooltipOptions: {
                        formatTooltipX: (d) => (d + "").toUpperCase(),
                        formatTooltipY: (d) => d + ' ' + currencyCode
                    }
                });

            }


            $('#pipeline, #year, #category').on('change', function() {
                var year = $('#year').val();
                var pipeline = $('#pipeline').val();
                var category = $('#category').val();
                if (category == 'all') {
                    category = null;
                }

                $.easyAjax({
                    url: "{{ route('lead-report.chart') }}",
                    type: "GET",
                    data: {
                        year: year,
                        pipeline: pipeline,
                        category: category
                    },
                    success: function(response) {
                        chart(response.datasets);
                        $('#deal-report').html(response.html);
                    },
                });
            });

            function showTable(loading = true) {

                var year = $('#year').val();
                var pipeline = $('#pipeline').val();
                var category = $('#category').val();
                if (category == 'all') {
                    category = null;
                }

                $.easyAjax({
                    url: "{{ route('lead-report.chart') }}",
                    type: "GET",
                    data: {
                        year: year,
                        pipeline: pipeline,
                        category: category
                    },
                    success: function(response) {
                        chart(response.datasets);
                        $('#deal-report').html(response.html);
                    },
                });

            }

            $('#pipeline, #year, #category').on('change keyup',
                function() {
                    if ($('#pipeline').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else if ($('#year').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else if ($('#category').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else {
                        $('#reset-filters').addClass('d-none');
                        showTable();
                    }
                });

            $('#reset-filters').click(function() {
                $('#filter-form')[0].reset();
                $('.filter-box .select-picker').selectpicker("refresh");
                $('#reset-filters').addClass('d-none');
                showTable();
            });

           
        });

        @if (canDataTableExport())
            $('#export-report').click(function() {
                var year = $('#year').val();
                var pipeline = $('#pipeline').val();
                var category = $('#category').val();
                if (category == 'all') {
                    category = null;
                }
                var url =
                    "{{ route('deal-report.export', [':year', ':pipeline', ':category']) }}";
                url = url.replace(':year', year).replace(':pipeline', pipeline).replace(':category', category);
                window.location.href = url;

            });
        @endif
    </script>
@endpush
