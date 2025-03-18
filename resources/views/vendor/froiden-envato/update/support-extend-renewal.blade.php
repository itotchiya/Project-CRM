<style>

    .e-btn--3d.-color-primary {
        -webkit-box-shadow: 0 2px 0 #6f9a37;
        box-shadow: 0 2px 0 #6f9a37;
        position: relative;
    }

</style>


<div class="support-div mb-2">
    @if (!is_null($envatoUpdateCompanySetting->supported_until))

        @php
            $expired = Carbon\Carbon::parse($envatoUpdateCompanySetting->supported_until)->isPast();
            $support = Carbon\Carbon::parse($envatoUpdateCompanySetting->supported_until);
        @endphp


        <div @class(['alert-success' => !$expired,'alert-danger' => $expired,'alert'])>
            <div class="row">
                <div class="col-md-8">
                    <h2 class="mb-0 f-21 font-weight-normal ">
                        <strong>Support @if($expired) Expired @endif
                        </strong>
                    </h2>
                </div>
                <div class="col-md-4 text-right">
                    <span class="text-center">
                         @if($expired)
                            {{ $support->diffForHumans(now(),Carbon\CarbonInterface::DIFF_ABSOLUTE) }} ago
                        @else
                            {{ $support->diffForHumans(now(),Carbon\CarbonInterface::DIFF_ABSOLUTE) }} left
                        @endif
                    </span>

                    </div>
                <div class="col-md-12 mt-3">
                    <div class="item-support-extension__row1 mb-2">
                        <div class="item-support-extension__label">
                            @if($expired)
                                <p>Renew support to get help from <a href="https://1.envato.market/froiden" target="_blank">Author</a>
                                    for 6 months</p>
                            @elseif($support->diffInDays() < 90)
                                <p>Get an extra 6 months of support now and save <strong>62.5%</strong> of item price.</p>
                            @endif
                            @include('custom-modules.sections.support-date',['fetchSetting' => $envatoUpdateCompanySetting])
                        </div>
                    </div>


                    @if($expired)
                        <x-forms.link-primary class="mr-2 e-btn--3d -color-primary -size-m -width-full h-mt"
                                              :link="Froiden\Envato\Helpers\FroidenApp::renewSupportUrl(config('froiden_envato.envato_item_id'))"
                                              icon="shopping-cart"
                                              data-toggle="tooltip"
                                              data-original-title="Extend the support of main app now. It will take you to codecanyon website to renew support"
                                              target="_blank">Renew support now
                        </x-forms.link-primary>
                        <x-forms.link-secondary link="javascript:;"
                                                class="e-btn--3d -color-primary -size-m -width-full h-mt"
                                                onclick="getPurchaseData();"
                                                data-toggle="tooltip"
                                                data-original-title="This will fetch the latest support date from codecanyon. Click on this button only when you have renewed the support and the new support date is not reflecting"
                                                icon="sync-alt">Refresh
                        </x-forms.link-secondary>
                    @elseif ($support->diffInDays() < 90)
                        <x-forms.link-primary class="mr-2 e-btn--3d -color-primary -size-m -width-full h-mt"
                                              :link="Froiden\Envato\Helpers\FroidenApp::extendSupportUrl(config('froiden_envato.envato_item_id'))"
                                              target="_blank"
                                              data-toggle="tooltip"
                                              data-original-title="Extend the support of main app now. It will take you to codecanyon website to renew support"
                                              icon="shopping-cart">Extend now and save
                        </x-forms.link-primary>

                        <x-forms.link-secondary link="javascript:;"
                                                class="e-btn--3d -color-primary -size-m -width-full h-mt"
                                                onclick="getPurchaseData();"
                                                data-toggle="tooltip"
                                                data-original-title="This will fetch the latest support date from codecanyon. Click on this button only when you have renewed the support and the new support date is not reflecting"
                                                icon="sync-alt">Refresh
                        </x-forms.link-secondary>

                    @endif
                </div>
            </div>

        </div>
    @endif
</div>

