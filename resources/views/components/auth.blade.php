<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $globalSetting->favicon_url }}">
    <link rel="manifest" href="{{ $globalSetting->favicon_url }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ $globalSetting->favicon_url }}">
    <meta name="theme-color" content="#ffffff">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/all.min.css') }}" defer="defer">

    <!-- Template CSS -->
    <link href="{{ asset('vendor/froiden-helper/helper.css') }}" rel="stylesheet" defer="defer">
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('css/main.css') }}">

    <title>{{ $globalSetting->global_app_name }}</title>


    @stack('styles')
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>

    <style defer="defer">
        .login_header {
            background-color: {{ $globalSetting->logo_background_color }}         !important;
        }

    </style>
    @include('sections.theme_css')
    @if(file_exists(public_path().'/css/login-custom.css'))
        <link href="{{ asset('css/login-custom.css') }}" rel="stylesheet">
    @endif

    @if ($globalSetting->sidebar_logo_style == 'full')
        <style>
            .login_header img {
                max-width: unset;
            }
        </style>
    @endif

</head>

<body
    class="{{ $globalSetting->auth_theme == 'dark' ? 'dark-theme' : '' }} {{ isRtl() ? (session('changedRtl') === false ? '' : 'rtl') : (session('changedRtl') == true ? 'rtl' : '') }}">

<header class="px-4 bg-white sticky-top d-flex justify-content-center align-items-center login_header">
    <img class="mr-2 rounded" src="{{ $globalSetting->logo_url }}" alt="Logo"/>
    @if ($globalSetting->sidebar_logo_style != 'full')
        <h3 class="mb-0 pl-1 {{ $globalSetting->auth_theme_text == 'light' ? ($globalSetting->auth_theme == 'dark' ? 'text-dark' : 'text-white') : '' }}">{{ $globalSetting->global_app_name ?? $globalSetting->app_name }}</h3>
    @endif
</header>


<section class="py-5 bg-grey login_section"
         @if ($globalSetting->login_background_url) style="background: url('{{ $globalSetting->login_background_url }}') center center/cover no-repeat;" @endif>
    <div class="container">
        <div class="row">
            <div class="text-center col-md-12">

                <div class="mx-auto text-center bg-white rounded login_box">
                    {{ $slot }}
                </div>

                {{ $outsideLoginBox ?? '' }}
                @if($languages->count() > 1)
                    <div class="my-3 d-flex flex-column flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center justify-content-center">
                            @foreach($languages->take(4) as $index => $language)
                                <span class="mx-3 my-10 f-12">
                                    <a href="javascript:;" class="text-dark-grey change-lang d-flex align-items-center"
                                       data-lang="{{ $language->language_code }}">
                                        <span class="mr-2 flag-icon flag-icon-{{ $language->flag_code === 'en' ? 'gb' : $language->flag_code }} flag-icon-squared"></span>
                                        {{ \App\Models\LanguageSetting::LANGUAGES_TRANS[$language->language_code] ?? $language->language_name }}
                                    </a>
                                </span>
                            @endforeach

                            @if($languages->count() > 4)
                                <div class="dropdown" style="z-index:10000">
                                    <a class="btn btn-lg f-14 px-2 py-1 text-dark-grey  rounded dropdown-toggle"
                                       type="button" id="languageDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                         aria-labelledby="languageDropdown" style="max-height: 600px; overflow-y: auto;">
                                        @foreach($languages->slice(4) as $language)
                                            <a class="dropdown-item change-lang" href="javascript:;"
                                               data-lang="{{ $language->language_code }}">
                                                <span class="mr-2 flag-icon flag-icon-{{ $language->flag_code === 'en' ? 'gb' : $language->flag_code }} flag-icon-squared"></span>
                                                {{ \App\Models\LanguageSetting::LANGUAGES_TRANS[$language->language_code] ?? $language->language_name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{--                @if($languages->count() >1)--}}
                {{--                    <div class="my-3 d-flex flex-column flex-grow-1">--}}
                {{--                        <div class="flex-wrap align-items-center d-flex flex-grow-1 justify-content-center">--}}
                {{--                            @foreach($languages as $language)--}}
                {{--                                <span class="mx-1 my-10 f-12 ">--}}
                {{--                                    <a href="javascript:;" class="text-dark-grey change-lang d-flex align-items-center"--}}
                {{--                                       data-lang="{{$language->language_code}}">--}}
                {{--                                        <span--}}
                {{--                                            class='mr-2 flag-icon flag-icon-{{ ($language->flag_code == 'en') ? 'gb' : $language->flag_code }} flag-icon-squared'></span>--}}
                {{--                                        {{\App\Models\LanguageSetting::LANGUAGES_TRANS[$language->language_code] ?? $language->language_name}}--}}
                {{--                                    </a>--}}
                {{--                                </span>--}}
                {{--                            @endforeach--}}
                {{--                        </div>--}}
                {{--                    </div>--}}
                {{--                @endif--}}


            </div>
        </div>

    </div>

</section>
<!-- Global Required Javascript -->
{{--<script src="{{ asset('vendor/bootstrap/javascript/bootstrap-native.js') }}" defer="defer"></script>--}}

<!-- Font Awesome -->
<script src="{{ asset('vendor/jquery/all.min.js') }}" defer="defer"></script>

<!-- Template JS -->
<script src="{{ asset('js/main.js') }}"></script>
<script>
    document.loading = '@lang('app.loading')';
    const MODAL_DEFAULT = '#myModalDefault';
    const MODAL_LG = '#myModal';
    const MODAL_XL = '#myModalXl';
    const MODAL_HEADING = '#modelHeading';
    const RIGHT_MODAL = '#task-detail-1';
    const RIGHT_MODAL_CONTENT = '#right-modal-content';
    const RIGHT_MODAL_TITLE = '#right-modal-title';

    const dropifyMessages = {
        default: "@lang('app.dragDrop')",
        replace: "@lang('app.dragDropReplace')",
        remove: "@lang('app.remove')",
        error: "@lang('messages.errorOccured')",
    };
    $('.change-lang').click(function (event) {
        const locale = $(this).data("lang");
        event.preventDefault();
        let url = "{{ route('front.changeLang', ':locale') }}";
        url = url.replace(':locale', locale);
        $.easyAjax({
            url: url,
            container: '#login-form',
            blockUI: true,
            type: "GET",
            success: function (response) {
                if (response.status === 'success') {
                    window.location.reload();
                }
            }
        })
    });
</script>

{{ $scripts }}

</body>

</html>
