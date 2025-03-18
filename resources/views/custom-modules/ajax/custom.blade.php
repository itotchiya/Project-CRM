<div class="table-responsive p-20">

    <div id="update-area" class="mt-20 mb-20 col-md-12 white-box d-none">
        {{__('app.loading')}}
    </div>
    <div class="alert alert-danger d-none" id="custom-module-alert"></div>

    @includeIf('languagepack::module-activated-alert')

    @include('custom-modules.sections.universal-bundle')

    <x-table class="table-bordered table-hover custom-modules-table" headType="thead-light">
        <x-slot name="thead">
            <th>@lang('app.name')</th>
            @if (!$universalBundle)
            <th>@lang('app.purchaseCode')</th>
            @endif
            <th>@lang('app.moduleVersion')</th>
            @if (!$universalBundle)
            <th class="text-right">@lang('app.notify')</th>
            @endif
            <th class="text-right">@lang('app.status')</th>
        </x-slot>

        @forelse ($allModules as $key=>$module)
        @php
            $fetchSetting = null;
            if (in_array($module, $worksuitePlugins) && config(strtolower($module) . '.setting'))
            {
                $fetchSetting = config(strtolower($module) . '.setting')::first();
            }
        @endphp
            <tr>
                <td><span>{{ $key }}</span>
                    @if (module_enabled('UniversalBundle') && isInstallFromUniversalBundleModule($key))
                            <i class="icon text-info fas fa-info-circle cursor-pointer" data-toggle="tooltip"
                              data-original-title="{{__('universalbundle::app.moduleInfo')}}"></i>
                    @else
{{--                        @if ($fetchSetting?->purchase_code && $fetchSetting?->supported_until)--}}
{{--                            <i class="icon text-info fas fa-info-circle cursor-pointer"--}}
{{--                            data-toggle="popover" data-placement="top" data-html="true" data-trigger="hover"--}}
{{--                            data-content="@include('custom-modules.sections.support-date')"></i>--}}
{{--                        @endif--}}
                    @endif
{{--                    @if ($fetchSetting?->license_type && !(module_enabled('UniversalBundle') && isInstallFromUniversalBundleModule($key)))--}}
{{--                        <span class="ml-2 badge badge-secondary">{{ $fetchSetting->license_type }}</span>--}}
{{--                        @if(str_contains($fetchSetting->license_type, 'Regular'))--}}
{{--                            <a href="{{ \Froiden\Envato\Helpers\FroidenApp::buyExtendedUrl(config(strtolower($module) . '.envato_item_id')) }}"--}}
{{--                            target="_blank">Upgrade now</a>--}}
{{--                        @endif--}}
{{--                    @endif--}}
                </td>
                @if (!$universalBundle)
                <td>
                    @if ($fetchSetting)
                        @if (config(strtolower($module) . '.verification_required'))
                            @include('custom-modules.sections.purchase-code')
                        @endif
                    @endif
                </td>
                @endif
                <td>
                    @if (config(strtolower($module) . '.setting'))
                        @include('custom-modules.sections.version')

                        @if ($plugins->where('envato_id', config(strtolower($module) . '.envato_item_id'))->first() && !(module_enabled('UniversalBundle') && isInstallFromUniversalBundleModule($key)))
                            @include('custom-modules.sections.module-update')
                        @endif
                    @endif

                </td>

                @if (!$universalBundle)
                <td class="text-right">
                    @if ($fetchSetting)
                    <div class="custom-control custom-switch ml-2 d-inline-block"  data-toggle="tooltip"
                         data-original-title="@lang('app.moduleNotifySwitchMessage', ['name' => $module])">
                        <input type="checkbox" class="custom-control-input change-module-notification"
                                @checked($fetchSetting->notify_update)
                               id="module-notification-{{ $key }}" data-module-name="{{ $module }}">
                        <label class="custom-control-label cursor-pointer" for="module-notification-{{ $key }}"></label>
                    </div>
                    @endif
                </td>
                @endif

                <td class="text-right">
                    <div class="custom-control custom-switch ml-2 d-inline-block"  data-toggle="tooltip"
                         data-original-title="@lang('app.moduleSwitchMessage', ['name' => $module])">
                        <input type="checkbox" @if (in_array($module, $worksuitePlugins)) checked
                               @endif class="custom-control-input change-module-status"
                               id="module-{{ $key }}" data-module-name="{{ $module }}">
                        <label class="custom-control-label cursor-pointer" for="module-{{ $key }}"></label>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">
                    <x-cards.no-record icon="calendar" :message="__('messages.noRecordFound')"/>
                </td>
            </tr>
        @endforelse

    </x-table>

    @include('vendor.froiden-envato.update.plugins', ['allModules' => $allModules])
</div>

<script>
    $('body').on('change', '.change-module-status', function () {
        let moduleStatus;
        const module = $(this).data('module-name');

        if ($(this).is(':checked')) {
            moduleStatus = 'active';
        } else {
            moduleStatus = 'inactive';
        }

        let url = "{{ route('custom-modules.update', ':module') }}";
        url = url.replace(':module', module);

        $('#custom-module-alert').addClass('d-none');

        $.easyAjax({
            url: url,
            type: "POST",
            disableButton: true,
            buttonSelector: ".change-module-status",
            container: '.custom-modules-table',
            blockUI: true,
            data: {
                'id': module,
                'status': moduleStatus,
                '_method': 'PUT',
                '_token': '{{ csrf_token() }}'
            },
            error: function (response) {
                if (response.responseJSON) {
                    $('#custom-module-alert').html(response.responseJSON.message).removeClass('d-none');
                    $('#module-' + module).prop('checked', false);
                }

            }
        });
    });

    $('body').on('click', '.verify-module', function () {
        const module = $(this).data('module');
        let url = "{{ route('custom-modules.show', ':module') }}";
        url = url.replace(':module', module);
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

</script>
@includeIf('vendor.froiden-envato.update.update_module')
