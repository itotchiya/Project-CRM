@extends('layouts.app')

@push('styles')
    <style>
        .form_custom_label {
            justify-content: left;
        }
        .client{
            margin: auto;
        }

    </style>
@endpush

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
                @method('PUT')
                <div class="row">
                    <div class="col-lg-6 mb-2">
                        <x-forms.checkbox :fieldLabel="__('modules.messages.allowClientEmployeeChat')"
                            fieldName="allow_client_employee" fieldId="allow-client-employee" fieldValue="yes"
                            fieldRequired="true" :checked="$messageSettings->allow_client_employee == 'yes'" />
                    </div>

                    <div class="col-lg-6 mb-2">
                        <x-forms.checkbox :fieldLabel="__('modules.messages.allowClientAdminChat')"
                            fieldName="allow_client_admin" fieldId="allow-client-admin" fieldValue="yes"
                            fieldRequired="true" :checked="$messageSettings->allow_client_admin == 'yes'" />
                    </div>
                    <div class="col-lg-12 mb-2">
                        <div id="restrict_client" class="row client @if ($messageSettings->allow_client_employee == 'no') d-none @endif">
                            <x-forms.radio fieldId="restrict_client-no" :fieldLabel="__('app.all')" fieldValue="no"
                                fieldName="restrict_client" :checked="$messageSettings->restrict_client == 'no'">
                            </x-forms.radio>

                            <x-forms.radio fieldId="restrict_client-yes" :fieldLabel="__('modules.messages.allowClientProjectEmployeeChat')" fieldValue="yes"
                                fieldName="restrict_client" :checked="$messageSettings->restrict_client == 'yes'">
                            </x-forms.radio>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-2">
                        <x-forms.select fieldId="send_sound_notification" fieldName="send_sound_notification" :fieldLabel="__('modules.messages.sendSoundNotification')" :popover="__('modules.messages.soundNotificationInfo')">
                            <option {{ message_setting()->send_sound_notification == 0 ? 'selected' : '' }} value="0">@lang('app.no')</option>
                            <option {{ message_setting()->send_sound_notification == 1 ? 'selected' : '' }} value="1">@lang('app.yes')</option>
                        </x-forms.select>
                    </div>

                </div>
            </div>

            <x-slot name="action">
                <!-- Buttons Start -->
                <div class="w-100 border-top-grey">
                    <x-setting-form-actions>
                        <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
                        </x-forms.button-primary>
                    </x-setting-form-actions>
                </div>
                <!-- Buttons End -->
            </x-slot>

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
    <script>

            $('#allow-client-employee').on('change', function() {
                $('#restrict_client').toggleClass('d-none');
            });

            $('#save-form').click(function() {
                $.easyAjax({
                    url: "{{ route('message-settings.update', [1]) }}",
                    container: '#editSettings',
                    type: "POST",
                    data: $('#editSettings').serialize()
                })
            });

    </script>
@endpush
