@component('mail::message')
# @lang('email.leave.applied')

@component('mail::text', ['text' => $content])

@endcomponent

@if (!empty($url))
    @component('mail::button', ['url' => $url, 'themeColor' => ((!empty($themeColor)) ? $themeColor : '#1f75cb')])
    {{ $actionText }}
    @endcomponent
@endif

@lang('email.regards'),<br>
{{ config('app.name') }}
@endcomponent
