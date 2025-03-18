<link rel="stylesheet" href="{{ asset('vendor/css/daterangepicker.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-colorpicker.css') }}" />
<!-- for sortable content -->
<link rel="stylesheet" href="{{ asset('vendor/css/jquery-ui.css') }}">

<style>
    .preloader-container {
        margin-left: 260px !important;
        width: calc(100% - 260px) !important;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="update-rotation-data-form">
            @method('PUT')
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.editRotation')</h4>
                <div class="row p-20">
                    <input type="hidden" name="rotation_id" value="{{ $shiftRotation->id }}">
                    <div class="col-md-3">
                        <x-forms.text fieldId="rotation_name" :fieldLabel="__('app.rotationName')" fieldName="rotation_name"
                            fieldRequired="true" :fieldValue="$shiftRotation->rotation_name">
                        </x-forms.text>
                    </div>

                    <div class="col-md-3">
                        <x-forms.select fieldId="rotation_frequency" :fieldLabel="__('app.rotationFrequency')" fieldName="rotation_frequency"
                            search="true">
                            <option value="">--</option>
                            <option value="weekly" @if($shiftRotation->rotation_frequency == 'weekly') selected @endif>@lang('app.weekly')</option>
                            <option value="bi-weekly" @if($shiftRotation->rotation_frequency == 'bi-weekly') selected @endif>@lang('app.bi-weekly')</option>
                            <option value="monthly" @if($shiftRotation->rotation_frequency == 'monthly') selected @endif>@lang('app.monthly')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-3 @if(is_null($shiftRotation->schedule_on)) d-none @endif" id="scheduleOnDiv">
                        <x-forms.select fieldId="schedule_on" :fieldLabel="__('app.scheduleOn')" fieldName="schedule_on" fieldRequired="true" search="true">
                            <option value="">--</option>
                            <option value="every-monday" @if($shiftRotation->schedule_on == 'every-monday') selected @endif>@lang('app.every') @lang('app.monday')</option>
                            <option value="every-tuesday" @if($shiftRotation->schedule_on == 'every-tuesday') selected @endif>@lang('app.every') @lang('app.tuesday')</option>
                            <option value="every-wednesday" @if($shiftRotation->schedule_on == 'every-wednesday') selected @endif>@lang('app.every') @lang('app.wednesday')</option>
                            <option value="every-thursday" @if($shiftRotation->schedule_on == 'every-thursday') selected @endif>@lang('app.every') @lang('app.thursday')</option>
                            <option value="every-friday" @if($shiftRotation->schedule_on == 'every-friday') selected @endif>@lang('app.every') @lang('app.friday')</option>
                            <option value="every-saturday" @if($shiftRotation->schedule_on == 'every-saturday') selected @endif>@lang('app.every') @lang('app.saturday')</option>
                            <option value="every-sunday" @if($shiftRotation->schedule_on == 'every-sunday') selected @endif>@lang('app.every') @lang('app.sunday')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-3 @if(is_null($shiftRotation->rotation_date)) d-none @endif" id="dateDiv">
                        <x-forms.select fieldId="rotation_date" :fieldLabel="__('app.scheduleDate')" fieldName="rotation_date"
                        fieldRequired="true" search="true">
                            <option value="">--</option>
                            @foreach ($dates as $date)
                                <option value="{{ $date }}" @if($shiftRotation->rotation_date == $date) selected @endif>{{ $date }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-3 mt-3">
                        <div id="colorpicker" class="input-group">
                            <div class="form-group w-100">
                                <x-forms.label fieldId="color_code" :fieldLabel="__('app.colorCode')" fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <input type="text" name="color_code" id="color_code" value="@if($shiftRotation->color_code) {{ $shiftRotation->color_code }} @else #7EE7F9 @endif"
                                        class="form-control height-35 f-15 light_text">
                                    <x-slot name="append">
                                        <span class="input-group-text colorpicker-input-addon height-35"><i></i></span>
                                    </x-slot>
                                </x-forms.input-group>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row pl-20 pr-20">
                    <div class="col-md-3">
                        <x-forms.select fieldId="rotation_sequence" :fieldLabel="__('app.selectShift')" fieldName="rotation_sequence"
                        fieldRequired="true" search="true">
                            <option value="">--</option>
                            @foreach ($employeeShifts as $item)
                                <option
                                data-content="<i class='fa fa-circle mr-2' style='color: {{ $item->color }}'></i> {{ ($item->shift_name != 'Day Off') ? $item->shift_name : __('modules.attendance.' . str($item->shift_name)->camel()) }}{{ ($item->shift_name != 'Day Off') ? ' ['.$item->office_start_time.' - '.$item->office_end_time.']' : ''}}"
                                value="{{ $item->id }}" data-name="{{ $item->shift_name }}" data-color="{{ $item->color }}">
                                    {{ ($item->shift_name != 'Day Off') ? $item->shift_name : __('modules.attendance.' . str($item->shift_name)->camel()) }}{{ ($item->shift_name != 'Day Off') ? ' ['.$item->office_start_time.' - '.$item->office_end_time.']' : ''}}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-3 mt-5 " id="add-shift-btn">
                        <div class="pxl-1 pt-0 pb-3 mt-2 row">
                            <div class="col-md-12">
                                <a class="f-15 f-w-500" href="javascript:;" id="add-shift"><i class="fa fa-plus mr-2"></i>@lang('app.clickToAdd')</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mt-5">
                        <x-forms.checkbox :fieldLabel="__('app.replacePreAssignedShift')"
                                        fieldName="override_shift"
                                        fieldId="override_shift" fieldValue="yes" :checked="($shiftRotation->override_shift == 'yes') ? 'checked' : ''"/>
                    </div>
                    <div class="col-md-3 mt-5">
                        <x-forms.checkbox :fieldLabel="__('app.sendRotationNotification')"
                                        fieldName="send_mail"
                                        fieldId="send_mail" fieldValue="yes" :checked="($shiftRotation->send_mail == 'yes') ? 'checked' : ''" />
                    </div>
                </div>

                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.label fieldId="shiftContainer" :fieldLabel="__('app.rotationSequence')">
                        </x-forms.label>
                        <div id="shiftContainer" class="border p-3" style="min-height: 150px;">
                            @foreach ($shiftRotation->sequences as $sequence)
                                <div class="shift-item border p-2 mb-2" data-id="{{ $sequence->employee_shift_id }}" data-name="<i class='fa fa-circle mr-2' style='color: {{ $sequence->shift->color }}'></i> {{ ($sequence->shift->shift_name != 'Day Off') ? $sequence->shift->shift_name : __('modules.attendance.' . str($sequence->shift->shift_name)->camel()) }}{{ ($sequence->shift->shift_name != 'Day Off') ? ' ['.$sequence->shift->office_start_time.' - '.$sequence->shift->office_end_time.']' : ''}}">
                                    <input type="hidden" name="sort_order[]" value="{{ $loop->index + 1 }}">
                                    <input type="hidden" name="shifts[]" value="{{ $sequence->shift->id }}">
                                    <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
                                    <i class='fa fa-circle mr-2' style='color: {{ $sequence->shift->color }}'></i> {{ ($sequence->shift->shift_name != 'Day Off') ? $sequence->shift->shift_name : __('modules.attendance.' . str($sequence->shift->shift_name)->camel()) }}{{ ($sequence->shift->shift_name != 'Day Off') ? ' ['.$sequence->shift->office_start_time.' - '.$sequence->shift->office_end_time.']' : ''}}
                                    <span class="remove-shift text-danger float-right" style="cursor: pointer;">&times;</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary class="mr-3" id="update-rotation-form" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('attendance-settings.index', ['tab' => 'shift-rotation'])" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>
    </div>
</div>

<script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}" defer=""></script>
<!-- script for sortable content start-->
<script src="{{ asset('vendor/jquery/jquery-ui.min.js') }}"></script>

<script>
    $(document).ready(function() {
        const rotId = '{{ $shiftRotation->id }}';
        $('a.text-lightest[href="/account/settings/shift-rotations/'+rotId+'"]').contents().unwrap();
        const redirectUrl = "{{ route('attendance-settings.index') }}?tab=shift-rotation";
        $('a.text-lightest[href="/account/settings/shift-rotations"]').attr('href', redirectUrl);

        let color = "{{ $shiftRotation->color_code ? $shiftRotation->color_code : '#7EE7F9' }}";
        $('#colorpicker').colorpicker({
            "color": color
        });

        $('body').on('change', '#rotation_frequency', function() {
            let rotation = $(this).val();
            $('#scheduleOnDiv').toggleClass('d-none', !(rotation == 'weekly' || rotation == 'bi-weekly'));
            $('#dateDiv').toggleClass('d-none', rotation != 'monthly');
        });

        $('#update-rotation-form').click(function() {
            let url = "{{ route('shift-rotations.update', $shiftRotation->id) }}";

            $.easyAjax({
                url: url,
                container: '#update-rotation-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#update-rotation-data-form",
                file: true,
                data: $('#update-rotation-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        let url = "{{ route('attendance-settings.index') }}?tab=shift-rotation";
                        window.location.href = url;
                    }
                }
            });
        });

        $(document).ready(function() {
            $('#rotation_sequence').change(function() {
                if ($(this).val() === "") {
                    $('#add-shift-btn').hide();
                } else {
                    $('#add-shift-btn').show();
                }
            });

            $('#rotation_sequence').trigger('change');
        });

        // Sequence code...
        var sequenceNumber = $('#shiftContainer .shift-item').length + 1;

        $('#add-shift').on('click', function() {
            var selectedOption = $('#rotation_sequence option:selected');
            var shiftId = selectedOption.val();
            var shiftName = selectedOption.data('content');

            if (shiftId) {
                var shiftItem = `<div class="shift-item border p-2 mb-2" data-id="${shiftId}" data-name="${shiftName}" data-sequence="${sequenceNumber}">
                        <input type="hidden" name="sort_order[]" value="${sequenceNumber}">
                        <input type="hidden" name="shifts[]" value="${shiftId}">
                        <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
                        ${shiftName}
                        <span class="remove-shift text-danger float-right" style="cursor: pointer;">&times;</span>
                    </div>`;
                $('#shiftContainer').append(shiftItem);
                sequenceNumber++;
            }
        });

        $('#shiftContainer').sortable({
            placeholder: "ui-state-highlight",
            update: function(event, ui) {
                updateSequenceNumbers();
            }
        });

        $('body').on('click', '.remove-shift', function() {
            $(this).parent().remove();
            updateSequenceNumbers();
        });

        function updateSequenceNumbers() {
            var items = $('#shiftContainer .shift-item');
            items.each(function(index) {
                var shiftId = $(this).data('id');
                var shiftName = $(this).data('name');
                $(this).html(`
                    <input type="hidden" name="sort_order[]" value="${index + 1}">
                    <input type="hidden" name="shifts[]" value="${shiftId}">
                    <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
                    ${shiftName}
                    <span class="remove-shift text-danger float-right" style="cursor: pointer;">&times;</span>
                `);
                $(this).data('sequence', index + 1);
            });
        }

        init(RIGHT_MODAL);
    });
</script>
