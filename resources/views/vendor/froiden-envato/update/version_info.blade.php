
@php($envatoUpdateCompanySetting = \Froiden\Envato\Functions\EnvatoUpdate::companySetting())

<div class="table-responsive">

    <table class="table table-bordered">
        <thead>
        <th width="25%">@lang('modules.update.systemDetails')</th>
        <th></th>
        </thead>
        <tbody>
        <tr>
            <td>App Version</td>
            <td class="text-left">{{ $updateVersionInfo['appVersion'] }}
                @if(!isset($updateVersionInfo['lastVersion']))
                    <i class="fa fa-check-circle text-success"></i>
                @endif
            </td>
        </tr>

        @if(!app()->environment(['codecanyon','demo']))
            <tr>
                <td>App Environment</td>
                <td>{{ app()->environment() }}
                    @if(!isset($updateVersionInfo['lastVersion']))
                        <i class="fa fa-warning text-danger" title="Change the environment back to <b>codecanyon</b>"
                           data-toggle="tooltip" data-html="true"></i>
                    @endif
                </td>
            </tr>
        @endif

        <tr>
            <td>Laravel Version</td>
            <td>{{ $updateVersionInfo['laravelVersion'] }}</td>
        </tr>

        <td>PHP Version</td>
        <td>
            {{ phpversion() }}
            @if (version_compare(PHP_VERSION, '8.2.0') >= 0)
                 <i class="fa fa-check-circle text-success"></i>
            @else
                <i data-toggle="tooltip" data-original-title="@lang('messages.phpUpdateRequired')" class="fa fa-warning text-danger"></i>
            @endif
        </td>

        @if(!is_null($mysql_version))
            <tr>
                <td>{{ $databaseType }}</td>
                <td>
                    {{ $mysql_version}}
                </td>
            </tr>
        @endif

        </tbody>
    </table>
    @if(!is_null($envatoUpdateCompanySetting->purchase_code))
    <table class="table table-bordered">
        <thead>
        <th width="25%">License Details</th>
        <th></th>
        </thead>
        <tbody>


            <tr>
                <td>Envato Purchase code</td>
                <td>
                    <span class="blur-code purchase-code">{{$envatoUpdateCompanySetting->purchase_code}} </span>
                    <span class="show-hide-purchase-code" data-toggle="tooltip"
                          data-original-title="{{__('messages.showHidePurchaseCode')}}">
                       <i class="icon far fa-eye-slash fa-fw cursor-pointer"></i>
                    </span>
                    <a href="{{route('verify-purchase')}}">Change Purchase Code</a>
                </td>
            </tr>
            @if(!is_null($envatoUpdateCompanySetting?->purchased_on))
                <tr>
                    <td>Purchased On</td>
                    <td>
                        <span>{{\Carbon\Carbon::parse($envatoUpdateCompanySetting->purchased_on)->translatedFormat('d M, Y')}} <small class="f-12 text-muted"> ({{\Carbon\Carbon::parse($envatoUpdateCompanySetting->purchased_on)->diffForHumans()}})</small></span>
                    </td>
                </tr>
            @endif
            @if(!is_null($envatoUpdateCompanySetting?->supported_until))
                <tr>
                    <td>Support Expire</td>
                    <td>
                        <span>{{\Carbon\Carbon::parse($envatoUpdateCompanySetting->supported_until)->translatedFormat('d M, Y')}} <small class="f-12 text-muted"> ({{\Carbon\Carbon::parse($envatoUpdateCompanySetting->supported_until)->diffForHumans()}})</small>
                            @if(\Carbon\Carbon::parse($envatoUpdateCompanySetting->supported_until)->lessThan(now()))
                            <span class="text-danger">Expired</span>
                            @endif
                        </span>
                    </td>
                </tr>
            @endif
            @if(!is_null($envatoUpdateCompanySetting->license_type))
                <tr>
                    <td>License Type</td>
                    <td>
                        <span>{{$envatoUpdateCompanySetting->license_type}}
                            @if(str_contains($envatoUpdateCompanySetting->license_type, 'Regular'))
                                <a href="{{'https://codecanyon.net/checkout/from_item/' . config('froiden_envato.envato_item_id') . '?license=extended'}}"
                                   target="_blank">Upgrade now </a>
                            @endif
                        </span>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
    @endif
</div>

