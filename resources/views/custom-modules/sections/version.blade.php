@if ($plugins->where('envato_id', config(strtolower($module) . '.envato_item_id'))->first())
    @if ($plugins->where('envato_id', config(strtolower($module) . '.envato_item_id'))->pluck('version')->first() > \Illuminate\Support\Facades\File::get($module->getPath() . '/version.txt'))

        <span class="badge badge-danger" data-toggle="tooltip"
              data-original-title="@lang('app.moduleUpdateMessage', [
                            'name' => $module->getName(),
                            'version' => $plugins->where('envato_id', config(strtolower($module) . '.envato_item_id'))->pluck('version')->first(),
        ])">

            {{ \Illuminate\Support\Facades\File::get($module->getPath() . '/version.txt') }}
        </span>
    @else
        <span class="badge badge-success">
            {{ \Illuminate\Support\Facades\File::get($module->getPath() . '/version.txt') }}
        </span>
    @endif
@else
    <span class="badge badge-success">{{ \Illuminate\Support\Facades\File::get($module->getPath() . '/version.txt') }}</span>
@endif
