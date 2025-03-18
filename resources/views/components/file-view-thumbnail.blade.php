@if ($file->icon == 'images')
    <a class="img-lightbox" data-image-url="{{ $file->file_url }}" href="javascript:;">
        <img src="{{ $file->file_url }}">
    </a>
@else
    <a href="{{ $file->file_url }}" target="_blank">
        <i class="fa {{ $file->icon }} text-lightest"></i>
    </a>
@endif
