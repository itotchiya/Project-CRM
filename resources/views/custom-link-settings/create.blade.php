<x-form id="addCustomLink">
    <div class="modal-header">
        <h5 class="modal-title" id="modelHeading">@lang('modules.customLinkSettings.addNewCustomLink')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">×</span></button>
    </div>
    <div class="modal-body">

        <div class="row">

            <div class=" col-md-6">
                <x-forms.text class="mr-0 mr-lg-2 mr-md-2"
                            :fieldLabel="__('modules.customLinkSettings.linkTitle')"
                            :fieldPlaceholder="__('placeholders.linkTitle')"
                            fieldName="link_title"
                            fieldId="link_title" fieldRequired="true"></x-forms.text>
            </div>

            <div class=" col-md-6">
                <x-forms.text class="mr-0 mr-lg-2 mr-md-2"
                            :fieldLabel="__('modules.customLinkSettings.url')"
                            :fieldPlaceholder="__('placeholders.url')"
                            fieldName="url"
                            fieldId="url" fieldRequired="true"></x-forms.text>
            </div>

            {{-- <div class="col-sm-12 col-lg-4" id="roles-viewed-by">
                <x-forms.select :fieldLabel="__('modules.customLinkSettings.canBeViewedBy')" fieldName="can_be_viewed_by[]" fieldId="roles_viewed_by"
                multiple="true">
                    @foreach ($roles as $item)
                        <option value="{{ $item->id }}">{{ $item->display_name }}</option>
                    @endforeach
                </x-forms.select>
            </div> --}}

            <div class="col-md-6">
                <x-forms.select fieldId="can_be_viewed_by" :fieldLabel="__('modules.customLinkSettings.canBeViewedBy')"
                    fieldName="can_be_viewed_by[]" fieldRequired="true" search="true" multiple="true">
                    @foreach ($roles as $item)
                            <option value="{{ $item->id }}">{{ $item->display_name }}</option>
                    @endforeach
                </x-forms.select>
            </div>

            <div class=" col-md-6">
                <x-forms.select class="mr-0 mr-lg-2 mr-md-2" fieldId="status" fieldLabel="Status" fieldName="status" search="true">
                    <option value="active">@lang('app.active')</option>
                    <option value="inactive">@lang('app.inactive')</option>
                </x-forms.select>
            </div>

        </div>

    </div>
    <!-- SETTINGS END -->

    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0">@lang('app.cancel')
        </x-forms.button-cancel>
        <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
        </x-forms.button-primary>
    </div>
</x-form>

<script>

$(".select-picker").selectpicker();

    // Save form data
        $('#save-form').click(function () {
            $.easyAjax({
                url: "{{ route('custom-link-settings.store') }}",
                container: '#addCustomLink',
                type: "POST",
                blockUI: true,
                redirect: true,
                disableButton: true,
                buttonSelector: "#save-form",
                data: $('#addCustomLink').serialize(),
                success: function (response) {
                    if (response.status == 'success') {
                        window.location.reload();
                    }
                }
            })
        });

</script>

