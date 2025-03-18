@component('mail::message')

# @lang('email.hello')@if(!empty($notifiableName)){{ ' '.$notifiableName }}@endif!

@lang('email.newProjectStatus.subject')

<h5>@lang('app.projectDetails')</h5>

@component('mail::text', ['text' => $content])

@endcomponent

@component('mail::button', ['url' => $url, 'themeColor' => $themeColor])
@lang('app.viewProject')
@endcomponent

@lang('email.regards'),<br>
{{ config('app.name') }}
@endcomponent
