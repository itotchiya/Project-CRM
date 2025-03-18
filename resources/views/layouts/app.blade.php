<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="icon" type="image/png" sizes="16x16" href="{{ companyOrGlobalSetting()->favicon_url }}">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/all.min.css') }}" defer="defer">

    <!-- Simple Line Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/simple-line-icons.css') }}" defer="defer">

    <!-- Datepicker -->
    <link rel="stylesheet" href="{{ asset('vendor/css/datepicker.min.css') }}" defer="defer">

    <!-- TimePicker -->
    <link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-timepicker.min.css') }}" defer="defer">

    <!-- Select Plugin -->
    <link rel="stylesheet" href="{{ asset('vendor/css/select2.min.css') }}" defer="defer">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-icons.css') }}" defer="defer">

    @stack('datatable-styles')

    <!-- Template CSS -->
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('css/main.css') }}">

    <title>{{ is_array(__($pageTitle)) ? $pageTitle : __($pageTitle) }}</title>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ companyOrGlobalSetting()->favicon_url }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @isset($activeSettingMenu)
        <style>
            .preloader-container {
                margin-left: 510px;
                width: calc(100% - 510px)
            }

            .blur-code {
                filter: blur(3px);

            }

            .purchase-code {
                transition: filter .2s ease-out;
                margin-right: 4px;
            }
            .ql-editor {
                text-align: left;
                white-space: unset;
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

        .ql-editor p {
            line-height: 1.42;
            margin: revert;
        }

        .ql-container .ql-tooltip {
            left: 8.5px !important;
            top: -17px !important;
        }

        .table [contenteditable="true"] {
            height: 55px;
        }

        .table [contenteditable="true"]:hover::after {
            content: "{{ __('app.clickEdit') }}" !important;
        }

        .table [contenteditable="true"]:focus::after {
            content: "{{ __('app.anywhereSave') }}" !important;
        }

        .table-bordered .displayName {
            padding: 17px;
        }

        p {
            word-break: break-word;
        }
        .inactive{
            opacity: 0.7;
        }
    </style>

    {{-- Custom theme styles --}}
    @if (!user()->dark_theme)
        @include('sections.theme_css')
    @endif

    @if (file_exists(public_path() . '/css/app-custom.css'))
        <link href="{{ asset('css/app-custom.css') }}" rel="stylesheet">
    @endif

    @if (file_exists(public_path() . '/css/theme-custom.css'))
        <link href="{{ asset('css/theme-custom.css') }}" rel="stylesheet">
    @endif

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/modernizr.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/bootstrap-colorpicker.js') }}"></script>

    {{-- Timepicker --}}
    <script src="{{ asset('vendor/jquery/bootstrap-timepicker.min.js') }}" defer="defer"></script>

    @includeif('sections.push-setting-include')

    {{-- Include file for widgets if exist --}}
    @includeif('sections.custom_script')

    <script>
        const checkMiniSidebar = localStorage.getItem("mini-sidebar");
    </script>

</head>


<body id="body" class="{{ user()->dark_theme ? 'dark-theme' : '' }} {{ isRtl('rtl') }}">
<script>
    if (checkMiniSidebar == "yes" || checkMiniSidebar == "") {
        $('body').addClass('sidebar-toggled');
    }
</script>
{{-- include topbar --}}
@include('sections.topbar')

{{-- include sidebar menu --}}
@include('sections.sidebar')

<!-- BODY WRAPPER START -->
<div class="clearfix body-wrapper">


    <!-- MAIN CONTAINER START -->
    <section class="mb-5 main-container bg-additional-grey mb-sm-0" id="fullscreen">

        <div class="preloader-container d-flex justify-content-center align-items-center">
            <div class="spinner-border" role="status" aria-hidden="true"></div>
        </div>


        @yield('filter-section')

        <x-app-title class="d-block d-lg-none" :pageTitle="$pageTitle"></x-app-title>

        @yield('content')


    </section>
    <!-- MAIN CONTAINER END -->
</div>
<!-- BODY WRAPPER END -->
@include('sections.modals')

<!-- Global Required Javascript -->
<script src="{{ asset('js/main.js') }}"></script>
<script>
    // Translation of default values for the select picker box.
    $.fn.selectpicker.Constructor.DEFAULTS.noneSelectedText = "@lang('placeholders.noneSelectedText')";
    $.fn.selectpicker.Constructor.DEFAULTS.noneResultsText = "@lang('placeholders.noneResultsText')";
    $.fn.selectpicker.Constructor.DEFAULTS.selectAllText = "@lang('placeholders.selectAllText')";
    $.fn.selectpicker.Constructor.DEFAULTS.deselectAllText = "@lang('placeholders.deselectAllText')";

    const MODAL_DEFAULT = '#myModalDefault';
    const MODAL_LG = '#myModal';
    const MODAL_XL = '#myModalXl';
    const MODAL_HEADING = '#modelHeading';
    const RIGHT_MODAL = '#task-detail-1';
    const RIGHT_MODAL_CONTENT = '#right-modal-content';
    const RIGHT_MODAL_TITLE = '#right-modal-title';
    const company = @json(companyOrGlobalSetting());
    const pusher_setting = @json(pusher_settings());
    const message_setting = @json(message_setting());
    const SEARCH_KEYWORD = "{{ request('search_keyword') }}";
    const MOMENTJS_TIME_FORMAT = "{{ (companyOrGlobalSetting()->time_format == 'h:i A') ? 'hh:mm A' : ( (companyOrGlobalSetting()->time_format == 'h:i a') ? 'hh:mm a' : 'H:mm') }}";

    const datepickerConfig = {
        formatter: (input, date, instance) => {
            input.value = moment(date).format('{{ companyOrGlobalSetting()->moment_date_format }}')
        },
        showAllDates: true,
        customDays: {!!  json_encode(\App\Models\GlobalSetting::getDaysOfWeek())!!},
        customMonths: {!!  json_encode(\App\Models\GlobalSetting::getMonthsOfYear())!!},
        customOverlayMonths: {!!  json_encode(\App\Models\GlobalSetting::getMonthsOfYear())!!},
        overlayButton: "@lang('app.submit')",
        overlayPlaceholder: "@lang('app.enterYear')",
        startDay: parseInt("{{ attendance_setting()?->week_start_from }}")
    };

    const daterangeConfig = {
        "@lang('app.today')": [moment(), moment()],
        "@lang('app.last30Days')": [moment().subtract(29, 'days'), moment()],
        "@lang('app.thisMonth')": [moment().startOf('month'), moment().endOf('month')],
        "@lang('app.lastMonth')": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        "@lang('app.last90Days')": [moment().subtract(89, 'days'), moment()],
        "@lang('app.last6Months')": [moment().subtract(6, 'months'), moment()],
        "@lang('app.last1Year')": [moment().subtract(1, 'years'), moment()]
    };

    const daterangeLocale = {
        "format": "{{ companyOrGlobalSetting()->moment_date_format }}",
        "customRangeLabel": "@lang('app.customRange')",
        "separator": " @lang('app.to') ",
        "applyLabel": "@lang('app.apply')",
        "cancelLabel": "@lang('app.cancel')",
        "monthNames": {!!  json_encode(\App\Models\GlobalSetting::getMonthsOfYear())!!},
        "daysOfWeek": {!!  json_encode(\App\Models\GlobalSetting::getDaysOfWeek())!!},
        "firstDay": parseInt("{{ attendance_setting()?->week_start_from }}")
    };

    const dropifyMessages = {
        default: "@lang('app.dragDrop')",
        replace: "@lang('app.dragDropReplace')",
        remove: "@lang('app.remove')",
        error: "@lang('messages.errorOccured')",
    };

    const DROPZONE_FILE_ALLOW = "{{ global_setting()->allowed_file_types }}";
    const DROPZONE_MAX_FILESIZE = "{{ global_setting()->allowed_file_size }}";
    const DROPZONE_MAX_FILES = "{{ global_setting()->allow_max_no_of_files }}";

    Dropzone.prototype.defaultOptions.dictFallbackMessage = "{{ __('modules.projectTemplate.dropFallbackMessage') }}";
    Dropzone.prototype.defaultOptions.dictFallbackText = "{{ __('modules.projectTemplate.dropFallbackText') }}";
    Dropzone.prototype.defaultOptions.dictFileTooBig = "{{ __('modules.projectTemplate.dropFileTooBig') }}";
    Dropzone.prototype.defaultOptions.dictInvalidFileType = "{{ __('modules.projectTemplate.dropInvalidFileType') }}";
    Dropzone.prototype.defaultOptions.dictResponseError = "{{ __('modules.projectTemplate.dropResponseError') }}";
    Dropzone.prototype.defaultOptions.dictCancelUpload = "{{ __('modules.projectTemplate.dropCancelUpload') }}";
    Dropzone.prototype.defaultOptions.dictCancelUploadConfirmation = "{{ __('modules.projectTemplate.dropCancelUploadConfirmation') }}";
    Dropzone.prototype.defaultOptions.dictRemoveFile = "{{ __('modules.projectTemplate.dropRemoveFile') }}";
    Dropzone.prototype.defaultOptions.dictMaxFilesExceeded = "{{ __('modules.projectTemplate.dropMaxFilesExceeded') }}";
    Dropzone.prototype.defaultOptions.dictDefaultMessage = "{{ __('modules.projectTemplate.dropFile') }}";
    Dropzone.prototype.defaultOptions.timeout = 0;

    $('#datatableRange').on('apply.daterangepicker', (event, picker) => {
        cb(picker.startDate, picker.endDate);
        const startDate = picker.startDate.format('{{ companyOrGlobalSetting()->moment_date_format }}');
        const endDate = picker.endDate.format('{{ companyOrGlobalSetting()->moment_date_format }}');
        $('#datatableRange').val(`${startDate} @lang("app.to") ${endDate}`);
    });

    $('#datatableRange2').on('apply.daterangepicker', (event, picker) => {
        cb(picker.startDate, picker.endDate);
        $('#datatableRange2').val(picker.startDate.format('{{ companyOrGlobalSetting()->moment_date_format }}') +
            ' @lang("app.to") ' + picker.endDate.format(
                '{{ companyOrGlobalSetting()->moment_date_format }}'));
    });

    function cb(start, end) {
        $('#datatableRange, #datatableRange2').val(start.format('{{ companyOrGlobalSetting()->moment_date_format }}') +
            ' @lang("app.to") ' + end.format(
                '{{ companyOrGlobalSetting()->moment_date_format }}'));
        $('#reset-filters, #reset-filters-2').removeClass('d-none');

    }

</script>

<!-- Scripts -->
<script>
    window.Laravel = {!! json_encode([
    'csrfToken' => csrf_token(),
    'user' => user(),
]) !!};
</script>

@stack('scripts')

<script>
    $(window).on('load', function () {
        // Animate loader off screen
        init();
        $(".preloader-container").fadeOut("slow", function () {
            $(this).removeClass("d-flex");
        });
    });

    $('body').on('click', '.view-notification', function (event) {
        event.preventDefault();
        const id = $(this).data('notification-id');
        const href = $(this).attr('href');

        $.easyAjax({
            url: "{{ route('mark_single_notification_read') }}",
            type: "POST",
            data: {
                '_token': "{{ csrf_token() }}",
                'id': id
            },
            success: function () {
                if (typeof href !== 'undefined') {
                    window.location = href;
                }
            }
        });
    });

    $('body').on('click', '.img-lightbox', function () {
        const imageUrl = $(this).data('image-url');
        const url = "{{ route('front.public.show_image').'?image_url=' }}" + encodeURIComponent(imageUrl);
        $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_XL, url);
    });

    $('body').on('click', '.piechart-full-screen', function () {
        const chartData = JSON.stringify($(this).data('chart-data'));
        const chartId = $(this).data('chart-id');
        const url = "{{ route('front.public.show_piechart').'?chart_data=' }}" + encodeURIComponent(chartData) + "&chart_id=" + chartId;
        $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_XL, url);
    });

    function updateOnesignalPlayerId(userId) {
        $.easyAjax({
            url: '{{ route('profile.update_onesignal_id') }}',
            type: 'POST',
            data: {
                'userId': userId,
                '_token': '{{ csrf_token() }}'
            }
        })
    }

    if (SEARCH_KEYWORD !== '' && $('#search-text-field').length > 0) {
        $('#search-text-field').val(SEARCH_KEYWORD);
        $('#reset-filters').removeClass('d-none');
    }

    $('body').on('click', '.show-hide-purchase-code', function () {
        $('> .icon', this).toggleClass('fa-eye-slash fa-eye');
        $(this).siblings('span').toggleClass('blur-code ');
    });

</script>

<script>
    let quillArray = {};

    function quillImageLoad(ID) {
        const quillContainer = document.querySelector(ID);
        quillArray[ID] = new Quill(ID, {
            modules: {
                toolbar: [
                    // [{ align: '' }, { align: 'center' }, { align: 'right' }, { align: 'justify' }],
                    [{
                        header: [1, 2, 3, 4, 5, false]
                    }],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['image', 'link', 'video'],
                    [{
                        'direction': 'rtl'
                    }],
                    ['clean']
                ],
                clipboard: {
                    matchVisual: false
                },
                "emoji-toolbar": true,
                "emoji-textarea": true,
                "emoji-shortname": true,
            },
            theme: 'snow',
            bounds: quillContainer
        });
        $.each(quillArray, function (key, quill) {
            quill.getModule('toolbar').addHandler('image', selectLocalImage);
        });


    }

    function destory_editor(selector) {
        if ($(selector)[0]) {
            var content = $(selector).find('.ql-editor').html();
            $(selector).html(content);

            $(selector).siblings('.ql-toolbar').remove();
            $(selector + " *[class*='ql-']").removeClass(function (index, class_name) {
                return (class_name.match(/(^|\s)ql-\S+/g) || []).join(' ');
            });

            $(selector + "[class*='ql-']").removeClass(function (index, class_name) {
                return (class_name.match(/(^|\s)ql-\S+/g) || []).join(' ');
            });
        } else {
            console.error('editor not exists');
        }
    }

    function quillMention(atValues, ID) {
        const mentionItemTemplate = '<div class="mention-item"> <img src="{image}" class="mr-3 rounded align-self-start taskEmployeeImg">{name}</div>';

        const customRenderItem = function (item, searchTerm) {
            const html = mentionItemTemplate.replace('{image}', item.image).replace('{name}', item.value);
            return html;
        }
        let placeholder;
        if (ID === '#submitTexts') {
            placeholder = "@lang('placeholders.message')";
        } else {
            placeholder = '';
        }

        const quillContainer = document.querySelector(ID);

        quillArray[ID] = new Quill(ID, {
            placeholder: placeholder,
            modules: {
                magicUrl: {
                    urlRegularExpression: /(https?:\/\/[\S]+)|(www.[\S]+)|(tel:[\S]+)/g,
                    globalRegularExpression: /(https?:\/\/|www\.|tel:)[\S]+/g,
                },
                toolbar: [
                    [{
                        header: [1, 2, 3, 4, 5, false]
                    }],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['image', 'link', 'video'],
                    [{
                        'direction': 'rtl'
                    }],
                    ['clean']
                ],
                mention: {
                    allowedChars: /^[A-Za-z\sÅÄÖåäö]*$/,
                    mentionDenotationChars: ["@", "#"],
                    source: function (searchTerm, renderList, mentionChar) {
                        let values;
                        if (mentionChar === "@") {
                            values = atValues;
                        } else {
                            values = hashValues;
                        }

                        if (searchTerm.length === 0) {
                            renderList(values, searchTerm);

                        } else {
                            const matches = [];
                            for (i = 0; i < values.length; i++)
                                if (
                                    ~values[i].value
                                        .toLowerCase()
                                        .indexOf(searchTerm.toLowerCase())
                                )
                                    matches.push(values[i]);
                            renderList(matches, searchTerm);
                        }
                    },
                    renderItem: customRenderItem,

                },
                clipboard: {
                    matchVisual: false
                },
                "emoji-toolbar": true,
                "emoji-textarea": true,
                "emoji-shortname": true,
            },
            theme: 'snow',
            bounds: quillContainer
        });

        quillArray[ID].getModule('toolbar').addHandler('image', selectLocalImage);
    }

    /**
     * click to open user profile
     *
     */
    window.addEventListener('mention-clicked', function ({value}) {
        if (value?.link) {
            window.open(value.link, value?.target ?? '_blank');
        }
    });

    /**
     * Step1. select local image
     *
     */
    function selectLocalImage() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.click();

        // Listen upload local image and save to server
        input.onchange = () => {
            const file = input.files[0];

            // file type is only image.
            if (/^image\//.test(file.type)) {
                saveToServer(file);
            } else {
                console.warn('You could only upload images.');
            }
        };
    }

    /**
     * Step2. save to server
     *
     * @param {File} file
     */
    function saveToServer(file) {
        const fd = new FormData();
        fd.append('image', file);
        $.ajax({
            type: 'POST',
            url: "{{ route('image.store') }}",
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: fd,
            contentType: false,
            processData: false,
            success: function (response) {
                insertToEditor(response)
            },
        });
    }

    function insertToEditor(url) {
        // push image url to rich editor.
        $.each(quillArray, function (key, quill) {
            try {
                let range = quill.getSelection();
                quill.insertEmbed(range.index, 'image', url);
            } catch (err) {
            }
        });
    }

    function checkboxChange(parentClass, id) {
        var checkedData = '';
        $('.' + parentClass).find("input[type='checkbox']:checked").each(function() {
            checkedData = (checkedData !== '') ? checkedData + ', ' + $(this).val() : $(this).val();
        });
        $('#' + id).val(checkedData);
    }
</script>

<script>
    $('body').on('click', '#pause-timer-btn, .pause-active-timer', function () {
        const id = $(this).data('time-id');
        let url = "{{ route('timelogs.pause_timer', ':id') }}";
        url = url.replace(':id', id);
        const token = '{{ csrf_token() }}';

        let currentUrl = $(this).data('url');

        $.easyAjax({
            url: url,
            blockUI: true,
            type: "POST",
            disableButton: true,
            buttonSelector: "#pause-timer-btn",
            data: {
                timeId: id,
                currentUrl: currentUrl,
                _token: token
            },
            success: function (response) {
                if (response.status === 'success') {
                    if ($('#myActiveTimer').length > 0) {
                        $(MODAL_XL + ' .modal-content').html(response.html);

                        if ($('#allTasks-table').length) {
                            window.LaravelDataTables["allTasks-table"].draw(true);
                        }
                    }

                    if ($('#allTasks-table').length) {
                        window.LaravelDataTables["allTasks-table"].draw(true);
                    }

                    if (response.reload === 'yes') {
                        window.location.reload();
                    } else {
                        $('#timer-clock').html(response.clockHtml);
                    }
                }
            }
        })
    });

    $('body').on('click', '#resume-timer-btn, .resume-active-timer', function () {
        const id = $(this).data('time-id');
        let url = "{{ route('timelogs.resume_timer', ':id') }}";
        url = url.replace(':id', id);
        const token = '{{ csrf_token() }}';

        let currentUrl = $(this).data('url');

        $.easyAjax({
            url: url,
            blockUI: true,
            type: "POST",
            disableButton: true,
            buttonSelector: "#resume-timer-btn",
            data: {
                timeId: id,
                currentUrl: currentUrl,
                _token: token
            },
            success: function (response) {

                if (response.status === 'success') {
                    if ($('#myActiveTimer').length > 0) {
                        $(MODAL_XL + ' .modal-content').html(response.html);
                    }

                    $('#timer-clock').html(response.clockHtml);
                    if ($('#allTasks-table').length) {
                        window.LaravelDataTables["allTasks-table"].draw(true);
                    }

                    if (response.reload === 'yes') {
                        window.location.reload();
                    }
                }
            }
        })
    });

    $('body').on('click', '.stop-active-timer', function() {
            var url = "{{ route('timelogs.stopper_alert', ':id') }}?via=timelog";
            var id = $(this).data('time-id');
            url = url.replace(':id', id);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
    });
</script>

@if (in_array('messages', user_modules()))
    <script>
        function newMessageNotificationPlay() {
            var audio = new Audio("{{ asset('message-notification.mp3') }}");
            audio.play();
        }

        function checkNewMessage() {
            var url = "{{ route('messages.check_new_message') }}";
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                type: "POST",
                data: {
                    '_token': token,
                },
                success: function (response) {
                    if (response.new_message_count > 0) {
                        newMessageNotificationPlay();
                        Swal.fire({
                            icon: 'info',
                            text: 'New message received.',

                            toast: true,
                            position: "top-end",
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,

                            customClass: {
                                confirmButton: "btn btn-primary",
                            },
                            showClass: {
                                popup: "swal2-noanimation",
                                backdrop: "swal2-noanimation",
                            },
                        });
                    }
                }
            });
        }

        // Reduce server load
        // if (message_setting.send_sound_notification == 1 && !(pusher_setting.status === 1 && pusher_setting.messages === 1)) {
        //     window.setInterval(function () {
        //         checkNewMessage()
        //     }, 10000); // Check messages every 10 seconds
        // }

    </script>
@endif

</body>

</html>
