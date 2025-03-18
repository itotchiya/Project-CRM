@forelse ($employees as $item)
    <div class="col-sm-12 mb-3 timelog-user-{{ $item->id }}">
        <div class="card ticket-message rounded border">
            <div class="">
                <div class="card-body border-0 ">
                    <div class="row">
                        <div class="col-md-4">
                            <x-employee :user="$item" />
                        </div>
                        <div class="col-md-4 text-center align-self-center border-left">
                            <span class="f-w-500">
                                {{ intdiv(($item->total_minutes - $item->total_break_minutes), 60) }} @lang('modules.leaves.hours') {{ ($item->total_minutes - $item->total_break_minutes) % 60 }} @lang('app.minutes')
                            </span> <span class="f-12 text-dark-grey ml-1"> @lang('modules.timeLogs.logged')</span>
                        </div>

                        <div class="col-md-3 text-center align-self-center border-left">

                            <span class="f-w-500">
                                {{ currency_format($item->earnings, company()->currency_id) }}
                            </span> <span class="f-12 text-dark-grey ml-1"> @lang('app.earnings')</span>
                        </div>

                        <div class="col-md-1 text-center align-self-center border-left">
                            <button class="btn btn-outline show-user-timelogs text-primary" data-user-id="{{ $item->id }}" data-toggle="tooltip" data-original-title="@lang('app.expand')"><i
                                    class="fa fa-plus"></i></button>

                            <button class="btn btn-outline hide-user-timelogs d-none" data-user-id="{{ $item->id }}" data-toggle="tooltip" data-original-title="@lang('app.collapse')"><i
                                    class="fa fa-minus"></i></button>
                        </div>

                    </div>

                </div>

            </div>
        </div><!-- card end -->
    </div>

@empty
    <div class="col-md-12">
        <x-cards.no-record icon="user" :message="__('messages.noRecordFound')" />
    </div>
@endforelse
