@if (!is_null($selfActiveTimer) && in_array('tasks', user_modules()))

    <span class="border rounded f-14 py-2 px-2 d-none d-sm-block mr-3">
        <span id="active-timer" class="mr-2">{{ $selfActiveTimer->timer }}</span>

        @if (is_null($selfActiveTimer->activeBreak))
            <a href="javascript:;" class="pause-active-timer mr-1 border-right" data-url="{{ url()->current() }}"
                data-toggle="tooltip" data-original-title="{{ __('modules.timeLogs.pauseTimer') }}"
                data-time-id="{{ $selfActiveTimer->id }}">
                <i class="fa fa-pause-circle text-primary"></i>
            </a>
        @else
            <a href="javascript:;" class="resume-active-timer mr-1 border-right" data-url="{{ url()->current() }}"
                data-toggle="tooltip" data-original-title="{{ __('modules.timeLogs.resumeTimer') }}"
                data-time-id="{{ $selfActiveTimer->activeBreak->id }}">
                <i class="fa fa-play-circle text-primary"></i>
            </a>
        @endif
        <a href="javascript:;" class="stop-active-timer" id="stop-active-timer" data-toggle="tooltip"
            data-original-title="{{ __('modules.timeLogs.stopTimer') }}" data-url="{{ url()->current() }}"
            data-time-id="{{ $selfActiveTimer->id }}">
            <i class="fa fa-stop-circle text-danger"></i>
        </a>

    </span>

    @if (is_null($selfActiveTimer->activeBreak))
        <a href="javascript:;"
            class='btn-danger btn btn-sm rounded mr-3 f-14 py-2 px-2 stop-active-timer d-block d-sm-none mr-2'
            data-time-id="{{ $selfActiveTimer->id }}" data-url="{{ url()->current() }}" id="stop-active-timer">
            {{ __('modules.timeLogs.stopTimer') }}
        </a>
    @endif
@endif

<script>
    var $worked = $("#active-timer");
    var activeBreak = "{{ !is_null($selfActiveTimer) && !is_null($selfActiveTimer->activeBreak) }}";

    function updateTimerTask() {
        var myTime = $worked.html();
        var ss = myTime.split(":");

        var hours = ss[0];
        var mins = ss[1];
        var secs = ss[2];
        secs = parseInt(secs) + 1;

        if (secs > 59) {
            secs = '00';
            mins = parseInt(mins) + 1;
        }

        if (mins > 59) {
            secs = '00';
            mins = '00';
            hours = parseInt(hours) + 1;
        }

        if (hours.toString().length < 2) {
            hours = '0' + hours;
        }
        if (mins.toString().length < 2) {
            mins = '0' + mins;
        }
        if (secs.toString().length < 2) {
            secs = '0' + secs;
        }
        var ts = hours + ':' + mins + ':' + secs;

        $worked.html(ts);

        if (runTimeClock) {
            return setTimeout(updateTimerTask, 1000);
        }
    }

    if ($('#active-timer').length && activeBreak != '1') {
        runTimeClock = true;
        setTimeout(updateTimerTask, 1000);
    } else {
        runTimeClock = false;
    }
        document.addEventListener("visibilitychange", function() {
            if (document.visibilityState === "visible") {
                var url = "{{ route('timelogs.timer_data') }}";
                    $.easyAjax({
                    url: url,
                    container: '#startTimerForm',
                    type: "GET",
                    blockUI: true,
                    success: function (response) {
                        if (response.status == 'success') {
                            $('#active-timer').html(response.data.timer);
                        }
                    }
                });
            }
        });

@if(!is_null($selfActiveTimer))
    $('.stop-active-timer').click(function(){
        var url = "{{ route('timelogs.stopper_alert', ':id') }}?via=timelog";
        var id = "{{$selfActiveTimer->id}}";
        url = url.replace(':id', id);
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    })
@endif

</script>
