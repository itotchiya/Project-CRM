<div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-20">
    @include('sections.password-autocomplete-hide')
    <input type="hidden" name="payment_method" value="mollie">

    <div class="row">
        <div class="col-lg-12 mb-3">
            <x-forms.checkbox :fieldLabel="__('modules.payments.mollieStatus')" fieldName="mollie_status"
                fieldId="mollie_status" fieldValue="active" fieldRequired="true"
                :checked="$credentials->mollie_status == 'active'" />
        </div>
    </div>
    <div class="row @if ($credentials->mollie_status == 'deactive') d-none @endif" id="mollie_details">
        <div class="col-lg-12">
            <x-forms.label class="mt-3" fieldId="modules.payments.mollieKey" :fieldLabel="__('modules.payments.mollieKey')"
                           fieldRequired="true">
            </x-forms.label>

            <x-forms.input-group>
                <input type="password" name="mollie_api_key" id="mollie_api_key"
                       class="form-control height-35 f-14" value="{{ $credentials->mollie_api_key }}">
                <x-slot name="preappend">
                    <button type="button" data-toggle="tooltip" data-original-title="@lang('app.viewPassword')"
                            class="btn btn-outline-secondary border-grey height-35 toggle-password"><i
                            class="fa fa-eye"></i></button>
                </x-slot>
            </x-forms.input-group>
        </div>
    </div>
</div>
<!-- Buttons Start -->
<div class="w-100 border-top-grey">
    <x-setting-form-actions>
        <div class="d-flex">
            <x-forms.button-primary class="mr-3 w-100" icon="check" id="save_mollie_data">@lang('app.save')
            </x-forms.button-primary>
        </div>
    </x-setting-form-actions>
</div>
