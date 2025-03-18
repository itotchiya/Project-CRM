<!DOCTYPE html>

<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/all.min.css') }}">

    <!-- Template CSS -->
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('css/main.css') }}">

    <!-- DatePicker CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/css/datepicker.min.css') }}">

    <title>@lang($pageTitle)</title>
    <meta name="msapplication-TileColor" content="#ffffff">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $company->favicon_url ?? '' }}">
    <meta name="msapplication-TileImage" content="{{ $company->favicon_url ?? '' }}">

    <meta name="theme-color" content="#ffffff">

    @include('sections.theme_css', ['company' => $company])

    @isset($activeSettingMenu)
        <style>
            .preloader-container {
                margin-left: 510px;
                width: calc(100% - 510px)
            }

        </style>
    @endisset

    @stack('styles')

    <style>
        :root {
            --fc-border-color: #E8EEF3;
            --fc-button-text-color: #99A5B5;
            --fc-button-border-color: #99A5B5;
            --fc-button-bg-color: #ffffff;
            --fc-button-active-bg-color: #171f29;
            --fc-today-bg-color: #f2f4f7;
        }

        .fc a[data-navlink] {
            color: #99a5b5;
        }

        img {
            height: 50px;
            margin-top: 20px;
        }

        .box {
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

    </style>

</head>

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->


<body class="{{ isRtl('rtl') }}"><!-- change dark theme class according to application dark theme setting -->

<div class="box">

    <div class="@if($styled == 1) col-md-6 @else col-md-12 @endif">
        @if($withLogo == 1)
            <div class="mb-2 text-center">
                <img src="{{ $company->logo_url }}" alt="{{ $company->company_name }}"
                     class="text-center" height="50px"/>
            </div>
        @endif
        <div class="white-box p-t-20 border-dark">
            <x-form id="createLead" method="POST">
                <div class="form-body">
                    <div class="row">

                        @foreach ($leadFormFields as $item)
                            @if ($item->custom_fields_id === null)

                                @if ($item->field_name == 'product')
                                    <div class="col-md-6">
                                        @if(in_array('products', user_modules()) || in_array('purchase', user_modules()))
                                            <x-forms.select fieldId="product"
                                                            :fieldLabel="__('modules.module.'.$item->field_name)"
                                                            :fieldName="$item->field_name.'[]'"
                                                            :fieldRequired="$item->required == 1"
                                                            search="true" multiple>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </x-forms.select>
                                        @endif
                                    </div>

                                @elseif ($item->field_name == 'country')
                                    <div class="col-md-6">
                                        <x-forms.select fieldId="country"
                                                        :fieldLabel="__('modules.leads.'.$item->field_name)"
                                                        :fieldName="$item->field_name"
                                                        :fieldRequired="$item->required == 1"
                                                        search="true">
                                            <option value="">--</option>
                                            @foreach ($countries as $country)
                                                <option data-tokens="{{ $country->iso3 }}"
                                                        data-content="<span class='flag-icon flag-icon-{{ strtolower($country->iso) }} flag-icon-squared'></span> {{ $country->nicename }}"
                                                        value="{{ $country->nicename }}">{{ $country->nicename }}</option>
                                            @endforeach
                                        </x-forms.select>
                                    </div>
                                @elseif($item->field_name == 'source')
                                    <div class="col-md-6">
                                        <x-forms.select fieldId="source"
                                                        :fieldLabel="__('modules.lead.'.$item->field_name)"
                                                        :fieldName="$item->field_name"
                                                        :fieldRequired="$item->required == 1"
                                                        search="true">
                                            <option value="">--</option>
                                            @foreach ($sources as $source)
                                                <option
                                                    value="{{ $source->id }}">{{ $source->type }}</option>
                                            @endforeach
                                        </x-forms.select>
                                    </div>
                                @elseif ($item->field_name == 'message')
                                    <div class="col-md-6">
                                        <x-forms.textarea :fieldId="$item->field_name"
                                                          :fieldLabel="__('modules.leads.'.$item->field_name)"
                                                          :fieldName="$item->field_name"
                                                          :fieldRequired="$item->required == 1">
                                        </x-forms.textarea>
                                    </div>
                                @else
                                    <div class="col-md-6">
                                        <x-forms.text
                                            :fieldId="$item->field_name"
                                            :fieldLabel="__('modules.leads.'.$item->field_name)"
                                            :fieldName="$item->field_name"
                                            :fieldRequired="$item->required == 1">
                                        </x-forms.text>
                                    </div>
                                @endif
                            @else
                                @if(isset($item->customField->type) && $item->customField->type == 'text')
                                    <div class="col-md-6">
                                        <x-forms.text
                                            fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                            :fieldLabel="$item->field_display_name"
                                            fieldName="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                            :fieldRequired="($item->required === 1) ? true : false">
                                        </x-forms.text>
                                    </div>
                                @elseif($item->customField->type == 'password')
                                    <div class="col-md-6">
                                        <x-forms.password
                                            fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                            :fieldLabel="$item->field_display_name"
                                            fieldName="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                            :fieldPlaceholder="$item->label"
                                            :fieldRequired="($item->required === 1) ? true : false">
                                        </x-forms.password>
                                    </div>
                                @elseif($item->customField->type == 'number')
                                    <div class="col-md-6">
                                        <x-forms.number
                                            fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                            :fieldLabel="$item->field_display_name"
                                            fieldName="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                            :fieldRequired="($item->required === 1) ? true : false">
                                        </x-forms.number>
                                    </div>
                                @elseif($item->customField->type == 'textarea')
                                    <div class="col-md-6">
                                        <x-forms.textarea
                                            :fieldLabel="$item->field_display_name"
                                            fieldName="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                            fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                            :fieldRequired="($item->required === 1) ? true : false">
                                        </x-forms.textarea>
                                    </div>
                                @elseif($item->customField->type == 'radio')
                                    <div class="col-md-6">
                                        <div class="my-3 form-group">
                                            <x-forms.label
                                                fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                                :fieldLabel="$item->field_display_name"
                                                :fieldRequired="($item->required === 1) ? true : false">
                                            </x-forms.label>
                                            <div class="d-flex">
                                                @foreach (json_decode($item->customField->values) as $key => $value)
                                                    <x-forms.radio
                                                        fieldId="optionsRadios{{ $key . $item->customField->id }}"
                                                        :fieldLabel="$value"
                                                        fieldName="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                                        :fieldValue="$value" :checked="($key == 0) ? true : false"/>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @elseif($item->customField->type == 'select')
                                    <div class="col-md-6">
                                        <div class="my-3 form-group">
                                            <x-forms.select
                                                fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                                :fieldLabel="$item->field_display_name"
                                                fieldName="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                                :fieldRequired="($item->required === 1) ? true : false"
                                                search="true">
                                                <option value="">--</option>
                                                @foreach(json_decode($item->customField->values) as $key => $item)
                                                    <option value="{{ $key }}">{{ $item }}</option>
                                                @endforeach
                                            </x-forms.select>
                                        </div>
                                    </div>
                                @elseif($item->customField->type == 'date')
                                    <div class="col-md-6">
                                        <x-forms.datepicker custom="true"
                                                            fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                                            :fieldRequired="($item->required === 1) ? true : false"
                                                            :fieldLabel="$item->field_display_name"
                                                            fieldName="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                                            :fieldValue="now()->timezone($company->timezone)->format($company->date_format)"
                                                            :fieldPlaceholder="$item->label"/>
                                    </div>
                                @elseif($item->customField->type == 'checkbox')
                                    <div class="col-md-6">
                                        <div class="my-3 form-group">
                                            <x-forms.label
                                                fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                                :fieldLabel="$item->field_display_name"
                                                :fieldRequired="($item->required === 1) ? true : false">
                                            </x-forms.label>
                                            <div class="d-flex checkbox-{{$item->customField->id}}">
                                                <input type="hidden"
                                                       name="custom_fields_data[{{$item->field_name.'_'.$item->customField->id}}]"
                                                       id="{{$item->field_name.'_'.$item->customField->id}}">
                                                @foreach (json_decode($item->customField->values) as $key => $value)
                                                    <x-forms.checkbox
                                                        fieldId="optionsRadios{{ $key . $item->customField->id }}"
                                                        :fieldLabel="$value"
                                                        :fieldName="$item->field_name.'_'.$item->customField->id.'[]'"
                                                        :fieldValue="$value"
                                                        onchange="checkboxChange('checkbox-{{$item->customField->id}}', '{{$item->field_name.'_'.$item->customField->id}}')"
                                                        :fieldRequired="($item->required === 1) ? true : false"/>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @elseif ($item->customField->type == 'file')
                                    <div class="col-md-6">
                                        <input type="hidden"
                                               name="custom_fields_data[{{$item->field_name.'_'.$item->customField->id}}]">
                                        <x-forms.file
                                            :fieldLabel="$item->field_display_name"
                                            :fieldRequired="($item->required === 1) ? true : false"
                                            :fieldName="'custom_fields_data[' . $item->field_name . '_' . $item->customField->id . ']'"
                                            :fieldId="'custom_fields_data[' . $item->field_name . '_' . $item->customField->id . ']'"
                                            fieldValue=""
                                        />
                                    </div>
                                @endif
                            @endif
                        @endforeach

                        @if($globalSetting->google_recaptcha_status == 'active' && $globalSetting->google_recaptcha_v2_status == 'active')
                            <div class="mt-2 mb-2 col-md-12 col-lg-12" id="captcha_container"></div>
                        @endif

                        {{-- This is used for google captcha v3 --}}
                        <input type="hidden" id="g_recaptcha" name="g_recaptcha">

                        @if ($errors->has('g-recaptcha-response'))
                            <div class="help-block with-errors">{{ $errors->first('g-recaptcha-response') }}</div>
                        @endif

                    </div>
                </div>
                <br><br>
                <input type="hidden" name="company_id" value="{{ $company->id }}">
                <div class="form-actions">
                    <button type="button" id="save-form" class="btn btn-primary"><i
                            class="fa fa-check"></i> @lang('app.save')</button>
                    <button type="reset" class="ml-3 btn btn-secondary">@lang('app.reset')</button>
                </div>
            </x-form>

            <div class="row">
                <div class="col-sm-12 col-md-12">
                    <div class="alert alert-success" id="success-message" style="display:none"></div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>


<!-- jQuery -->
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>

<!-- Global Required Javascript -->
<script src="{{ asset('vendor/bootstrap/javascript/bootstrap-native.js') }}"></script>

<!-- Font Awesome -->
<script src="{{ asset('vendor/jquery/all.min.js') }}"></script>

<!-- Template JS -->
<script src="{{ asset('js/main.js') }}"></script>

<script>
    const MODAL_LG = '#myModal';
    const MODAL_XL = '#myModalXl';
    document.loading = '@lang('app.loading')';
    const dropifyMessages = {
        default: "@lang('app.dragDrop')",
        replace: "@lang('app.dragDropReplace')",
        remove: "@lang('app.remove')",
        error: "@lang('messages.errorOccured')",
    };

    function checkboxChange(checkboxClass, hiddenFieldId) {
        const selectedValues = $(`.${checkboxClass} input:checked`).map(function() {
            return $(this).val();
        }).get();

        $(`#${hiddenFieldId}`).val(selectedValues.join(','));
    }

    $(window).on('load', function () {
        // Animate loader off screen
        init();
        $(".preloader-container").fadeOut("slow", function () {
            $(this).removeClass("d-flex");
        });
    });

    const datepickerConfig = {
        formatter: (input, date, instance) => {
            input.value = moment(date).format('{{ $globalSetting->moment_format }}')
        },
        showAllDates: true,
        customDays: {!!  json_encode(\App\Models\GlobalSetting::getDaysOfWeek())!!},
        customMonths: {!!  json_encode(\App\Models\GlobalSetting::getMonthsOfYear())!!},
        customOverlayMonths: {!!  json_encode(\App\Models\GlobalSetting::getMonthsOfYear())!!},
        overlayButton: "@lang('app.submit')",
        overlayPlaceholder: "@lang('app.enterYear')",
        startDay: parseInt("{{ attendance_setting()->week_start_from }}")
    };
</script>
<script>

    $('.custom-date-picker').each(function (ind, el) {
        datepicker(el, {
            position: 'bl',
            ...datepickerConfig
        });
    });

    $(".select-picker").selectpicker();

    $('#save-form').click(function () {
        $.easyAjax({
            url: "{{route('front.lead_store')}}",
            container: '#createLead',
            type: "POST",
            redirect: true,
            disableButton: true,
            file: true,
            blockUI: true,
            data: $('#createLead').serialize(),
            success: function (response) {
                if (response.status == "success") {
                    $('#createLead')[0].reset();
                    $('#createLead').hide();
                    $('#success-message').html(response.message);
                    $('#success-message').show();
                }
            }
        })
    });

</script>

@if($globalSetting->google_recaptcha_status == 'active' && $globalSetting->google_recaptcha_v2_status == 'active')
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
            async defer></script>
    <script>
        var gcv3;
        var onloadCallback = function () {
            // Renders the HTML element with id 'captcha_container' as a reCAPTCHA widget.
            // The id of the reCAPTCHA widget is assigned to 'gcv3'.
            gcv3 = grecaptcha.render('captcha_container', {
                'sitekey': '{{$globalSetting->google_recaptcha_v2_site_key}}',
                'theme': 'light',
                'callback': function (response) {
                    if (response) {
                        $('#g_recaptcha').val(response);
                    }
                },
            });
        };
    </script>
@endif

@if($globalSetting->google_recaptcha_status == 'active' && $globalSetting->google_recaptcha_v3_status == 'active')
    <script
        src="https://www.google.com/recaptcha/api.js?render={{$globalSetting->google_recaptcha_v3_site_key}}"></script>
    <script>
        grecaptcha.ready(function () {
            grecaptcha.execute('{{$globalSetting->google_recaptcha_v3_site_key}}').then(function (token) {
                // Add your logic to submit to your backend server here.
                $('#g_recaptcha').val(token);
            });
        });
    </script>
@endif

</html>
