<div class="col-lg-12 wasabi-form">
    <div class="row">
        <div class="col-lg-6">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2 field"
                          :fieldLabel="__('app.storageSetting.wasabiKey')"
                          fieldName="wasabi_key"
                          fieldId="wasabi_key" :fieldValue="$wasabiKeys->key??''"
                          :fieldPlaceholder="__('placeholders.storageSetting.awsKey')"
                          :fieldRequired="true">
            </x-forms.text>
        </div>

        <div class="col-lg-6">
            <x-forms.label class="mt-3 field" fieldId="password"
                           :fieldLabel="__('app.storageSetting.wasabiSecret')"
                           :fieldRequired="true">
            </x-forms.label>

            <x-forms.input-group>

                <input type="password"
                       name="wasabi_secret"
                       id="wasabi_secret"
                       class="form-control height-35 f-14 field"
                       value="{{ $wasabiKeys->secret ?? '' }}">
                <x-slot name="preappend">
                    <button type="button" data-toggle="tooltip"
                            data-original-title="{{ __('messages.viewKey') }}"
                            class="btn btn-outline-secondary border-grey height-35 toggle-password">
                        <i
                            class="fa fa-eye"></i></button>
                </x-slot>
            </x-forms.input-group>
        </div>

        <div class="col-lg-6">
            <x-forms.select fieldId="wasabi_region"
                            :fieldLabel="__('app.storageSetting.wasabiRegion')"
                            class="field"
                            fieldName="wasabi_region" search="true">
                @foreach (\App\Models\StorageSetting::WASABI_REGIONS as $key => $data)
                    <option @if(isset($wasabiKeys->region) && $wasabiKeys->region == $key) selected @endif
                    value="{{$key}}">{{ $data }} - {{$key}}</option>
                @endforeach
            </x-forms.select>
        </div>



        <div class="col-lg-6">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2 field"
                          :fieldLabel="__('app.storageSetting.wasabiBucket')"
                          fieldName="wasabi_bucket" fieldId="wasabi_bucket" :fieldValue="$wasabiKeys->bucket??''"
                          :fieldPlaceholder="__('placeholders.storageSetting.awsBucket')"
                          :fieldRequired="true">
            </x-forms.text>
        </div>
    </div>
</div>
