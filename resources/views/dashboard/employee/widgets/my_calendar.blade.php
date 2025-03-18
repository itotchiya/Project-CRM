@if (in_array('my_calender', $activeWidgets) &&
                (in_array('tasks', user_modules()) || in_array('events', user_modules()) || in_array('holidays', user_modules()) ||
                in_array('tickets', user_modules()) || in_array('leaves', user_modules())))
    <div class="row">
        <div class="col-md-12">
            <x-cards.data :title="__('app.menu.myCalendar')">
                <div id="calendar"></div>
                <x-slot name="action">
                    <div class="dropdown ml-auto calendar-action">
                        <button id="event-btn" class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle cal-event" type="button"
                                aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div id="cal-drop" class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-2">
                            @if(in_array('tasks', user_modules()))
                                <div class="custom-control custom-checkbox cal-filter">
                                    <input type="checkbox" value="task"
                                           class="form-check-input filter-check" name="calendar[]"
                                           id="customCheck1" @if(in_array('task',$event_filter)) checked @endif>
                                    <label
                                        class="form-check-label form_custom_label text-dark-grey pl-2 mr-3 justify-content-start cursor-pointer checkmark-20 pt-2 text-wrap"
                                        for="customCheck1">@lang('app.menu.tasks')</label>
                                </div>
                            @endif
                            @if(in_array('events', user_modules()))
                                <div class="custom-control custom-checkbox cal-filter">
                                    <input type="checkbox" value="events"
                                           class="form-check-input filter-check" name="calendar[]"
                                           id="customCheck2" @if(in_array('events',$event_filter)) checked @endif>
                                    <label
                                        class="form-check-label form_custom_label text-dark-grey pl-2 mr-3 justify-content-start cursor-pointer checkmark-20 pt-2 text-wrap"
                                        for="customCheck2">@lang('app.menu.events')</label>
                                </div>
                            @endif
                            @if(in_array('holidays', user_modules()))
                                <div class="custom-control custom-checkbox cal-filter">
                                    <input type="checkbox" value="holiday"
                                           class="form-check-input filter-check" name="calendar[]"
                                           id="customCheck3" @if(in_array('holiday',$event_filter)) checked @endif>
                                    <label
                                        class="form-check-label form_custom_label text-dark-grey pl-2 mr-3 justify-content-start cursor-pointer checkmark-20 pt-2 text-wrap"
                                        for="customCheck3">@lang('app.menu.holiday')</label>
                                </div>
                            @endif
                            @if(in_array('tickets', user_modules()))
                                <div class="custom-control custom-checkbox cal-filter">
                                    <input type="checkbox" value="tickets"
                                           class="form-check-input filter-check" name="calendar[]"
                                           id="customCheck4" @if(in_array('tickets',$event_filter)) checked @endif>
                                    <label
                                        class="form-check-label form_custom_label text-dark-grey pl-2 mr-3 justify-content-start cursor-pointer checkmark-20 pt-2 text-wrap"
                                        for="customCheck4">@lang('app.menu.tickets')</label>
                                </div>
                            @endif
                            @if(in_array('leaves', user_modules()))
                                <div class="custom-control custom-checkbox cal-filter">
                                    <input type="checkbox" value="leaves"
                                           class="form-check-input filter-check" name="calendar[]"
                                           id="customCheck5" @if(in_array('leaves',$event_filter)) checked @endif>
                                    <label
                                        class="form-check-label form_custom_label text-dark-grey pl-2 mr-3 justify-content-start cursor-pointer checkmark-20 pt-2 text-wrap"
                                        for="customCheck5">@lang('app.menu.leaves')</label>
                                </div>
                            @endif
                            @if(in_array('leads', user_modules()) && $leadAgent)
                                <div class="custom-control custom-checkbox cal-filter">
                                    <input type="checkbox" value="follow_ups"
                                           class="form-check-input filter-check" name="calendar[]"
                                           id="customCheck5" @if(in_array('follow_ups',$event_filter)) checked @endif>
                                    <label
                                        class="form-check-label form_custom_label text-dark-grey pl-2 mr-3 justify-content-start cursor-pointer checkmark-20 pt-2 text-wrap"
                                        for="customCheck5">@lang('modules.dashboard.followUps')</label>
                                </div>
                            @endif
                        </div>
                    </div>
                </x-slot>
            </x-cards.data>
        </div>
    </div>
@endif
