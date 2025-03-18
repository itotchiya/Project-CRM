<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/all.min.css') }}">

    <link rel="stylesheet" href="{{ asset('vendor/css/select2.min.css') }}">

    <!-- Simple Line Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/simple-line-icons.css') }}">

    <!-- Template CSS -->
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('css/main.css') }}">
    <link rel='stylesheet' href="{{ asset('vendor/css/dragula.css') }}" type='text/css' />
    <link rel='stylesheet' href="{{ asset('vendor/css/drag.css') }}" type='text/css' />

    <title>@lang($pageTitle)</title>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ isset($company)?$company->favicon_url:global_setting()->favicon_url }}">
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" type="image/png" sizes="16x16"
          href="{{ isset($company)?$company->favicon_url:global_setting()->favicon_url }}">

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

        .preloader-container {
            height: 100vh;
            width: 100%;
            margin-left: 0;
            margin-top: 0;
        }

        .rtl .preloader-container {
            margin-right: 0;
        }

        .fc a[data-navlink] {
            color: #99a5b5;
        }

        .b-p-tasks {
            min-height: 90%;
        }

    </style>
    <style>
        #logo {
            height: 50px;
        }

    </style>


    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/modernizr.min.js') }}"></script>

    <script>
        var checkMiniSidebar = localStorage.getItem("mini-sidebar");
    </script>

</head>


<body id="body" class="{{ isRtl('rtl') }}">


<!-- BODY WRAPPER START -->
<div class="clearfix body-wrapper">

    <!-- MAIN CONTAINER START -->
    <section class="bg-additional-grey" id="fullscreen">

        <div class="preloader-container d-flex justify-content-center align-items-center">
            <div class="spinner-border" role="status" aria-hidden="true"></div>
        </div>


        <x-app-title class="d-block d-lg-none" :pageTitle="$pageTitle"></x-app-title>

        <!-- CONTENT WRAPPER START -->
        <div class="content-wrapper">

            <div class="row">
                <div class="mb-4 col-12">
                    <img src="{{ isset($company)?$company->light_logo_url:global_setting()->light_logo_url }}" class="rounded height-35">
                    <div class="mt-2 f-12 text-dark-grey">{{  isset($company)?$company->company_name:global_setting()->global_app_name }}</div>
                </div>
            </div>


            @yield('content')

            <div class="row">
                <div class="col-12 f-11 text-dark-grey">
                    &copy; {{ now()->year }} | {{  isset($company)?$company->company_name:global_setting()->global_app_name }}
                </div>
            </div>
        </div>

    </section>
    <!-- MAIN CONTAINER END -->
</div>
<!-- BODY WRAPPER END -->

<x-right-modal />


<!-- also the modal itself -->
<div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog d-flex justify-content-center align-items-center modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modelHeading">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                {{__('app.loading')}}
            </div>
            <div class="modal-footer">
                <button type="button" class="mr-3 rounded btn-cancel" data-dismiss="modal">Close</button>
                <button type="button" class="rounded btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Global Required Javascript -->
<script src="{{ asset('js/main.js') }}"></script>

<script>
    const MODAL_DEFAULT = '#myModalDefault';
    const MODAL_LG = '#myModal';
    const MODAL_XL = '#myModalXl';
    const MODAL_HEADING = '#modelHeading';
    const RIGHT_MODAL = '#task-detail-1';
    const RIGHT_MODAL_CONTENT = '#right-modal-content';
    const RIGHT_MODAL_TITLE = '#right-modal-title';
    const company = @json($company??global_setting());
    document.loading = '@lang('app.loading')';

    const datepickerConfig = {
        formatter: (input, date, instance) => {
            const value = moment(date).format('{{ global_setting()->moment_date_format }}')
            input.value = value
        },
        showAllDates: true,
        customDays: {!!  json_encode(\App\Models\GlobalSetting::getDaysOfWeek())!!},
        customMonths: {!!  json_encode(\App\Models\GlobalSetting::getMonthsOfYear())!!},
        customOverlayMonths: {!!  json_encode(\App\Models\GlobalSetting::getMonthsOfYear())!!},
        overlayButton: "@lang('app.submit')",
        overlayPlaceholder: "@lang('app.enterYear')"
    };

    const dropifyMessages = {
        default: '@lang("app.dragDrop")',
        replace: '@lang("app.dragDropReplace")',
        remove: '@lang("app.remove")',
        error: '@lang("app.largeFile")'
    };
</script>
<script>
    var allowDrag = 'false';
</script>

@stack('scripts')

<script>
    $(window).on('load', function() {
        // Animate loader off screen
        init();
        $(".preloader-container").fadeOut("slow", function() {
            $(this).removeClass("d-flex");
        });
    });
</script>

</body>

</html>
