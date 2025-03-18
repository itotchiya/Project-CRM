<style>
    .role-permission-select .btn {
        border: none;
        width: auto;
    }

    .permisison-table .thead-light {
        top: 107px;
        z-index: unset;
    }


</style>

<x-alert type="warning" @class(['mt-4', 'd-none' => !($employee->customised_permissions)])>
    <div class="d-flex justify-content-between">
        <div class="pt-2">
            <i class="fa fa-exclamation-triangle"></i> @lang('messages.customPermissionError')
        </div>
        <x-forms.button-secondary id="reset-user-permissions" icon="sync">@lang('app.resetPermissions')</x-forms.button-secondary>
    </div>
</x-alert>

@if ($employee->hasRole('admin'))
    <x-alert type="danger" class="mt-5" icon="exclamation-triangle">
        @lang('messages.adminPermissionError')
    </x-alert>
@else
   
    <x-table class="table-bordered table-hover mt-4 permisison-table bg-white rounded" headType="thead-light">
        <x-slot name="thead">
            <th width="20%">
                @lang('app.module')
            </th>
            <th width="16%">@lang('app.add')</th>
            <th width="16%">@lang('app.view')</th>
            <th width="16%">@lang('app.update')</th>
            <th width="16%">@lang('app.delete')</th>
            <th width="16%"></th>
        </x-slot>
        @foreach ($modulesData as $moduleData)
            @php
                $notPermited = !in_array($moduleData->module_name, $employeeModules) ? 'disabled' : null;
            @endphp
            <tr>
                
                <td>@lang('modules.module.'.$moduleData->module_name)
                    @if($notPermited)
                        <i class="fa fa-info-circle" data-toggle="popover" data-placement="top" data-content="@lang('messages.moduleDisabled')" data-html="true" data-trigger="hover"></i>
                    @endif
                </td>
                @foreach ($moduleData->permissions as $permission)
                    @php
                        $permissionType = $employee->customised_permissions === 1 ? $employee->permissionTypeId($permission->name) : $role->permissionType($permission->id);
                        $allowedPermissions = json_decode($permission->allowed_permissions);
                    @endphp
                    <td>
                        <select class="role-permission-select border-0" data-permission-id="{{ $permission->id }}" {{ $notPermited }}>
                            @if (!is_null($allowedPermissions))
                                @foreach ($allowedPermissions as $key => $item)
                                    @php
                                        $type = $employee->customised_permissions === 1 ? $key : $item;
                                    @endphp
                                    <option @if ($permissionType == $type) selected @endif
                                    @if (!$permissionType && $item == 5) selected @endif value="{{ $item }}">
                                    @lang('app.'.$key)</option>
                                @endforeach
                            @endif
                        </select>
                    </td>
                @endforeach

                @if (count($moduleData->permissions) < 4)
                    @for ($i = 1; $i <= 4 - count($moduleData->permissions); $i++) <td>--</td> @endfor
                @endif

                <td class="text-center bg-light border-left">
                    <div class="p-2">
                        @if ($moduleData->custom_permissions_count > 0  && in_array($moduleData->module_name,$employeeModules))
                            <a href="javascript:;" data-module-id="{{ $moduleData->id }}"
                                class="text-dark-grey show-custom-permission dropdown-toggle">
                                @lang('app.more') <i class="fa fa-chevron-down"></i>
                            </a>
                        @else
                            &nbsp;
                        @endif
                    </div>
                </td>


            </tr>

        @endforeach
    </x-table>

    <script>
        $('body').on('click', '.show-custom-permission', function() {
            var moduleRow = $(this).closest('tr');
            var moduleId = $(this).data('module-id');
            var url = "{{ route('user-permissions.custom_permissions', $employee->id) }}";
            var showCustomPermissionButton = $(this);

            $.easyAjax({
                url: url,
                blockUI: true,
                container: '.main-container',
                type: "POST",
                data: {
                    'moduleId': moduleId,
                    '_token': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status == 'success') {
                        if ($('table.permisison-table tbody #module-custom-permission-' + moduleId)
                            .length > 0) {
                            $('table.permisison-table tbody #module-custom-permission-' + moduleId)
                                .remove();
                        } else {
                            moduleRow.after(response.html);
                        }
                        showCustomPermissionButton
                            .find(".svg-inline--fa")
                            .toggleClass("fa-chevron-down fa-chevron-up");
                    }
                }
            });
        });

        $('body').on('change', '.role-permission-select', function() {
            var permissionId = $(this).data('permission-id');
            var permissionType = $(this).val();
            var url = "{{ route('user-permissions.update', $employee->id) }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: '.main-container',
                type: "POST",
                data: {
                    '_method': 'PUT',
                    'permissionId': permissionId,
                    'permissionType': permissionType,
                    'permissionCustomised': 1,
                    '_token': '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#reset-user-permissions').closest('.alert').removeClass('d-none');
                }
            });
        });

        $('body').on('click', '#reset-user-permissions', function() {
            var url = "{{ route('user-permissions.reset_permissions', $employee->id) }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: '.main-container',
                type: "POST",
                data: {
                    '_token': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.reload();
                    }
                }
            });
        });

    </script>
@endif
