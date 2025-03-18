<div class="col-lg-12 digitalocean-form">
    <div class="row">
        <div class="col-lg-6">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2 field"
                          :fieldLabel="__('app.storageSetting.digitaloceanKey')"
                          fieldName="digitalocean_key"
                          fieldId="digitalocean_key" :fieldValue="$digitaloceanKeys->key??''"
                          :fieldPlaceholder="__('placeholders.storageSetting.awsKey')"
                          :fieldRequired="true">
            </x-forms.text>
        </div>

        <div class="col-lg-6">
            <x-forms.label class="mt-3 field" fieldId="password"
                           :fieldLabel="__('app.storageSetting.digitaloceanSecret')"
                           :fieldRequired="true">
            </x-forms.label>

            <x-forms.input-group>

                <input type="password"
                       name="digitalocean_secret"
                       id="digitalocean_secret"
                       class="form-control height-35 f-14 field"
                       value="{{ $digitaloceanKeys->secret ?? '' }}">
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
            <x-forms.select fieldId="digitalocean_region"
                            :fieldLabel="__('app.storageSetting.digitaloceanRegion')"
                            class="field"
                            fieldName="digitalocean_region" search="true">
                @foreach (\App\Models\StorageSetting::DIGITALOCEAN_REGIONS as $key => $data)
                    <option @if(isset($digitaloceanKeys->region) && $digitaloceanKeys->region == $key) selected @endif
                    value="{{$key}}">{{ $data }} - {{$key}}</option>
                @endforeach
            </x-forms.select>
        </div>



        <div class="col-lg-6">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2 field"
                          :fieldLabel="__('app.storageSetting.digitaloceanBucket')"
                          fieldName="digitalocean_bucket" fieldId="digitalocean_bucket" :fieldValue="$digitaloceanKeys->bucket??''"
                          :fieldPlaceholder="__('placeholders.storageSetting.awsBucket')"
                          :fieldRequired="true">
            </x-forms.text>
        </div>
    </div>
</div>
