@component('mail::message')
# {{ \Carbon\Carbon::parse('01-' . $month . '-' . now()->year)->translatedFormat('F-Y') }} @lang('app.menu.attendanceReport')

@component('mail::text', ['text' => __('email.attendanceReport.text')])

@endcomponent

@lang('email.regards'),<br>
{{ config('app.name') }}
@endcomponent
