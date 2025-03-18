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
    <meta name="msapplication-TileImage" content="{{ $company->favicon ?? '' }}">

    <meta name="theme-color" content="#ffffff">

    @include('sections.theme_css')

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

        body {
            overflow-x: hidden;
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


<body class="{{ isRtl('rtl') }}">
<!-- change dark theme class according to application dark theme setting -->
<div class="box">
    <div class="@if($styled==1) col-md-6 @else col-md-12 @endif">
        @if($withLogo==1)
            <div class="text-center">
                <img src="{{ $company->logo_url }}" alt="{{ $company->company_name }}"
                     class="text-center"/>
            </div>
        @endif
        <x-form id="createTicket" method="POST">
            <div class="form-body">
                <div class="row">
                    @foreach ($ticketFormFields as $item)
                        @if ($item->custom_fields_id === null)
                            @if ($item->field_type == 'textarea')
                                <div class="col-lg-12">
                                    <x-forms.textarea :fieldId="$item->field_name"
                                                      :fieldLabel="__('modules.tickets.'.$item->field_name)"
                                                      :fieldName="$item->field_name"
                                                      :fieldRequired="$item->required == 1">
                                    </x-forms.textarea>
                                </div>
                            @elseif($item->field_type == 'select')
                                @if ($item->field_name == 'type')
                                    <div class="col-lg-12">
                                        <x-forms.select :fieldId="$item->field_name"
                                                        :fieldLabel="__('modules.tickets.'.$item->field_name)"
                                                        :fieldName="$item->field_name" search="true" alignRight="true"
                                                        :fieldRequired="$item->required == 1">
                                            @forelse($types as $type)
                                                <option value="{{ $type->id }}">{{ $type->type }}
                                                </option>
                                            @empty
                                                <option value="">@lang('messages.noTicketTypeAdded')</option>
                                            @endforelse
                                        </x-forms.select>
                                    </div>
                                @elseif ($item->field_name == 'priority')
                                    <div class="col-lg-12">
                                        <x-forms.select :fieldId="$item->field_name"
                                                        :fieldLabel="__('modules.tickets.'.$item->field_name)"
                                                        :fieldName="$item->field_name" search="true" alignRight="true"
                                                        :fieldRequired="$item->required == 1">
                                            <option value="low">@lang('app.low')</option>
                                            <option value="medium">@lang('app.medium')</option>
                                            <option value="high">@lang('app.high')</option>
                                            <option value="urgent">@lang('app.urgent')</option>
                                        </x-forms.select>
                                    </div>
                                @else
                                    <div class="col-lg-12">
                                        <x-forms.select :fieldId="$item->field_name"
                                                        :fieldLabel="__('modules.tickets.'.$item->field_name)"
                                                        :fieldName="$item->field_name" search="true" alignRight="true"
                                                        :fieldRequired="$item->required == 1">
                                            @foreach($groups as $group)
                                                <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                            @endforeach
                                        </x-forms.select>
                                    </div>
                                @endif
                            @else
                                <div class="col-md-12">
                                    <x-forms.text :fieldId="$item->field_name"
                                                  :fieldLabel="__('modules.tickets.'.$item->field_name)"
                                                  :fieldName="$item->field_name" fieldPlaceholder=""
                                                  :fieldRequired="$item->required == 1">
                                    </x-forms.text>
                                </div>
                            @endif
                        @else
                            @if($item->field_type == 'text')
                                <div class="col-md-6">
                                    <x-forms.text
                                        fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                        :fieldLabel="$item->field_display_name"
                                        fieldName="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                        :fieldRequired="($item->required === 1) ? true : false">>
                                    </x-forms.text>
                                </div>
                            @elseif($item->field_type == 'password')
                                <div class="col-md-6">
                                    <x-forms.password
                                        fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                        :fieldLabel="$item->field_display_name"
                                        fieldName="custom_fields_data[{{ $item->name . '_' . $item->id }}]"
                                        :fieldPlaceholder="$item->label"
                                        :fieldRequired="($item->required === 1) ? true : false">
                                    </x-forms.password>
                                </div>
                            @elseif($item->field_type == 'number')
                                <div class="col-md-6">
                                    <x-forms.number
                                        fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                        :fieldLabel="$item->field_display_name"
                                        fieldName="custom_fields_data[{{ $item->name . '_' . $item->id }}]"
                                        :fieldPlaceholder="$item->label"
                                        :fieldRequired="($item->required === 1) ? true : false">
                                    </x-forms.number>
                                </div>
                            @elseif($item->field_type == 'textarea')
                                <div class="col-md-6">
                                    <x-forms.textarea
                                        :fieldLabel="$item->field_display_name"
                                        fieldName="custom_fields_data[{{ $item->name . '_' . $item->id }}]"
                                        fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                        :fieldRequired="($item->required === 1) ? true : false"
                                        :fieldPlaceholder="$item->label">
                                    </x-forms.textarea>
                                </div>
                            @elseif($item->field_type == 'radio')
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
                            @elseif($item->field_type == 'select')
                                <div class="col-md-6">
                                    <div class="my-3 form-group">
                                        <x-forms.select
                                            fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                            :fieldLabel="$item->field_display_name"
                                            fieldName="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                            :fieldRequired="$item->required == 1"
                                            search="true">
                                            <option value="">--</option>
                                            @foreach(json_decode($item->customField->values) as $key => $item)
                                                <option value="{{ $key }}">{{ $item }}</option>
                                            @endforeach
                                        </x-forms.select>
                                    </div>
                                </div>
                            @elseif($item->field_type == 'date')
                                <div class="col-md-6">
                                    <x-forms.datepicker custom="true"
                                                        fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                                        :fieldRequired="($item->required === 1) ? true : false"
                                                        :fieldLabel="$item->field_display_name"
                                                        fieldName="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                                        :fieldValue="now()->timezone($company->timezone)->format($company->date_format)"
                                                        :fieldPlaceholder="$item->label"/>
                                </div>
                            @elseif($item->field_type == 'checkbox')
                                <div class="col-md-6">
                                    <div class="my-3 form-group">
                                        <x-forms.label
                                            fieldId="custom_fields_data[{{ $item->field_name . '_' . $item->customField->id }}]"
                                            :fieldLabel="$item->field_display_name"
                                            :fieldRequired="($item->required === 1) ? true : false">
                                        </x-forms.label>
                                        <div class="d-flex checkbox-{{$item->id}}">
                                            <input type="hidden"
                                                   name="custom_fields_data[{{$item->name.'_'.$item->id}}]"
                                                   id="{{$item->name.'_'.$item->id}}">
                                            @foreach (json_decode($item->customField->values) as $key => $value)
                                                <x-forms.checkbox fieldId="optionsRadios{{ $key . $item->id }}"
                                                                  :fieldLabel="$value"
                                                                  fieldName="$item->field_name.'_'.$item->customField->id.'[]'"
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

                    @if (global_setting()->google_recaptcha_status == 'active' && global_setting()->google_recaptcha_v2_status == 'active')
                        <div class="mt-2 col-md-12 col-lg-12" id="captcha_container"></div>
                    @endif

                    {{-- This is used for google captcha v3 --}}
                    <input type="hidden" id="g_recaptcha" name="g_recaptcha">

                    @if ($errors->has('g-recaptcha-response'))
                        <div class="help-block with-errors">{{ $errors->first('g-recaptcha-response') }}</div>
                    @endif


                </div>
            </div>
            <input type="hidden" name="company_id" value="{{ $company->id }}">
            <div class="mt-4 mb-4 form-actions">
                <button type="button" id="save-form" class="mr-3 btn btn-primary"><i class="fa fa-check"></i>
                    @lang('app.save')</button>
                <button type="reset" class="btn btn-secondary">@lang('app.reset')</button>
            </div>
        </x-form>

        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-success" id="success-message" style="display:none"></div>
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
<script src="{{ asset('vendor/froiden-helper/helper.js') }}"></script>

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

    $(window).on('load', function () {
        // Animate loader off screen
        init();
        $(".preloader-container").fadeOut("slow", function () {
            $(this).removeClass("d-flex");
        });
    });

    const datepickerConfig = {
        formatter: (input, date, instance) => {
            input.value = moment(date).format('{{ $company->moment_format }}')
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
            url: "{{ route('front.ticket_store') }}",
            container: '#createTicket',
            type: "POST",
            redirect: true,
            disableButton: true,
            blockUI: true,
            file: true,
            data: $('#createTicket').serialize(),
            success: function (response) {
                if (response.status == "success") {
                    $('#createTicket')[0].reset();
                    $('#createTicket').hide();
                    $('#success-message').html(response.message);
                    $('#success-message').show();
                }
            }
        })
    });
</script>

@if (global_setting()->google_recaptcha_status == 'active' && global_setting()->google_recaptcha_v2_status == 'active')
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
    <script>
        var gcv3;
        var onloadCallback = function () {
            // Renders the HTML element with id 'captcha_container' as a reCAPTCHA widget.
            // The id of the reCAPTCHA widget is assigned to 'gcv3'.
            gcv3 = grecaptcha.render('captcha_container', {
                'sitekey': '{{ global_setting()->google_recaptcha_v2_site_key }}',
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

@if (global_setting()->google_recaptcha_status == 'active' && global_setting()->google_recaptcha_v3_status == 'active')
    <script
        src="https://www.google.com/recaptcha/api.js?render={{ global_setting()->google_recaptcha_v3_site_key }}"></script>
    <script>
        grecaptcha.ready(function () {
            grecaptcha.execute('{{ global_setting()->google_recaptcha_v3_site_key }}').then(function (token) {
                // Add your logic to submit to your backend server here.
                $('#g_recaptcha').val(token);
            });
        });
    </script>
@endif

</html>
