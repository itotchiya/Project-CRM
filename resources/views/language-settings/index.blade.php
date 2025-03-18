@extends('layouts.app')

@push('styles')

@endpush

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        @include('sections.setting-sidebar')

        <x-setting-card>

            <x-slot name="buttons">
                <div class="row">
                    <div class="mb-2 col-md-12">
                        <x-forms.button-primary icon="plus" id="add-language"
                                                class="mb-2 mr-2"> @lang('app.addNewLanguage')
                        </x-forms.button-primary>
                        <x-forms.button-secondary icon="cog" id="translations"
                                                  class="mb-2 mr-2"> @lang('modules.languageSettings.translate')
                        </x-forms.button-secondary>
                        <x-forms.button-secondary icon="cog" id="autoTranslate"
                                                  class="mb-2"> @lang('modules.languageSettings.autoTranslate')
                        </x-forms.button-secondary>
                        @includeIf('languagepack::publish-all-button')
                    </div>
                </div>
            </x-slot>

            <x-slot name="header">

                <div class="s-b-n-header" id="tabs">

                    <h2 class="p-20 mb-0 f-21 font-weight-normal  border-bottom-grey">
                        @lang($pageTitle)
                    </h2>
                </div>
            </x-slot>


            <!-- LEAVE SETTING START -->
            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100">

                <div class="mt-2 mb-2 alert alert-primary">

                    <div><strong>Note:</strong>
                        {{__('messages.languageEnabledAlertMessage')}}
                    </div>
                </div>

                <x-table class="table table-sm-responsive">
                    <x-slot name="thead">
                        <th>@lang('app.languageName')</th>
                        <th>@lang('app.languageCode')</th>
                        <th width="10%">@lang('app.rtlStatus')</th>
                        <th>@lang('app.status')</th>
                        <th width="50%" class="text-right">@lang('app.action')</th>
                    </x-slot>

                    @forelse($languages as $language)
                        <tr id="languageRow{{ $language->id }}" @if (!user()->dark_theme)
                                @class(['bg-additional-grey' => companyOrGlobalSetting()->locale === $language->language_code])
                            @endif >
                            <td><span class='flag-icon flag-icon-{{ $language->language_code=='en'?'gb':$language->flag_code }} flag-icon-squared'></span> {{ $language->language_name }}</td>
                            <td>{{ $language->language_code }}</td>
                            <td>
                                <span class="badge badge-{{ $language->is_rtl == 1 ? 'success' : 'secondary' }}">
                                    @lang('app.' . ($language->is_rtl == 1 ? 'yes' : 'no'))
                                </span>
                            </td>
                            <td>
                                @if(companyOrGlobalSetting()->locale !== $language->language_code)
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" @if($language->status == 'enabled') checked
                                               @endif class="custom-control-input change-language-setting"
                                               id="{{ $language->id }}">
                                        <label class="cursor-pointer custom-control-label f-14"
                                               for="{{ $language->id }}"></label>
                                    </div>
                                @else

                                    --
                                @endif

                            </td>

                            @php $appSettingLink = "<a href='".route('app-settings.index')."'>".__('app.menu.appSettings')."</a>" @endphp
                            <td class='text-right'>
                                @if($language->language_code !=='en' && companyOrGlobalSetting()->locale != $language->language_code)
                                    @if (companyOrGlobalSetting()->locale != $language->language_code)
                                        @includeIf('languagepack::publish', ['language' => $language])
                                        <button type="button"
                                            class="p-2 rounded edit-language btn btn-outline-secondary f-14"
                                            data-language-id="{{ $language->id }}">
                                            <i class="mr-2 fa fa-edit icons"></i> @lang('app.edit')
                                        </button>
                                        <button type="button"
                                            class="p-2 rounded delete-language btn btn-outline-secondary f-14"
                                            data-language-id="{{ $language->id }}">
                                            <i class="mr-2 fa fa-trash icons"></i> @lang('app.delete')
                                        </button>
                                    @else
                                        @includeIf('languagepack::publish', ['language' => $language])

                                        <button type="button" onclick="window.location.href='{{ route('app-settings.index') }}'"
                                            class="p-2 rounded btn btn-outline-secondary f-14"
                                            data-toggle="popover" data-placement="top"
                                            data-content="@lang('messages.defaultLanguageCantChange',['appsettings'=> $appSettingLink])"
                                            data-html="true" data-trigger="hover">
                                            &nbsp;<i class="fas fa-question-circle"></i>&nbsp;
                                        </button>
                                    @endif
                                @else
                                    @includeIf('languagepack::publish', ['language' => $language])
                                    <button type="button" onclick="window.location.href='{{ route('app-settings.index') }}'"
                                        class="p-2 rounded btn btn-outline-secondary f-14"
                                        data-toggle="popover" data-placement="top"
                                        data-content="@if (companyOrGlobalSetting()->locale == $language->language_code)@lang('messages.defaultLanguageCantChange',['appsettings'=> $appSettingLink])@else @lang('messages.defaultEnLanguageCantChange')@endif"
                                        data-html="true" data-trigger="hover">
                                        &nbsp;<i class="fas fa-question-circle"></i>&nbsp;
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-cards.no-record-found-list colspan="4"/>
                    @endforelse

                </x-table>

            </div>
            <!-- LEAVE SETTING END -->

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')

    <script>

        $('body').on('click', '#translations', function () {
            const url = "{{ url('/translations') }}";

            window.open(url, '_blank');
        });


        $('body').on('click', '#add-language', function () {
            var url = "{{ route('language-settings.create')}}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '#autoTranslate', function () {
            var url = "{{ route('language_settings.auto_translate')}}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '.edit-language', function () {
            var id = $(this).data('language-id');
            var url = "{{ route('language-settings.edit',':id') }}";
            url = url.replace(':id', id);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('.change-language-setting').change(function () {
            var id = this.id;

            if ($(this).is(':checked'))
                var status = 'enabled';
            else
                var status = 'disabled';

            var url = "{{route('language-settings.update', ':id')}}";
            url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                type: "POST",
                blockUI: true,
                data: {'id': id, 'status': status, '_method': 'PUT', '_token': '{{ csrf_token() }}'}
            })
        });

        $('body').on('click', '.delete-language', function () {
            var id = $(this).data('language-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.deleteField')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {

                    var url = "{{ route('language-settings.destroy',':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {'_token': token, '_method': 'DELETE'},
                        blockUI: true,
                        success: function (response) {
                            if (response.status == "success") {
                                $.unblockUI();
                                $('#languageRow' + id).fadeOut();
                            }
                        }
                    });
                }
            });
        });

    </script>
    @includeIf('languagepack::script')
@endpush
