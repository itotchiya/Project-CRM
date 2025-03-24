<a href="{{ $link }}" {{ $attributes->merge(['class' => 'btn btn-primary rounded f-14 p-2']) }}>
    @if ($icon != '')
        <x-lucide-arrow-right class="icon-btn mr-1" />
    @endif
    {{ $slot }}
</a>
