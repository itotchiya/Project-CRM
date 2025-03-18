<div class="col-lg-12 aws-form">
    <div class="row">
        <div class="col-lg-6">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2 field"
                          :fieldLabel="__('app.storageSetting.awsKey')"
                          fieldName="aws_key"
                          fieldId="aws_key" :fieldValue="$awsKeys->key??''"
                          :fieldPlaceholder="__('placeholders.storageSetting.awsKey')"
                          :fieldRequired="true">
            </x-forms.text>
        </div>

        <div class="col-lg-6">
            <x-forms.label class="mt-3" fieldId="password"
                           :fieldLabel="__('app.storageSetting.awsSecret')"
                           :fieldRequired="true">
            </x-forms.label>

            <x-forms.input-group>

                <input type="password" name="aws_secret" id="aws_secret"
                       class="form-control height-35 f-14 field" value="{{ $awsKeys->secret??'' }}">
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
            <x-forms.select fieldId="aws_region"
                            :fieldLabel="__('app.storageSetting.awsRegion')"
                            class="field"
                            fieldName="aws_region" search="true">
                @foreach (\App\Models\StorageSetting::AWS_REGIONS as $key => $data)
                    <option @if(isset($awsKeys) && $awsKeys->region == $key) selected @endif
                    value="{{$key}}">{{ $data }}</option>
                @endforeach
            </x-forms.select>
        </div>

        <div class="col-lg-6">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2 field"
                          :fieldLabel="__('app.storageSetting.awsBucket')"
                          fieldName="aws_bucket" fieldId="aws_bucket" :fieldValue="$awsKeys->bucket??''"
                          :fieldPlaceholder="__('placeholders.storageSetting.awsBucket')"
                          :fieldRequired="true">
            </x-forms.text>
        </div>
    </div>
</div>
