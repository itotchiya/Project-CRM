<!-- ROW START -->
<div class="row py-0 py-md-0 py-lg-3">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- ACTIVITY DETAIL START -->
        <div class="p-activity-detail cal-info b-shadow-4" data-menu-vertical="1" data-menu-scroll="1"
            data-menu-dropdown-timeout="500" id="projectActivityDetail">

            @forelse($activities as $key=>$activity)
                <div class="card border-0 b-shadow-4 p-20 rounded-0">
                    <div class="card-horizontal">
                        <div class="card-header m-0 p-0 bg-white rounded">
                            <x-date-badge :month="$activity->created_at->timezone(company()->timezone)->translatedFormat('M')" :date="$activity->created_at->timezone(company()->timezone)->translatedFormat('d')" />
                        </div>
                        <div class="card-body border-0 p-0 ml-3">
                            <h4 class="card-title f-14 font-weight-normal ">{!! __($activity->activity) !!}
                            </h4>
                            <p class="card-text f-12 text-dark-grey">
                                {{ $activity->created_at->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
                            </p>
                        </div>
                    </div>
                </div><!-- card end -->
            @empty
                <div class="card border-0 b-shadow-4 p-20 rounded-0">
                    <div class="card-horizontal">

                        <div class="card-body border-0 p-0 ml-3">
                            <h4 class="card-title f-14 font-weight-normal">
                                @lang('messages.noActivityByThisUser')</h4>
                            <p class="card-text f-12 text-dark-grey"></p>
                        </div>
                    </div>
                </div><!-- card end -->
            @endforelse

        </div>
        <!-- ACTIVITY DETAIL END -->
    </div>
</div>
