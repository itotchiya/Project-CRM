@php
$moveClass = '';
@endphp
@if ($draggable == 'false')
    @php
        $moveClass = 'move-disable';
    @endphp
@endif

<div class="card rounded bg-white border-grey b-shadow-4 m-1 mb-2 {{ $moveClass }} task-card" data-task-id="{{ $lead->id }}"
    id="drag-task-{{ $lead->id }}">
    <div class="card-body p-2">
        <div class="d-flex justify-content-between mb-2">
            <a href="{{ route('deals.show', [$lead->id]) }}"
                class="f-12 f-w-500 text-dark mb-0 text-wrap openRightModal">{{ $lead->name }}
                @if (!is_null($lead->contact->client_id))
                <i class="fa fa-check-circle text-success" data-toggle="tooltip" data-original-title="@lang('modules.lead.convertedClient')"></i>
                @endif
            </a>
            @if (!is_null($lead->value))
                <div class="d-flex">
                    <span
                        class="ml-2 f-11 text-lightest">{{ currency_format($lead->value, $lead->currency_id) }}</span>
                </div>
            @endif
        </div>

        @if ($lead->contact->client_name)
            <div class="d-flex mb-3 align-items-center">
                <i class="fa fa-building f-11 text-lightest"></i><span
                    class="ml-2 f-11 text-lightest">{{ $lead->contact->client_name_salutation }}</span>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center">
            @if (!is_null($lead->agent_id))
                <div class="d-flex flex-wrap">
                    <div class="avatar-img mr-1 rounded-circle">
                        <a href="{{ route('employees.show', $lead->leadAgent->user_id) }}" alt="{{ $lead->leadAgent->user->name }}" data-toggle="tooltip"
                            data-original-title="{{ __('app.leadAgent') .' : '. $lead->leadAgent->user->name }}"
                            data-placement="right"><img src="{{ $lead->leadAgent->user->image_url }}"></a>
                    </div>
                </div>
            @endif
            @if ($lead->next_follow_up_date != null && $lead->next_follow_up_date != '')
                <div class="d-flex text-lightest">
                    <span class="f-12 ml-1"><i class="f-11 bi bi-calendar"></i> {{ \Carbon\Carbon::parse($lead->next_follow_up_date)->translatedFormat(company()->date_format) }}</span>
                </div>
            @endif

        </div>
    </div>
</div><!-- div end -->
