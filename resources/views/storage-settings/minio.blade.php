<div class="col-lg-12 minio-form">
    <div class="row">
        <div class="col-lg-12">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2 field"
                          :fieldLabel="__('app.storageSetting.minioEndpoint')"
                          fieldName="minio_endpoint"
                          fieldId="minio_endpoint"
                          :fieldValue="$minioKeys->endpoint ?? '' "
                          fieldPlaceholder="https://minio:9000"
                          :fieldRequired="true">
            </x-forms.text>
        </div>
        <div class="col-lg-6">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2 field"
                          :fieldLabel="__('app.storageSetting.minioKey')"
                          fieldName="minio_key"
                          fieldId="minio_key" :fieldValue="$minioKeys->key ?? '' "
                          :fieldPlaceholder="__('placeholders.storageSetting.awsKey')"
                          :fieldRequired="true">
            </x-forms.text>
        </div>

        <div class="col-lg-6">
            <x-forms.label class="mt-3 field" fieldId="password"
                           :fieldLabel="__('app.storageSetting.minioSecret')"
                           :fieldRequired="true">
            </x-forms.label>

            <x-forms.input-group>

                <input type="password"
                       name="minio_secret"
                       id="minio_secret"
                       class="form-control height-35 f-14 field"
                       value="{{ $minioKeys->secret ?? '' }}">
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

                <x-forms.text class="mr-0 mr-lg-2 mr-md-2 field"
                              :fieldLabel="__('app.storageSetting.minioRegion')"
                              fieldName="minio_region" fieldId="minio_region" :fieldValue="$minioKeys->region??''"
                              :fieldPlaceholder="__('app.storageSetting.minioRegion')"
                              :fieldRequired="true">
                </x-forms.text>

        </div>



        <div class="col-lg-6">
            <x-forms.text class="mr-0 mr-lg-2 mr-md-2 field"
                          :fieldLabel="__('app.storageSetting.minioBucket')"
                          fieldName="minio_bucket" fieldId="minio_bucket" :fieldValue="$minioKeys->bucket??''"
                          :fieldPlaceholder="__('placeholders.storageSetting.awsBucket')"
                          :fieldRequired="true">
            </x-forms.text>
        </div>
    </div>
</div>
