@component('mail::message')
# @lang('email.hello') {{$name}},
@lang('email.dailyTimelogReport.subject') {{ \Carbon\Carbon::parse($date)->translatedFormat('Y-m-d') }}

@component('mail::text', ['text' => __('email.dailyTimelogReport.text')])

@endcomponent

@lang('email.regards'),<br>
{{ config('app.name') }}
@endcomponent
