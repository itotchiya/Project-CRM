<style>
    .preloader-container {
        margin-left: 260px !important;
        width: calc(100% - 260px) !important;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-automated-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.menu.addAutomateShift')</h4>
                <div class="row p-20">
                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="department_id" :fieldLabel="__('app.department')" fieldName="department_id"
                            search="true">
                            <option value="0">--</option>
                            @foreach ($departments as $team)
                                <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-9">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="selectEmployee" :fieldLabel="__('app.menu.employees')" fieldRequired="true">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control multiple-users" multiple name="user_id[]"
                                    id="selectEmployee" data-live-search="true" data-size="8">
                                    @foreach ($employees as $item)
                                        <x-user-option :user="$item" :pill="true" />
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>
                    </div>
                </div>

                <div class="row px-4 pb-4">
                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="rotation" :fieldLabel="__('app.rotationName')" fieldName="rotation" fieldRequired="true" search="true">
                            @foreach ($shiftRotation as $item)
                                <option
                                    data-content="<i class='fa fa-circle mr-2' style='color: {{ $item->color_code }}'></i>
                                    {{ $item->rotation_frequency != 'monthly' ? $item->rotation_name . ' [ ' . $item->rotation_frequency . ' ' . __('app.on') . ' ' . $item->schedule_on . ' ]' : $item->rotation_name . ' [ ' . $item->rotation_frequency . ' ' . __('app.onDate') . ' ' . $item->rotation_date .' ]' }}"
                                    value="{{ $item->id }}">
                                    {{ $item->rotation_frequency != 'monthly' ? $item->rotation_name . ' [ ' . $item->rotation_frequency . ' ' . __('app.on') . ' ' . $item->schedule_on . ' ]' : $item->rotation_name . ' [ ' . $item->rotation_frequency . ' ' . __('app.onDate') . ' ' . $item->rotation_date .' ]' }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary class="mr-3" id="save-automated-form" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('attendance-settings.index', ['tab' => 'shift-rotation'])" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        const redirectUrl = "{{ route('attendance-settings.index') }}?tab=shift-rotation";
        $('a.text-lightest[href="/account/settings/shift-rotations"]').attr('href', redirectUrl);

        $("#selectEmployee").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        $('#department_id').change(function() {
            var id = $(this).val();
            var rotation = $('#rotation').val();
            var url = "{{ route('employees.by_department', ':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                url: url,
                container: '#save-automated-data-form',
                type: "GET",
                blockUI: true,
                data: {
                    request_from: 'rotation',
                    department_id: id,
                    rotation: rotation,
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#selectEmployee').html(response.data);
                        $('#selectEmployee').selectpicker('refresh');
                    }
                }
            });
        });

        $('#save-automated-form').click(function() {
            const url = "{{ route('shift-rotations.store_automate_shift') }}";

            $.easyAjax({
                url: url,
                container: '#save-automated-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-automated-form",
                data: $('#save-automated-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        let url = "{{ route('attendance-settings.index') }}?tab=shift-rotation";
                        window.location.href = url;
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
