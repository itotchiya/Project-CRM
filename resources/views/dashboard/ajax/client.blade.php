<script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
<script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>
<style>
    .pipeline {
        width: 20% !important;
    }
</style>

<div class="row">
    @if (in_array('clients', user_modules()) && in_array('total_clients', $activeWidgets))
        <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
            <a href="javascript:;" id="totalClients">
                <x-cards.widget :title="__('modules.dashboard.totalClients')" :value="$totalClient" icon="users"/>
            </a>
        </div>
    @endif

    @if (in_array('leads', user_modules()) && in_array('total_leads', $activeWidgets))
        <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
            <a href="javascript:;" id="totalLeads">
                <x-cards.widget :title="__('modules.dashboard.totalLeads')" :value="$totalLead" icon="users"/>
            </a>
        </div>
    @endif

    @if (in_array('leads', user_modules()) && in_array('total_deals', $activeWidgets))
        <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
            <a href="javascript:;" id="totalDeals">
                <x-cards.widget :title="__('modules.deal.totalDeals')" :value="$totalDeals" icon="file-contract"
                                :info="__('messages.totalDealWidget')"/>
            </a>
        </div>
    @endif

    @if (in_array('leads', user_modules()) && in_array('total_lead_conversions', $activeWidgets))
        <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
            <a href="javascript:;" id="totalLeadConversions">
                <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center">
                    <div class="d-block ">
                        <h5 class="f-15 f-w-500 mb-20 text-darkest-grey"> @lang('modules.deal.dealConversions')
                            <i class="fa fa-question-circle" data-toggle="popover" data-placement="top"
                               data-content="@lang('messages.leadConversion')" data-html="true"
                               data-trigger="hover"></i>
                        </h5>
                        <div class="d-flex">
                            <p class="mb-0 f-15 font-weight-bold text-blue text-primary d-grid"><span
                                    id="total_lead_conversions">{{ $convertedDeals }}/{{ $totalLeadConversions->count() }}  <span
                                        class="text-dark-grey f-11 text-wrap ql-editor p-0"> {{ $convertDealPercentage }}%</span></span>
                            </p>
                        </div>
                    </div>
                    <div class="d-block">
                        <i class="fa fa-check text-lightest f-18"></i>
                    </div>
                </div>
            </a>
        </div>
    @endif

    @if (in_array('contracts', user_modules()) && in_array('total_contracts_generated', $activeWidgets))
        <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
            <a href="javascript:;" id="totalContractsGenerated">
                <x-cards.widget :title="__('modules.dashboard.totalContractsGenerated')"
                                :value="$totalContractsGenerated" icon="file-contract"/>
            </a>
        </div>
    @endif

    @if (in_array('contracts', user_modules()) && in_array('total_contracts_signed', $activeWidgets))
        <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
            <a href="javascript:;" id="totalContractsSigned">
                <x-cards.widget :title="__('modules.dashboard.totalContractsSigned')" :value="$totalContractsSigned"
                                icon="file-signature"/>
            </a>
        </div>
    @endif

</div>

<div class="row">
    @if (in_array('payments', user_modules()) && in_array('client_wise_earnings', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-3">
            <x-cards.data
                :title="__('modules.dashboard.clientWiseEarnings').' <i class=\'fa fa-question-circle\' data-toggle=\'popover\' data-placement=\'top\' data-content=\''.__('messages.earningChartNote').'\' data-trigger=\'hover\'></i>'">
                <x-bar-chart id="task-chart1" :chartData="$clientEarningChart" height="300"/>
            </x-cards.data>
        </div>
    @endif

    @if (in_array('timelogs', user_modules()) && in_array('client_wise_timelogs', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-3">
            <x-cards.data :title="__('modules.dashboard.clientWiseTimelogs')">
                <x-line-chart id="task-chart2" :chartData="$clientTimelogChart" height="300"/>
            </x-cards.data>
        </div>
    @endif

    @if (in_array('leads', user_modules()) && in_array('lead_vs_status', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-3">
            <div class="card bg-white border-0 b-shadow-4" id="lead_vs_status">

                <x-cards.card-header>
                    @lang('modules.deal.dealVsStatus')
                    <x-slot name="action">
                        <select name="pipeline" id="pipeline" class="form-control pipeline select-picker w-30 mw-30">
                            @foreach ($leadPipelines as $pipeline)
                                <option @if($pipeline->default == 1) selected @endif  value="{{ $pipeline->id }}">
                                    {{ $pipeline->name }}</option>
                            @endforeach
                        </select>
                    </x-slot>

                </x-cards.card-header>

                <div class="card-body p-0" id="leadStageData">
                    @if (array_sum($leadStatusChart['values']) > 0)
                        <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen" data-chart-id="task-chart3" data-chart-data="{{ json_encode($leadStatusChart) }}"><i class="fas fa-expand float-right mr-3"></i></a>
                    @endif
                    <x-pie-chart id="task-chart3" :labels="$leadStatusChart['labels']"
                                 :values="$leadStatusChart['values']"
                                 :colors="$leadStatusChart['colors']" height="310" width="300"/>
                </div>

            </div>

        </div>
    @endif

    @if (in_array('leads', user_modules()) && in_array('lead_vs_source', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-3">
            <x-cards.data :title="__('modules.dashboard.leadVsSource')">
                @if (array_sum($leadSourceChart['values']) > 0)
                    <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen" data-chart-id="task-chart4" data-chart-data="{{ json_encode($leadSourceChart) }}"><i class="fas fa-expand float-right"></i></a>
                @endif
                <x-pie-chart id="task-chart4" :labels="$leadSourceChart['labels']" :values="$leadSourceChart['values']"
                             :colors="$leadSourceChart['colors']" height="300" width="300"/>
            </x-cards.data>
        </div>
    @endif

    @if (in_array('clients', user_modules()) && in_array('latest_client', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-3">
            <x-cards.data :title="__('modules.dashboard.latestClient')" padding="false" otherClasses="h-200">
                <x-table class="border-0 pb-3 admin-dash-table table-hover">
                    <x-slot name="thead">
                        <th class="pl-20">@lang('app.client')</th>
                        <th>@lang('app.email')</th>
                        <th class="pr-20 text-right">@lang('app.createdOn')</th>
                    </x-slot>
                    @forelse ($latestClient->users as $item)
                        <tr>
                            <td class="pl-20">
                                <x-client :user="$item"/>
                            </td>
                            <td>
                                {{ $item->email }}
                            </td>
                            <td class="pr-20"
                                align="right">{{ $item->created_at->translatedFormat(company()->date_format) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="shadow-none">
                                <x-cards.no-record icon="ticket-alt" :message="__('messages.noRecordFound')"/>
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </x-cards.data>
        </div>
    @endif

    @if (in_array('clients', user_modules()) && in_array('recent_login_activities', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-3">
            <x-cards.data :title="__('modules.dashboard.recentLoginActivities')" padding="false" otherClasses="h-200">
                <x-table class="border-0 pb-3 admin-dash-table table-hover">

                    <x-slot name="thead">
                        <th class="pl-20">@lang('app.client')</th>
                        <th>@lang('app.email')</th>
                        <th class="pr-20 text-right">@lang('app.lastLogin')</th>
                    </x-slot>
                    @forelse ($recentLoginActivities->users as $item)
                        <tr>
                            <td class="pl-20">
                                <x-client :user="$item"/>
                            </td>
                            <td>
                                {{ $item->email }}
                            </td>
                            <td align="right" class="pr-20">
                                {{ $item->last_login ? $item->last_login->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) : '--' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="shadow-none">
                                <x-cards.no-record icon="ticket-alt" :message="__('messages.noRecordFound')"/>
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </x-cards.data>
        </div>
    @endif

</div>

<script>
    $('#save-dashboard-widget').click(function () {
        $.easyAjax({
            url: "{{ route('dashboard.widget', 'admin-client-dashboard') }}",
            container: '#dashboardWidgetForm',
            blockUI: true,
            type: "POST",
            redirect: true,
            data: $('#dashboardWidgetForm').serialize(),
            success: function () {
                window.location.reload();
            }
        })
    });


    $('#totalClients').click(function () {
        var dateRange = getDateRange();
        var url = `{{ route('clients.index') }}`;

        string = `?start=${dateRange.startDate}&end=${dateRange.endDate}`;
        url += string;

        window.location.href = url;
    });

    $('#totalLeads').click(function () {
        var dateRange = getDateRange();
        var url = `{{ route('lead-contact.index') }}`;

        string = `?start=${dateRange.startDate}&end=${dateRange.endDate}`;
        url += string;

        window.location.href = url;
    });

    $('#totalLeadConversions').click(function () {
        var dateRange = getDateRange();
        var url = `{{ route('deals.index') }}`;

        string = `?start=${dateRange.startDate}&end=${dateRange.endDate}`;
        url += string;

        window.location.href = url;
    });

    $('#totalDeals').click(function () {
        var url = `{{ route('deals.index') }}`;
        window.location.href = url;
    });

    $('#totalContractsGenerated').click(function () {
        var dateRange = getDateRange();
        var url = `{{ route('contracts.index') }}`;

        string = `?start=${dateRange.startDate}&end=${dateRange.endDate}`;
        url += string;

        window.location.href = url;
    });

    $('#totalContractsSigned').click(function () {
        var dateRange = getDateRange();
        var url = `{{ route('contracts.index') }}`;

        string = `?signed=yes&start=${dateRange.startDate}&end=${dateRange.endDate}`;
        url += string;

        window.location.href = url;
    });

    function getDateRange() {
        var dateRange = $('#datatableRange2').data('daterangepicker');
        var startDate = dateRange.startDate.format('{{ company()->moment_date_format }}');
        var endDate = dateRange.endDate.format('{{ company()->moment_date_format }}');

        startDate = encodeURIComponent(startDate);
        endDate = encodeURIComponent(endDate);
        var pipelineId = $('#pipeline').val();

        var data = [];
        data['startDate'] = startDate;
        data['endDate'] = endDate;
        data['pipelineId'] = pipelineId;

        return data;
    }

    $('#pipeline').on('change keyup', function () {
        var dateRange = $('#datatableRange2').data('daterangepicker');
        var startDate = dateRange.startDate.format('{{ company()->moment_date_format }}');
        var endDate = dateRange.endDate.format('{{ company()->moment_date_format }}');

        startDate = encodeURIComponent(startDate);
        endDate = encodeURIComponent(endDate);

        var pipelineId = $('#pipeline').val();
        var url = "{{ route('dashboard.deal-stage-data', ':id') }}?startDate=" + startDate + "&endDate=" + endDate;
        url = url.replace(':id', pipelineId);

        $.easyAjax({

            url: url,
            container: '#dashboardWidgetForm',
            blockUI: true,
            type: "GET",
            redirect: true,
            data: $('#dashboardWidgetForm').serialize(),
            success: function (response) {
                $('#leadStageData').html(response.html);
            }
        })
    });
</script>
