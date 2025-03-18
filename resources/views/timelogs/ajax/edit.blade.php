<div class="row">
    <div class="col-sm-12">
        <x-form id="save-timelog-data-form">
            @method('PUT')
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.timeLogDetails')</h4>
                <div class="row p-20">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <x-forms.select fieldId="project_id2" fieldName="project_id"
                                    :fieldLabel="__('app.project')" search="true">
                                    <option value="">--</option>
                                    @foreach ($projects as $project)
                                        <option @if ($project->id == $timeLog->project_id) selected @endif value="{{ $project->id }}">
                                            {{ $project->project_name }}
                                        </option>
                                    @endforeach
                                </x-forms.select>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <x-forms.select fieldId="task_id2" fieldName="task_id" :fieldLabel="__('app.task')"
                                    fieldRequired="true" search="true">
                                    <option value="">--</option>
                                    @if ($timeLog->task_id)
                                        <option selected value="{{ $timeLog->task_id }}">
                                            {{ $timeLog->task->heading }}</option>
                                    @endif
                                    @foreach ($tasks as $item)
                                        <option @if ($item->id == $timeLog->task_id) selected @endif value="{{ $item->id }}">
                                            {{ $item->heading }}
                                        </option>
                                    @endforeach
                                </x-forms.select>
                            </div>


                            @if ($editTimelogPermission == 'all')
                                <div class="col-md-6 col-lg-4">
                                    <x-forms.label class="mt-3" fieldId="user_id2" :fieldLabel="__('app.employee')"
                                        fieldRequired="true">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" name="user_id" id="user_id2"
                                            data-live-search="true" data-size="8">
                                            <option value="">--</option>
                                            @forelse ($employees as $item)
                                                <x-user-option :user="$item" :selected="$item->id == $timeLog->user_id"/>
                                            @empty

                                            @endforelse
                                        </select>
                                    </x-forms.input-group>
                                </div>
                            @else
                                <input type="hidden" name="user_id" value="{{ $timeLog->user_id }}">
                            @endif


                        </div>

                        <div class="row">
                            <div class="col-md-3 col-lg-3">
                                <x-forms.datepicker fieldId="start_date" fieldRequired="true"
                                    :fieldLabel="__('modules.timeLogs.startDate')" fieldName="start_date"
                                    :fieldValue="$timeLog->start_time->timezone(company()->timezone)->format(company()->date_format)"
                                    :fieldPlaceholder="__('placeholders.date')" />
                            </div>

                            <div class="col-md-3 col-lg-3">
                                <div class="bootstrap-timepicker timepicker">
                                    <x-forms.text :fieldLabel="__('modules.timeLogs.startTime')"
                                        :fieldPlaceholder="__('placeholders.hours')" fieldName="start_time"
                                        fieldId="start_time" fieldRequired="true"
                                        :fieldValue="$timeLog->start_time->timezone(company()->timezone)->format(company()->time_format)" />
                                </div>
                            </div>

                            <div class="col-md-3 col-lg-3">
                                <x-forms.datepicker fieldId="end_date" fieldRequired="true"
                                    :fieldLabel="__('modules.timeLogs.endDate')" fieldName="end_date"
                                    :fieldValue="now(company()->timezone)->format(company()->date_format)"
                                    :fieldPlaceholder="__('placeholders.date')"
                                    :fieldValue="$timeLog->end_time->timezone(company()->timezone)->format(company()->date_format)" />
                            </div>

                            <div class="col-md-3 col-lg-3">
                                <div class="bootstrap-timepicker timepicker">
                                    <x-forms.text :fieldLabel="__('modules.timeLogs.endTime')"
                                        :fieldPlaceholder="__('placeholders.hours')" fieldName="end_time"
                                        fieldId="end_time" fieldRequired="true"
                                        :fieldValue="$timeLog->end_time->timezone(company()->timezone)->format(company()->time_format)" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <x-forms.text :fieldLabel="__('modules.timeLogs.memo')" fieldName="memo" fieldRequired="true"
                            fieldId="memo" :fieldValue="$timeLog->memo"
                            :fieldPlaceholder="__('placeholders.timelog.memo')" />
                    </div>

                    <div class="col-md-6">
                        <x-forms.label fieldId="total_time" class="my-3"
                            :fieldLabel="__('modules.timeLogs.totalHours')" />
                        <p id="total_time" class="f-w-500 text-primary f-21">{{ $timeLog->hours }}</p>
                    </div>

                </div>

                <x-forms.custom-field :fields="$fields" :model="$timeLog"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="save-timelog-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('timelogs.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        const dp1 = datepicker('#start_date', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', $timeLog->start_time) }}"),
            onSelect: (instance, date) => {
                if (typeof dp2.dateSelected !== 'undefined' && dp2.dateSelected.getTime() < date
                    .getTime()) {
                    dp2.setDate(date, true)
                }
                if (typeof dp2.dateSelected === 'undefined') {
                    dp2.setDate(date, true)
                }
                dp2.setMin(date);
                calculateTime();
            },
            ...datepickerConfig
        });

        const dp2 = datepicker('#end_date', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', $timeLog->end_time->timezone(company()->timezone)) }}"),
            onSelect: (instance, date) => {
                dp1.setMax(date);
                calculateTime();
            },
            ...datepickerConfig
        });

        $('#start_time, #end_time').timepicker({
            @if (company()->time_format == 'H:i')
                showMeridian: false,
            @endif
        }).on('hide.timepicker', function(e) {
            var oldDate = new Date($(this).val());
            console.log(oldDate);
            calculateTime();
        });


        $('#project_id2').change(function() {
            var id = $(this).val();
            if (id == '') {
                id = 0;
            }
            var url = "{{ route('tasks.project_tasks', ':id').'?for_timelogs=true' }}";
            url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                type: "GET",
                container: '#save-timelog-data-form',
                blockUI: true,
                redirect: true,
                success: function(data) {
                    $('#task_id2').html(data.data);
                    $('#task_id2').selectpicker('refresh');
                }
            })
        });

        $('#task_id2').change(function() {
            var id = $(this).val();
            if (id == '') {
                id = 0;
            }
            var url = "{{ route('tasks.members', ':id') }}";
            url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                type: "GET",
                container: '#save-timelog-data-form',
                blockUI: true,
                redirect: true,
                success: function(data) {
                    $('#user_id2').html(data.data);
                    $('#user_id2').selectpicker('refresh');
                }
            })
        });

        $('#save-timelog-form').click(function() {
            const url = "{{ route('timelogs.update', $timeLog->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-timelog-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-timelog-form",
                data: $('#save-timelog-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        if ($(RIGHT_MODAL).hasClass('in')) {
                            document.getElementById('close-task-detail').click();
                            if ($('#timelogs-table').length) {
                                window.LaravelDataTables["timelogs-table"].draw(true);
                            } else {
                                showTable();
                            }
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            });
        });

        function calculateTime() {
            var format = '{{ company()->moment_date_format }}';
            var timeFormat = '{{ company()->time_format }}';
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            var startTime = $("#start_time").val();
            var endTime = $("#end_time").val();

            $.ajax({
                url: "{{ route('calculateTime') }}",
                type: 'POST',
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    start_time: startTime,
                    end_time: endTime,
                    format: format,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'error') {
                        Swal.fire({
                            icon: 'warning',
                            text: response.message,
                            customClass: {
                                confirmButton: 'btn btn-primary',
                            },
                            showClass: {
                                popup: 'swal2-noanimation',
                                backdrop: 'swal2-noanimation'
                            },
                            buttonsStyling: false
                        });

                        $('#end_time').val(startTime);
                    } else {
                        console.log(response);
                        var hours = response.hours;
                        var minutes = response.minutes;
                        $('#total_time').html(hours + " Hrs " + minutes + " Mins");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });

        }

        <x-forms.custom-field-filejs/>

        init(RIGHT_MODAL);
    });
</script>
