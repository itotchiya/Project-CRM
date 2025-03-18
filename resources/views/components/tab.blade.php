<a href="{{ $href }}" @if ($ajax == "false") {{ $attributes->merge(['class' => 'text-dark-grey  border-right-grey p-sub-menu']) }}

@else
{{ $attributes->merge(['class' => 'text-dark-grey  border-right-grey p-sub-menu ajax-tab']) }} @endif><span>{{ $text }}</span></a>
