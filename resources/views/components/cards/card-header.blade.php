<div class="card-header bg-white border-0  d-flex justify-content-between pt-4">
    <h4 class="f-18 f-w-500 mb-0">{{ $slot }}</h4>

    @if($action)
        {!! $action !!}
    @endif

</div>
