@if (global_setting()->system_update == 1 &&  in_array('admin', user_roles()))

    @php
        $universal = \Nwidart\Modules\Facades\Module::find('UniversalBundle');
        $plugins = \Froiden\Envato\Functions\EnvatoUpdate::plugins();
        $versionArray = [];

      foreach ($plugins as $value) {
           $versionArray[$value['envato_id']] = $value['version'];
      }

      $version = $versionArray;
    @endphp

    @if ($universal && config(strtolower($universal) . '.envato_item_id'))
      @if ($version[config(strtolower($universal) . '.envato_item_id')] > \Illuminate\Support\Facades\File::get($universal->getPath() . '/version.txt') && (config(strtolower($universal) . '.setting')::first()?->notify_update))
        <div class="col-md-12">
            <x-alert type="info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa fa-gift"></i> @lang('app.newModuleUpdateMessage', ['name' => $universal->getName()])
                        <span
                            class="badge badge-success">{{ $version[config(strtolower($universal) . '.envato_item_id')] }}</span>
                    </div>
                    <div>
                        <x-forms.link-primary :link="route('custom-modules.index').'?tab=custom'"
                                              icon="arrow-right">
                            @lang('modules.update.updateNow')
                        </x-forms.link-primary>
                    </div>

                </div>
            </x-alert>
        </div>
      @endif
    @else

        @php
            $allModules = \Nwidart\Modules\Facades\Module::allEnabled();

        @endphp
        @foreach($allModules as $key=>$module)

            @if (config(strtolower($module) . '.envato_item_id') && $version[config(strtolower($module) . '.envato_item_id')] > \Illuminate\Support\Facades\File::get($module->getPath() . '/version.txt') && (config(strtolower($module) . '.setting')::first()?->notify_update))

                <div class="col-md-12">
                    <x-alert type="info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fa fa-gift"></i> @lang('app.newModuleUpdateMessage', ['name' => $module->getName()])
                                <span
                                    class="badge badge-success">{{ $version[config(strtolower($module) . '.envato_item_id')] }}</span>
                            </div>
                            <div>
                                <x-forms.link-primary :link="route('custom-modules.index').'?tab=custom'"
                                                      icon="arrow-right">
                                    @lang('modules.update.updateNow')
                                </x-forms.link-primary>
                            </div>

                        </div>
                    </x-alert>
                </div>
            @endif

        @endforeach
    @endif
@endif
