    <div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.lead.editFollowUp')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="portlet-body">

        <x-form id="followUpForm" method="POST" class="ajax-form">
            <input type="hidden" name="deal_id" value="{{ $follow->deal_id }}">
            <input type="hidden" name="id" value="{{ $follow->id }}">
            <div class="form-body">
                <div class="row">

                    <div class="col-md-6">
                        <x-forms.datepicker fieldId="next_follow_up_date" fieldRequired="true"
                            :fieldLabel="__('modules.lead.leadFollowUp')" fieldName="next_follow_up_date"
                            :fieldValue="$follow->next_follow_up_date->format(company()->date_format)"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>
                    <div class="col-md-6">
                        <div class="bootstrap-timepicker timepicker">
                            <x-forms.text fieldLabel="Start Time" :fieldPlaceholder="__('placeholders.hours')" fieldName="start_time" fieldId="start_time" fieldRequired="true" :fieldValue="$follow->next_follow_up_date->format(company()->time_format)"/>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <x-forms.select fieldId="status" :fieldLabel="__('modules.employees.status')" fieldName="status" search="true">
                                        <option value="pending"  @selected($follow->status == 'pending')  data-content="<i class='fa fa-circle mr-2 text-warning'></i> @lang('app.pending') " >@lang('app.pending')</option>
                                        <option value="canceled" @selected($follow->status == 'canceled') data-content="<i class='fa fa-circle mr-2 text-red'></i> @lang('app.canceled') " >@lang('app.canceled')</option>
                                        <option value="completed" @selected($follow->status == 'completed') data-content="<i class='fa fa-circle mr-2 text-dark-green'></i> @lang('app.completed') " >@lang('app.completed')</option>
                                    </x-forms.select>
                            </div>

                            <div class="col-md-6 mt-5">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.reminder')" fieldName="send_reminder"
                                    fieldId="send_reminder" fieldValue="yes" fieldRequired="true"
                                    :checked="$follow->send_reminder == 'yes'" />
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 send_reminder_div @if ($follow->send_reminder == null) d-none @endif">
                        <div class="row">
                            <div class="col-lg-6 mt-1">
                                <x-forms.number class="mr-0 mr-lg-2 mr-md-2"
                                    :fieldLabel="__('modules.events.remindBefore')" fieldName="remind_time"
                                    fieldId="remind_time" :fieldValue="$follow->remind_time" fieldRequired="true" />
                            </div>
                            <div class="col-md-6 mt-3">
                                <x-forms.select fieldId="remind_type" fieldLabel="" fieldName="remind_type"
                                    search="true">
                                    <option @selected($follow->remind_type == 'day') value="day">@lang('app.day')</option>
                                    <option @selected($follow->remind_type == 'hour') value="hour">@lang('app.hour')</option>
                                    <option @selected($follow->remind_type == 'minute') value="minute">@lang('app.minute')
                                    </option>
                                </x-forms.select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.lead.remark')"
                                fieldName="remark" fieldId="remark"
                                fieldPlaceholder="" :fieldValue="$follow->remark">
                            </x-forms.textarea>
                        </div>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-followup" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>

    $(".select-picker").selectpicker();

    $('#start_time').timepicker({
            @if (company()->time_format == 'H:i')
                showMeridian: false,
            @endif
        });

    var dp1 = datepicker('#next_follow_up_date', {
        position: 'bl',
        dateSelected: new Date("{{ str_replace('-', '/', $follow->next_follow_up_date) }}"),
        onSelect: (instance, date) => {
            if (typeof dp2.dateSelected !== 'undefined' && dp2.dateSelected.getTime() < date
                .getTime()) {
                dp2.setDate(date, true)
            }
            if (typeof dp2.dateSelected === 'undefined') {
                dp2.setDate(date, true)
            }
            dp2.setMin(date);
        },
        ...datepickerConfig
    });

    $('#send_reminder').change(function() {
            $('.send_reminder_div').toggleClass('d-none');
        })

    // save followup
    $('#save-followup').click(function() {
        $.easyAjax({
            url: "{{ route('deals.follow_up_update') }}",
            container: '#followUpForm',
            type: "POST",
            blockUI: true,
            data: $('#followUpForm').serialize(),
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        })
    });

</script>
