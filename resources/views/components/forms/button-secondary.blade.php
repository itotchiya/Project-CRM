<button type="button" @disabled($disabled)
    {{ $attributes->merge(['class' => 'btn-secondary rounded f-14 p-2']) }}>
    @if (!is_null($icon))
        <i class="fa fa-{{ $icon }} mr-1"></i>
    @endif
    {{ $slot }}
</button>
