<div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-20">
    <div class="row">
        <div class="table-responsive">

            <x-table class="table-bordered">
                <x-slot name="thead">
                    <th>#</th>
{{--                    <th width="20%">@lang('app.qrCode')</th>--}}
                    <th width="20%">@lang('app.menu.method')</th>
                    <th width="30%">@lang('app.description')</th>
                    <th>@lang('app.status')</th>
                    <th class="text-right">@lang('app.action')</th>
                </x-slot>

                @forelse($offlineMethods as $method)
                    <tr class="row{{ $method->id }}">
                        <td>{{ $loop->iteration }}</td>
{{--                        <td>@if($method->image) <img src="{{$method->image_url}}" height="100px" width="100px">@else - @endif</td>--}}
                        <td>{{ $method->name }}</td>
                        <td class="text-break">{!! nl2br($method->description) !!} </td>
                        <td>{!! ($method->status == 'yes') ? \App\Helper\Common::active(): \App\Helper\Common::inactive() !!}</td>

                        <td class="text-right">
                            <div class="task_view">
                                <a href="javascript:;" data-type-id="{{ $method->id }}"
                                   class="task_view_more d-flex align-items-center justify-content-center edit-type"
                                   {{-- data-toggle="tooltip" --}}
                                   data-original-title="@lang('app.edit')">
                                    <i class="fa fa-edit icons"></i>
                                </a>
                            </div>
                            <div class="task_view">
                                <a href="javascript:;" data-type-id="{{ $method->id }}"
                                   class="task_view_more d-flex align-items-center justify-content-center delete-type"
                                   {{-- data-toggle="tooltip" --}}
                                   data-original-title="@lang('app.delete')">
                                    <i class="fa fa-trash icons"></i>
                                </a>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <x-cards.no-record-found-list colspan="5"/>
                    </tr>
                @endforelse
            </x-table>

        </div>
    </div>
</div>
