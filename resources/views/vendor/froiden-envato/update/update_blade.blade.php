<style>
    .note {
        margin-bottom: 15px;
        padding: 15px;
        background-color: #e7f3fe;
        border-left: 6px solid #2196F3;
    }

    ul,
    li {
        list-style: inherit;
        line-height: 20px;
    }

    .note ul {
        margin-bottom: 20px;
        margin-top: 2px;
        margin-left: 10px;
    }

    .version-update-heading {
        color: #39bee6;
    }

    .update-summary-title {
        border-bottom: 1px solid black;
        padding-bottom: 8px
    }

</style>
<div class="row">
    <div class="col-sm-12">
        @php($envatoUpdateCompanySetting = \Froiden\Envato\Functions\EnvatoUpdate::companySetting())

        @include('vendor.froiden-envato.update.support-extend-renewal')

        @if (isset($updateVersionInfo['lastVersion']))

            <x-alert type="danger">
                <ol class="mb-0">
                    <li>@lang('messages.updateAlert')</li>
                    <li>@lang('messages.updateBackupNotice')</li>
                </ol>
            </x-alert>

            <div id="update-area" class="mt-20 mb-20 col-md-12 white-box d-none">
                {{__('app.loading')}}
            </div>

            <div class="note alert alert-primary">
                <div class="row p-20" style="line-height: 22px">
                    <div class="col-md-8">
                        <h6 class="f-24">
                            <i class="fa fa-gift f-20"></i> @lang('modules.update.newUpdate') <span
                                class="badge badge-success">{{ $updateVersionInfo['lastVersion'] }}</span>
                        </h6>
                        <div class="mt-3"><span class="font-weight-bold text-red">Note:</span> You will get
                            logged
                            out after update. Login again to use the application.
                        </div>
                        <div class="font-12 mt-3">@lang('modules.update.updateAlternate')</div>
                    </div>
                    <div class="col-md-4 text-right mt-3">
                        <x-forms.link-primary id="update-app" link="javascript:;" icon="download">
                            @lang('modules.update.updateNow')</x-forms.link-primary>
                    </div>
                </div>

                <div class="col-md-12 mt-5">
                    <h6 class="update-summary-title"><i class="fa fa-history f-20"></i> Update Summary</h6>
                    <div>{!! $updateVersionInfo['updateInfo'] !!}</div>
                </div>
            </div>

        @else
            <x-alert type="info" icon="info-circle">
                You have the latest version of this app.
            </x-alert>
        @endif
    </div>
</div>
