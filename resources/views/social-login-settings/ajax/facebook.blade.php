<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-20">
    @method('PUT')

    <input type="hidden" name="tab" value="facebook">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <x-forms.checkbox :fieldLabel="__('app.status')" fieldName="facebook_status"
                fieldId="facebookButton" fieldValue="enable" fieldRequired="true"
                :checked="$credentials->facebook_status == 'enable'" />
        </div>
        <div class="col-lg-12 facebookSection mb-3 @if ($credentials->facebook_status !== 'enable') d-none @endif">
            <div class="row">
                <div class="col-lg-6">
                    <x-forms.text :fieldLabel="__('app.socialAuthSettings.facebookClientId')"
                        fieldName="facebook_client_id" fieldId="facebook_client_id" fieldRequired="true"
                        :fieldValue="$credentials->facebook_client_id"></x-forms.text>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <x-forms.label class="mt-3" fieldId="password" fieldRequired="true"
                            :fieldLabel="__('app.socialAuthSettings.facebookSecret')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <input type="password" name="facebook_secret_id" id="facebook_secret_id"
                                class="form-control height-35 f-14"
                                value="{{ $credentials->facebook_secret_id }}">

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
                                id="facebook-webhook-link">{{ route('social_login_callback', 'facebook') }}</span>
                            <a href="javascript:;" class="btn-copy btn-secondary f-12 rounded p-1 py-2 ml-1"
                                data-clipboard-target="#facebook-webhook-link">
                                <i class="fa fa-copy mx-1"></i>@lang('app.copy')</a>
                        </p>
                        <p class="text-primary">(@lang('messages.addFacebookCallback'))</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Buttons Start -->
        <div class="w-100 border-top-grey">
            <x-setting-form-actions>
                <x-forms.button-primary id="save_facebook_data" class="mr-3" icon="check">@lang('app.save')
                </x-forms.button-primary>
            </x-setting-form-actions>
        </div>
        <!-- Buttons End -->
    </div>
</div>

<script>

    $('#facebookButton').on('change', function() {
        $('.facebookSection').toggleClass('d-none');
    });
    
</script>
