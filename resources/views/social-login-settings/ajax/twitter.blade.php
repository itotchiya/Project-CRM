<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-20">
    @method('PUT')

    <input type="hidden" name="tab" value="twitter">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <x-forms.checkbox :fieldLabel="__('app.status')" fieldName="twitter_status" fieldId="twitterButton"
                fieldValue="enable" fieldRequired="true" :checked="$credentials->twitter_status == 'enable'" />
        </div>

        <div class="col-lg-12 twitterSection mb-3 @if ($credentials->twitter_status !== 'enable') d-none @endif">
            <div class="row">
                <div class="col-lg-6">
                    <x-forms.text :fieldLabel="__('app.socialAuthSettings.twitterClientId')"
                        fieldName="twitter_client_id" fieldRequired="true" fieldId="twitter_client_id"
                        :fieldValue="$credentials->twitter_client_id">
                    </x-forms.text>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <x-forms.label class="mt-3" fieldId="password" fieldRequired="true"
                            :fieldLabel="__('app.socialAuthSettings.twitterSecret')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <input type="password" name="twitter_secret_id" id="twitter_secret_id"
                                class="form-control height-35 f-14"
                                value="{{ $credentials->twitter_secret_id }}">
                            <x-slot name="preappend">
                                <button type="button" data-toggle="tooltip"
                                    data-original-title="{{ __('messages.viewKey') }}"
                                    class="btn btn-outline-secondary border-grey height-35 toggle-password"><i
                                        class="fa fa-eye"></i></button>
                            </x-slot>
                        </x-forms.input-group>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group my-3">
                        <label for="mail_from_name">@lang('app.callback')</label>
                        <p class="text-bold"><span
                                id="twitter_webhook_url">{{ route('social_login_callback', 'twitter') }}</span>
                            <a href="javascript:;" class="btn-copy btn-secondary f-12 rounded p-1 py-2 ml-1"
                                data-clipboard-target="#twitter_webhook_url">
                                <i class="fa fa-copy mx-1"></i>@lang('app.copy')</a>
                        </p>
                        <p class="text-primary">(@lang('messages.addTwitterCallback'))</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Buttons Start -->
        <div class="w-100 border-top-grey">
            <x-setting-form-actions>
                <x-forms.button-primary id="save_twitter_data" class="mr-3" icon="check">@lang('app.save')
                </x-forms.button-primary>
            </x-setting-form-actions>
        </div>
        <!-- Buttons End -->
    </div>
</div>

<script>

        $('#twitterButton').on('change', function() {
            $('.twitterSection').toggleClass('d-none');
        });

</script>
