<style>
    .link-length {
        word-break: break-all;
    }
</style>
<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-0">
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <x-table class="table-bordered">
                    <x-slot name="thead">
                        <th>@lang('modules.customLinkSettings.linkTitle')</th>
                        <th>@lang('modules.customLinkSettings.url')</th>
                        <th>@lang('modules.customLinkSettings.canBeViewedBy')</th>
                        <th>@lang('modules.customLinkSettings.status')</th>
                        <th class="text-right">@lang('app.action')</th>
                    </x-slot>

                    @forelse($custom_links as $key => $custom_link)
                        <tr class="row{{ $custom_link->id }}">
                            <td>{{ $custom_link->link_title }}</td>
                            <td class="link-length col-md-5">
                                <a target="_blank" href= {{ $custom_link->url }}>{{ $custom_link->url }}</a>
                            </td>

                            @php
                                $viewed = json_decode($custom_link->can_be_viewed_by);
                            @endphp
                            <td>
                                @foreach ($roles as $item)
                                    @if (in_array($item->id, $viewed))
                                        {{ $item->display_name }} <br>
                                    @endif
                                @endforeach
                            </td>

                            <td>{!! $custom_link->status == 'active' ? \App\Helper\Common::active() : \App\Helper\Common::inactive() !!}</td>

                            <td class="text-right">
                                <div class="task_view">
                                    <a class="task_view_more d-flex align-items-center justify-content-center edit-channel"
                                       data-custom_link-id="{{ $custom_link->id }}" href="javascript:;">
                                        <i class="fa fa-edit icons mr-2"></i> @lang('app.edit')
                                    </a>
                                </div>
                                <div class="task_view">
                                    <a class="task_view_more d-flex align-items-center justify-content-center delete-table-row"
                                       href="javascript:;" data-custom_link-id="{{ $custom_link->id }}">
                                        <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-cards.no-record-found-list colspan="5"/>
                    @endforelse
                </x-table>

            </div>
        </div>
    </div>
</div>
