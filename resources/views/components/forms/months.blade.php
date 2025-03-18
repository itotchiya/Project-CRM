@props([
    'selectedMonth' => '',
    'fieldRequired' => false,
    'all'=>false
])

@if(!$fieldRequired)
    <option value="">--</option>
@endif
@if($all)
    <option value="">@lang('app.all')</option>
@endif

@foreach(range(1, \Carbon\Carbon::MONTHS_PER_YEAR) as $monthNumber)
    <option {{ $selectedMonth == $monthNumber ? 'selected' : '' }} value="{{ $monthNumber }}">
        {{ now()->startOfMonth()->month($monthNumber)->translatedFormat('F') }}
    </option>
@endforeach
