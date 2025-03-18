<div class="table-responsive p-20">
    <x-table class="table-bordered">
        <x-slot name="thead">
            <th>@lang('app.name')</th>
            <th>@lang('modules.tickets.group')</th>
            <th>@lang('app.status')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($agents as $agent)
            <tr class="row{{ $agent->id }}">
                <td>
                    <x-employee :user="$agent" />
                </td>
                <td>
                    <select class="change-agent-group form-control select-picker" data-agent-id="{{ $agent->id }}" multiple name="groupId[]">
                        @foreach ($groups as $group)
                            <option
                                @foreach ($agent->agentGroup as $item)
                                    @if ($item->id == $group->id)
                                            selected
                                        @endphp
                                    @endif
                                @endforeach
                            value="{{ $group->id }}">{{ $group->group_name }}</option>
                        @endforeach
                    </select>

                </td>
                <td>
                    <select class="change-agent-status form-control select-picker" data-agent-id="{{ $agent->id }}">
                        <option @if ($agent->agent[0]->status == 'enabled') selected @endif>@lang('app.enabled')</option>
                        <option @if ($agent->agent[0]->status == 'disabled') selected @endif>@lang('app.disabled')</option>
                    </select>
                </td>
                <td class="text-right">
                    <div class="task_view">
                        <a href="javascript:;" data-agent-id="{{ $agent->id }}"
                            class="delete-agents task_view_more d-flex align-items-center justify-content-center dropdown-toggle">
                            <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">
                    <x-cards.no-record icon="user" :message="__('messages.noAgentAdded')" />
                </td>
            </tr>
        @endforelse
    </x-table>
</div>

@push('scripts')
    <script>
        $(".change-agent-group").selectpicker({
            multipleSeparator: ", ",
            selectedTextFormat: "count > 6",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.groupSelected') }} ";
            }
        });
    </script>
@endpush
