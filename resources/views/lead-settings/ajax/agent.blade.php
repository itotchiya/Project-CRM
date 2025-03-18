<div class="table-responsive p-20">
    <x-table class="table-bordered">
        <x-slot name="thead">
            <th>#</th>
            <th>@lang('app.name')</th>
            <th>@lang('app.category')</th>
            <th>@lang('app.status')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

       @forelse($leadAgents as $key => $agent)
            <tr class="row{{ $agent->id }}">
                <td>{{ ($key+1) }}</td>
                <td><x-employee :user="$agent" /></td>
                <td>
                    <select data-size="8" class="change-agent-category form-control select-picker" data-agent-id="{{ $agent->id }}" multiple name="categoryId[]">
                        @foreach ($leadCategories as $category)
                            <option
                                @foreach ($agent->leadAgent as $item)
                                    @if ($item->lead_category_id == $category->id)
                                            selected
                                    @endif
                                @endforeach
                            value="{{ $category->id }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="change-agent-status form-control select-picker" data-agent-id="{{ $agent->id }}">
                        <option @if ($agent->leadAgent[0]->status == 'enabled') selected @endif>@lang('app.enabled')</option>
                        <option @if ($agent->leadAgent[0]->status == 'disabled') selected @endif>@lang('app.disabled')</option>
                    </select>
                </td>
                <td class="text-right">
                    <div class="task_view">
                        <a class="task_view_more d-flex align-items-center justify-content-center delete-agent" href="javascript:;" data-agent-id="{{ $agent->id }}">
                            <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">
                    <x-cards.no-record icon="user" :message="__('messages.noLeadAgentAdded')" />
                </td>
            </tr>
        @endforelse
    </x-table>
</div>

<script>
    $(".change-agent-category").selectpicker({
        multipleSeparator: ", ",
        selectedTextFormat: "count > 8",
        countSelectedText: function(selected, total) {
            return selected + " {{ __('app.categorySelected') }} ";
        }
    });
</script>
