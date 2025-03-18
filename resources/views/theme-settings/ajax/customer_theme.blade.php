<div class="col-lg-12">
    <h4>@lang('modules.themeSettings.adminPanelTheme')</h4>
</div>

<div class="col-lg-6">
    <div class="form-group my-3">
        <x-forms.label fieldId="colorselector" fieldRequired="true"
                       :fieldLabel="__('modules.themeSettings.headerColor')">
        </x-forms.label>
        <x-forms.input-group class="color-picker">
            <input type="text" class="form-control height-35 f-14 header_color"
                   value="{{ $adminTheme->header_color }}"
                   placeholder="{{ __('placeholders.colorPicker') }}" name="primary_color[]">

            <x-slot name="append">
                <span class="input-group-text height-35 colorpicker-input-addon"><i></i></span>
            </x-slot>
        </x-forms.input-group>
    </div>
</div>

<div class="col-lg-6">
    <div class="form-group my-3">
        <x-forms.label fieldId="late_yes" :fieldLabel="__('modules.themeSettings.sidebarTheme')">
        </x-forms.label>
        <div class="d-flex">
            <x-forms.radio fieldId="sidebar_dark_1" :fieldLabel="__('modules.themeSettings.dark')"
                           fieldName="theme_settings[1][sidebar_theme]" fieldValue="dark"
                           class="sidebar_theme"
                           :checked="($adminTheme->sidebar_theme == 'dark')">
            </x-forms.radio>
            <x-forms.radio fieldId="sidebar_light_1" :fieldLabel="__('modules.themeSettings.light')"
                           fieldValue="light" :checked="($adminTheme->sidebar_theme == 'light')"
                           class="sidebar_theme"
                           fieldName="theme_settings[1][sidebar_theme]"></x-forms.radio>
        </div>
    </div>
</div>


<div class="col-lg-12 mt-3">
    <h4>@lang('modules.themeSettings.employeePanelTheme')</h4>
</div>

<div class="col-lg-6">
    <div class="form-group my-3">
        <x-forms.label fieldId="colorselector" fieldRequired="true"
                       :fieldLabel="__('modules.themeSettings.headerColor')">
        </x-forms.label>
        <x-forms.input-group class="color-picker">
            <input type="text" class="form-control height-35 f-14 header_color"
                   value="{{ $employeeTheme->header_color }}"
                   placeholder="{{ __('placeholders.colorPicker') }}" name="primary_color[]">

            <x-slot name="append">
                <span class="input-group-text height-35 colorpicker-input-addon"><i></i></span>
            </x-slot>
        </x-forms.input-group>
    </div>
</div>

<div class="col-lg-6">
    <div class="form-group my-3">
        <x-forms.label fieldId="late_yes" :fieldLabel="__('modules.themeSettings.sidebarTheme')">
        </x-forms.label>
        <div class="d-flex">
            <x-forms.radio fieldId="sidebar_dark_3" :fieldLabel="__('modules.themeSettings.dark')"
                           fieldName="theme_settings[3][sidebar_theme]" class="sidebar_theme"
                           fieldValue="dark"
                           :checked="($employeeTheme->sidebar_theme == 'dark')">
            </x-forms.radio>
            <x-forms.radio fieldId="sidebar_light_3" class="sidebar_theme"
                           :fieldLabel="__('modules.themeSettings.light')" fieldValue="light"
                           :checked="($employeeTheme->sidebar_theme == 'light')"
                           fieldName="theme_settings[3][sidebar_theme]"></x-forms.radio>
        </div>
    </div>
</div>


<div class="col-lg-12 mt-3">
    <h4>@lang('modules.themeSettings.clientPanelTheme')</h4>
</div>

<div class="col-lg-6">
    <div class="form-group my-3">
        <x-forms.label fieldId="colorselector" fieldRequired="true"
                       :fieldLabel="__('modules.themeSettings.headerColor')">
        </x-forms.label>
        <x-forms.input-group class="color-picker">
            <input type="text" class="form-control height-35 f-14 header_color"
                   value="{{ $clientTheme->header_color }}"
                   placeholder="{{ __('placeholders.colorPicker') }}" name="primary_color[]">

            <x-slot name="append">
                <span class="input-group-text height-35 colorpicker-input-addon"><i></i></span>
            </x-slot>
        </x-forms.input-group>
    </div>
</div>

<div class="col-lg-6">
    <div class="form-group my-3">
        <x-forms.label fieldId="late_yes" :fieldLabel="__('modules.themeSettings.sidebarTheme')">
        </x-forms.label>
        <div class="d-flex">
            <x-forms.radio fieldId="sidebar_dark_4" :fieldLabel="__('modules.themeSettings.dark')"
                           fieldName="theme_settings[4][sidebar_theme]" fieldValue="dark"
                           class="sidebar_theme"
                           :checked="($clientTheme->sidebar_theme == 'dark')">
            </x-forms.radio>
            <x-forms.radio fieldId="sidebar_light_4" :fieldLabel="__('modules.themeSettings.light')"
                           fieldValue="light" fieldName="theme_settings[4][sidebar_theme]"
                           class="sidebar_theme"
                           :checked="($clientTheme->sidebar_theme == 'light')">
            </x-forms.radio>
        </div>
    </div>
</div>

