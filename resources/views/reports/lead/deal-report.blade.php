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
            <td>{{__('app.months.'.$dealData['month'])}}</td>
            <td>{{$dealData['deals_closed']}}</td>
            <td>{{ $dealData['total_deal_amount'] ? currency_format($dealData['total_deal_amount'], company()->currencyId) : 0}}</td>
            <td>{{$dealData['average_deal_amount'] ? currency_format($dealData['average_deal_amount'], company()->currencyId) : 0}}</td>
            <td>{{$dealData['won_deals'] }}</td>
            <td>{{$dealData['deals_won_amount'] ? currency_format($dealData['deals_won_amount'], company()->currencyId) : 0}}</td>
            <td>{{$dealData['lost_deals']}}</td>
            <td>{{$dealData['deals_lost_amount'] ? currency_format($dealData['deals_lost_amount'], company()->currencyId) : 0}}</td>
            <td>{{$dealData['other_stages']}}</td>
            <td>{{$dealData['other_stages_value'] ? currency_format($dealData['other_stages_value'], company()->currencyId) : 0}}</td>
        </tr>
    @endforeach
</x-table>
