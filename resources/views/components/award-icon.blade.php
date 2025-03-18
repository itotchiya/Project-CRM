@props([
    'award' => $award
])

@if(isset($award->awardIcon->icon))
<span class="align-items-center d-inline-flex height-40 justify-content-center rounded width-40" style="background-color: {{ $award->color_code.'20' }};">
    <i class="bi bi-{{ $award->awardIcon->icon }} f-15 text-white appreciation-icon" style="color: {{ $award->color_code }}  !important"></i>
</span>

@endif