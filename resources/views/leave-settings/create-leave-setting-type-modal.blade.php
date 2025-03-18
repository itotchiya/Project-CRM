<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-colorpicker.css') }}" />

<div class="modal-header">
    <h5 class="modal-title">@lang('app.addNewLeaveType')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="createLeave" method="POST" class="ajax-form">
            <div class="tabs border-bottom-grey">
                <div class="nav" id="nav-tab">
                    <a class="nav-item nav-link f-15 type active" data-toggle="tab" href="#personal" role="tab" aria-controls="nav-type" aria-selected="true">@lang('app.general')</a>
                    <a class="nav-item nav-link f-15 type" data-toggle="tab" href="#promotion" role="tab" aria-controls="nav-type" aria-selected="true">@lang('modules.leaves.entitlement')</a>
                    <a class="nav-item nav-link f-15 type" data-toggle="tab" href="#vacation" role="tab" aria-controls="nav-type" aria-selected="true">@lang('modules.leaves.applicability')</a>
                </div>
            </div>

            <div class="tab-content" id="tab-content">
                    <div class="tab-pane active" id="personal">

                        <input type="hidden" value="true" name="page_reload" id="page_reload">
                        <h3 class="heading-h3 mt-4">@lang('app.general')</h3>

                        <div class="row">

                            <div class="col-lg-4">
                                <x-forms.text :fieldLabel="__('modules.leaves.leaveType')"
                                    :fieldPlaceholder="__('placeholders.leaveType')" fieldName="type_name" fieldId="type_name"
                                    fieldValue="" :fieldRequired="true" />
                            </div>

                            <div class="col-lg-4">
                                <x-forms.select fieldId="leavetype" :fieldLabel="__('modules.leaves.leaveAllotmentType')" fieldName="leavetype" search="true">
                                    <option value="monthly">@lang('app.monthlyLeaveType')</option>
                                    <option value="yearly">@lang('app.yearlyLeaveType')</option>
                                </x-forms.select>
                            </div>


                            <div class="col-lg-4" id="yearly-leave-field" style="display: none;">
                                <x-forms.number :fieldLabel="__('modules.leaves.noOfYearlyLeaves')"
                                                fieldName="yearly_leave_number" fieldId="yearly_leave_number" fieldValue="0" minValue="0" :popover="__('messages.leave.noOfYearlyLeaves')"/>
                            </div>

                            <div class="col-lg-4" id="monthly-leave-field">
                                <x-forms.number :fieldLabel="__('modules.leaves.noOfMonthlyLeaves')"
                                                fieldName="monthly_leave_number" fieldId="monthly_leave_number" fieldValue="0" minValue="0" :popover="__('messages.leave.noOfMonthlyLeaves')"/>
                            </div>

                            <div class="col-lg-4">
                                <x-forms.select fieldId="paid" :fieldLabel="__('modules.leaves.leavePaidStatus')" fieldName="paid" search="true" :popover="__('messages.leave.paidStatus')">
                                    <option value="1">@lang('app.paid')</option>
                                    <option value="0">@lang('app.unpaid')</option>
                                </x-forms.select>
                            </div>

                            <div class="col-lg-4" id="monthly-leave-limit">
                                <x-forms.number :fieldLabel="__('modules.leaves.monthLimit')"
                                    fieldName="monthly_limit" fieldId="monthly_limit" fieldValue="0"
                                    :fieldHelp="__('modules.leaves.monthLimitInfo')" minValue="0"
                                    :popover="__('messages.leave.monthlyLimit')"/>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="colorselector" fieldRequired="true"
                                        :fieldLabel="__('modules.sticky.colors')">
                                    </x-forms.label>
                                    <x-forms.input-group id="colorpicker">
                                        <input type="text" class="form-control height-35 f-14"
                                            placeholder="{{ __('placeholders.colorPicker') }}" name="color" id="colorselector">

                                        <x-slot name="append">
                                            <span class="input-group-text height-35 colorpicker-input-addon"><i></i></span>
                                        </x-slot>
                                    </x-forms.input-group>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="tab-pane" id="promotion">
                        <h3 class="heading-h3 mt-4">@lang('modules.leaves.entitlement')</h3>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group my-3">
                                    <div class="d-flex align-items-center">
                                        <label class="f-14 text-dark-grey mb-12 mt-2 mr-1">@lang('modules.leaves.effectiveAfter')</label>
                                        &nbsp;<i class="fa fa-question-circle text-dark-grey" data-toggle="popover" data-placement="top" data-html="true"
                                                data-content="{{__('messages.leave.effectiveAfter')}}" data-trigger="hover"></i>
                                        <div class="col-md-4 ml-2">
                                            <x-forms.input-group>
                                                <input type="number" class="form-control height-35 f-14" name="effective_after" id="effective_after" min="0">
                                                <x-slot name="append">
                                                    <select name="effective_type" class="select-picker form-control">
                                                        <option value="day">@lang('app.day')</option>
                                                        <option value="month">@lang('app.month')</option>
                                                    </select>
                                                </x-slot>
                                            </x-forms.input-group>
                                        </div>
                                        <label class="f-14 text-dark-grey mb-12 mt-2">@lang('modules.leaves.ofJoining')</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <div class="d-flex mt-3">
                                        <x-forms.checkbox :fieldLabel="__('modules.leaves.allowedProbation')"
                                                fieldName="allowed_probation" fieldId="allowed_probation" checked="true" fieldValue="1" fieldRequired="true"
                                                :popover="__('messages.leave.allowedProbation')"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group my-3">
                                    <div class="d-flex align-items-center">
                                        <label class="f-14 mb-12 mt-2 text-dark-grey mr-1">@lang('modules.leaves.unusedLeaves')</label>
                                        &nbsp;<i class="fa fa-question-circle text-dark-grey" data-toggle="popover" data-placement="top" data-html="true"
                                                data-content="{{__('messages.leave.unusedLeave')}}" data-trigger="hover"></i>
                                        <div class="col-md-6">
                                            <x-forms.input-group>
                                                <select name="unused_leave" class="select-picker form-control">
                                                    <option value="carry forward">@lang('modules.leaves.carryForward')</option>
                                                    <option value="lapse">@lang('modules.leaves.lapse')</option>
                                                    <option value="paid">@lang('app.paid')</option>
                                                </select>
                                            </x-forms.input-group>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group my-3">
                                    <div class="d-flex align-items-center">
                                        <label class="f-14 mb-12 mt-2 text-dark-grey mr-1">@lang('modules.leaves.overutilization')</label>
                                        &nbsp;<i class="fa fa-question-circle text-dark-grey" data-toggle="popover" data-placement="top" data-html="true"
                                                data-content="{{__('messages.leave.overutilization')}}" data-trigger="hover"></i>
                                        <div class="col-md-7">
                                            <x-forms.input-group>
                                                <select name="over_utilization" class="select-picker form-control">
                                                    <option value="not_allowed">@lang('modules.leaves.doNotAllow')</option>
                                                    <option value="allow_paid">@lang('modules.leaves.allowPaid')</option>
                                                    <option value="allow_unpaid">@lang('modules.leaves.allowUnpaid')</option>
                                                </select>
                                            </x-forms.input-group>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <div class="d-flex mt-3">
                                        <x-forms.checkbox :fieldLabel="__('modules.leaves.allowedNotice')"
                                                fieldName="allowed_notice" fieldId="allowed_notice"  checked="true" fieldValue="1" fieldRequired="true"
                                                :popover="__('messages.leave.allowedNotice')"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="vacation">
                        <h3 class="heading-h3 mt-4">@lang('modules.leaves.applicability')</h3>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="gender" :fieldLabel="__('modules.employees.gender')" fieldRequired="true">
                                    </x-forms.label>
                                    &nbsp;<i class="fa fa-question-circle text-dark-grey" data-toggle="popover" data-placement="top" data-html="true"
                                            data-content="{{__('messages.leave.gender')}}" data-trigger="hover"></i>
                                    <select class="form-control multiple-option" multiple name="gender[]"
                                        id="gender" data-live-search="true" data-size="8">
                                        <option value="male" selected>@lang('app.male')</option>
                                        <option value="female" selected>@lang('app.female')</option>
                                        <option value="others" selected>@lang('app.others')</option>
                                </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="marital_status" :fieldLabel="__('modules.employees.maritalStatus')" fieldRequired="true">
                                    </x-forms.label>
                                    &nbsp;<i class="fa fa-question-circle text-dark-grey" data-toggle="popover" data-placement="top" data-html="true"
                                            data-content="{{__('messages.leave.maritalStatus')}}" data-trigger="hover"></i>
                                    <select class="form-control multiple-option" multiple name="marital_status[]"
                                        id="marital_status" data-live-search="true" data-size="8">
                                        @foreach (\App\Enums\MaritalStatus::cases() as $status)
                                            <option selected value="{{ $status->value }}">{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="department" :fieldLabel="__('app.department')" fieldRequired="true">
                                    </x-forms.label>
                                    &nbsp;<i class="fa fa-question-circle text-dark-grey" data-toggle="popover" data-placement="top" data-html="true"
                                            data-content="{{__('messages.leave.department')}}" data-trigger="hover"></i>
                                    <select class="form-control multiple-option" multiple name="department[]"
                                            id="department" data-live-search="true" data-size="8">
                                        @foreach ($teams as $team)
                                            <option value="{{ $team->id }}" selected>{{ $team->team_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="designation" :fieldLabel="__('app.designation')" fieldRequired="true">
                                    </x-forms.label>
                                    &nbsp;<i class="fa fa-question-circle text-dark-grey" data-toggle="popover" data-placement="top" data-html="true"
                                            data-content="{{__('messages.leave.designation')}}" data-trigger="hover"></i>
                                    <select class="form-control multiple-option" multiple name="designation[]"
                                            id="designation" data-live-search="true" data-size="8">
                                        @foreach ($designations as $designation)
                                            <option value="{{ $designation->id }}" selected>{{ $designation->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="role" :fieldLabel="__('app.role')" fieldRequired="true">
                                    </x-forms.label>
                                    &nbsp;<i class="fa fa-question-circle text-dark-grey" data-toggle="popover" data-placement="top" data-html="true"
                                            data-content="{{__('messages.leave.role')}}" data-trigger="hover"></i>
                                    <select class="form-control multiple-option" multiple name="role[]"
                                            id="role" data-live-search="true" data-size="8">
                                        @foreach ($roles as $roles)
                                            <option value="{{ $roles->id }}" selected>{{ $roles->display_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

            </div>
        </x-form>

    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-leave-setting" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $(document).ready(function () {

        $('#monthly-leave-limit').hide();
        setTimeout(function () {
            $('[data-toggle="popover"]').popover();
        }, 500);
    });

    $(".select-picker").selectpicker();

    $('#colorpicker').colorpicker({
        "color": "#16813D"
    });

    $('#leavetype').change(function() {
        if ($(this).val() == 'monthly') {
            $('#yearly-leave-field').hide();
            $('#yearly_leave_number').attr('required', false);
            $('#monthly-leave-field').show();
            $('#monthly_leave_number').attr('required', true);
            $('#monthly-leave-limit').hide();
            $('#monthly_limit').attr('required', false);
        } else {
            $('#yearly-leave-field').show();
            $('#yearly_leave_number').attr('required', true);
            $('#monthly-leave-field').hide();
            $('#monthly_leave_number').attr('required', false);
            $('#monthly-leave-limit').show();
            $('#monthly_limit').attr('required', true);
        }
    });


    $(".multiple-option").selectpicker({
        actionsBox: true,
        selectAllText: "{{ __('modules.permission.selectAll') }}",
        deselectAllText: "{{ __('modules.permission.deselectAll') }}",
        multipleSeparator: ", ",
        selectedTextFormat: "count > 8",
        countSelectedText: function(selected, total) {
            return selected + " {{ __('app.membersSelected') }} ";
        }
    });

    $('#save-leave-setting').click(function() {
        $.easyAjax({
            container: '#createLeave',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-leave-setting",
            errorPosition: 'inline',
            url: "{{ route('leaveType.store') }}",
            data: $('#createLeave').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    if (response.page_reload == 'true') {
                        window.location.reload();
                    } else {
                        $('#leave_type_id').html(response.data);
                        $('#leave_type_id').selectpicker('refresh');
                        $(MODAL_XL).modal('hide');
                    }
                }
            }
        })
    });
</script>
