@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        @include('sections.setting-sidebar')

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs border-bottom-grey">
                        <ul class="nav -primary" id="nav-tab" role="tablist">
                            <li>

                                <a class="nav-item nav-link f-15 active google" data-toggle="tab"
                                   href="{{ route('social-auth-settings.index') }}" role="tab"
                                   aria-controls="nav-google"
                                   aria-selected="true"><img style="height: 15px;"
                                                             src="{{ asset('img/google-icon.svg') }}"> @lang('app.socialAuthSettings.google')
                                   <i class="fa fa-circle f-12 ml-1 {{ $credentials->google_status == 'enable' ? 'text-light-green' : 'text-red' }}"></i>
                                </a>
                            </li>
                            <li>
                                <a class="nav-item nav-link f-15 facebook" data-toggle="tab"
                                   href="{{ route('social-auth-settings.index') }}?tab=facebook" role="tab"
                                   aria-controls="nav-facebook" aria-selected="false"><img style="height: 15px;"
                                                                                           src="{{ asset('img/facebook-icon.svg') }}"> @lang('app.socialAuthSettings.facebook')
                                   <i class="fa fa-circle f-12 ml-1 {{ $credentials->facebook_status == 'enable' ? 'text-light-green' : 'text-red' }}"></i>
                                </a>
                            </li>
                            <li>
                                <a class="nav-item nav-link f-15 linkedin" data-toggle="tab"
                                   href="{{ route('social-auth-settings.index') }}?tab=linkedin" role="tab"
                                   aria-controls="nav-linkedin" aria-selected="false"><img style="height: 15px;"
                                                                                           src="{{ asset('img/linkedin-icon.svg') }}"> @lang('app.socialAuthSettings.linkedin')
                                   <i class="fa fa-circle f-12 ml-1 {{ $credentials->linkedin_status == 'enable' ? 'text-light-green' : 'text-red' }}"></i>
                                </a>
                            </li>
                            <li>
                                <a class="nav-item nav-link f-15 twitter" data-toggle="tab"
                                   href="{{ route('social-auth-settings.index') }}?tab=twitter" role="tab"
                                   aria-controls="nav-twitter" aria-selected="false"><img style="height: 15px;"
                                                                                          src="{{ asset('img/twitter-icon.svg') }}"> @lang('app.socialAuthSettings.twitter')
                                   <i class="fa fa-circle f-12 ml-1 {{ $credentials->twitter_status == 'enable' ? 'text-light-green' : 'text-red' }}"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </x-slot>

            {{-- include tabs here --}}
            @include($view)

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')
<script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>

    <script>
        /* manage menu active class */
        $('.nav-item').removeClass('active');
        const activeTab = "{{ $activeTab }}";
        $('.' + activeTab).addClass('active');

        $("body").on("click", "#editSettings .nav a", function (event) {
            event.preventDefault();

            $('.nav-item').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: "#nav-tabContent",
                historyPush: true,
                success: function (response) {
                    if (response.status === "success") {
                        $('#nav-tabContent').html(response.html);
                        init('.settings-box');
                        init('#F');
                    }
                }
            });
        });

        $('body').on('click', '#save_google_data, #save_facebook_data, #save_linkedin_data, #save_twitter_data', function(event) {
            var url = "{{ route('social-auth-settings.update', $credentials->id) }}";
            $.easyAjax({
                url: url,
                type: "POST",
                redirect: true,
                disableButton: true,
                blockUI: true,
                container: '#editSettings',
                data: $('#editSettings').serialize(),
                success: function () {
                    window.location.reload();
                }
            })
        });

        var clipboard = new ClipboardJS('.btn-copy');
        clipboard.on('success', function () {
            Swal.fire({
                icon: 'success',
                text: '@lang("app.webhookUrlCopied")',
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    confirmButton: 'btn btn-primary',
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
            })
        });

    </script>

@endpush
