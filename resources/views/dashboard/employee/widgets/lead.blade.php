@if (in_array('lead', $activeWidgets) && $leadAgent && in_array('leads', user_modules()))
    <div class="col-md-6 mb-3">
        <div
            class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mt-3 mt-lg-0 mt-md-0">
            <div class="d-block ">
                <h5 class="f-15 f-w-500 mb-20 text-darkest-grey"> @lang('app.menu.deal')  <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="@lang('messages.leadConversion')" data-html="true" data-trigger="hover"></i></h5>
                <div class="d-flex">
                    <a href="{{ route('deals.index') . '?assignee=me' }}">
                        <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
                            {{ count($deals) }}<span
                                class="f-12 font-weight-normal text-lightest">@lang('modules.deal.totalDeals')</span>
                        </p>
                    </a>

                    <a href="{{ route('deals.index') . '?assignee=me&stage=win' }}">
                        <p class="mb-0 f-21 font-weight-bold text-success d-grid">
                            {{ $convertedDeals }}<span
                                class="f-12 font-weight-normal text-lightest">@lang('modules.deal.convertedDeals')</span>
                        </p>
                    </a>
                </div>
            </div>
            <div class="d-block">
                <i class="bi bi-person text-lightest f-27"></i>
            </div>
        </div>
    </div>
@endif
