@if ($file->icon == 'images')
    <a class="img-lightbox taskView" data-image-url="{{ $file->file_url }}" href="javascript:;">
        @lang('app.view')
    </a>
@else
    <a class="taskView" href="{{ $file->file_url }}" target="_blank">
        @lang('app.view')
    </a>
@endif
