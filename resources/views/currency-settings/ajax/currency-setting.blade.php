<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-0">
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <x-table class="table-bordered">
                    <x-slot name="thead">
                        <th>@lang('modules.currencySettings.currencyName')</th>
                        <th>@lang('modules.currencySettings.currencySymbol')</th>
                        <th>@lang('modules.currencySettings.currencyCode')</th>
                        <th>@lang('modules.currencySettings.exchangeRate')</th>
                        <th>@lang('modules.accountSettings.currencyFormat')
                            <i class="fa fa-question-circle" data-toggle="tooltip" data-original-title="@lang('modules.accountSettings.currencyFormatSettingTooltip')"></i>
                          </th>
                        <th class="text-right">@lang('app.action')</th>
                    </x-slot>

                    @forelse($currencies as $key => $currency)
                        <tr class="row{{ $currency->id }}">
                            <td>{{ $currency->currency_name }}
                                @if (companyOrGlobalSetting()->currency_id == $currency->id)
                                    <label class='badge badge-primary'>@lang('app.default')</label>
                                @endif
                            </td>
                            <td>{{ $currency->currency_symbol }}</td>
                            <td>{{ $currency->currency_code }}</td>
                            <td><span data-toggle="tooltip" data-original-title="1 {{$currency->currency_code}} = {{$currency->exchange_rate}} {{companyOrGlobalSetting()->currency->currency_code}}">
                                {{ !is_null($currency->exchange_rate) ? $currency->exchange_rate : '--' }}</span> </td>
                            <td> {{ currency_format(1000, $currency->id) }}</td>
                            <td class="text-right">
                                <div class="task_view">
                                    <a class="task_view_more d-flex align-items-center justify-content-center edit-channel" data-currency-id="{{ $currency->id }}" href="javascript:;" >
                                        <i class="fa fa-edit icons mr-2"></i>  @lang('app.edit')
                                    </a>
                                </div>
                                @if (companyOrGlobalSetting()->currency_id != $currency->id)
                                    <div class="task_view mt-1 mt-lg-0 mt-md-0">
                                        <a class="task_view_more d-flex align-items-center justify-content-center delete-table-row" href="javascript:;" data-currency-id="{{ $currency->id }}">
                                            <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-cards.no-record-found-list />
                    @endforelse
                </x-table>

            </div>
        </div>
    </div>
</div>
