@if (in_array('notices', $activeWidgets) && $sidebarUserPermissions['view_notice'] != 5 && $sidebarUserPermissions['view_notice'] != 'none' && in_array('notices', user_modules()))
    @isset($notices)
        <div class="row">
            <!-- EMP DASHBOARD NOTICE START -->
            <div class="col-md-12">
                <div class="my-3 b-shadow-4 rounded bg-white pb-2">
                    <!-- NOTICE HEADING START -->
                    <div class="d-flex align-items-center b-shadow-4 p-20">
                        <p class="mb-0 f-18 f-w-500"> @lang('app.menu.notices') </p>
                    </div>
                    <!-- NOTICE HEADING END -->
                    <!-- NOTICE DETAIL START -->
                    <div class="b-shadow-4 cal-info scroll ps" data-menu-vertical="1" data-menu-scroll="1"
                         data-menu-dropdown-timeout="500" id="empDashNotice" style="overflow: hidden;">


                        @foreach ($notices as $notice)
                            <div class="card border-0 b-shadow-4 p-20 rounded-0">
                                <div class="card-horizontal">
                                    <div class="card-header m-0 p-0 bg-white rounded">
                                        <x-date-badge :month="$notice->created_at->translatedFormat('M')" :date="$notice->created_at
                                                            ->timezone(company()->timezone)
                                                            ->translatedFormat('d')" />
                                    </div>
                                    <div class="card-body border-0 p-0 ml-3">
                                        <h4 class="card-title f-14 font-weight-normal mb-0">
                                            <a href="{{ route('notices.show', $notice->id) }}"
                                               class="openRightModal text-darkest-grey">{{ $notice->heading }}</a>
                                        </h4>
                                    </div>
                                </div>
                            </div><!-- card end -->
                        @endforeach


                        <div class="ps__rail-x" style="left: 0px; top: 0px;">
                            <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                        </div>
                        <div class="ps__rail-y" style="top: 0px; left: 0px;">
                            <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                        </div>
                    </div>
                    <!-- NOTICE DETAIL END -->
                </div>
            </div>
            <!-- EMP DASHBOARD NOTICE END -->
        </div>
    @endisset
@endif
