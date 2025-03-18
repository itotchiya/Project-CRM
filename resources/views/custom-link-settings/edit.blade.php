<x-form id="editCustomLink">
    <div class="modal-header">
        <h5 class="modal-title" id="modelHeading">@lang('modules.customLinkSettings.editCustomLink')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">×</span></button>
    </div>
    <div class="modal-body">

        @method('PUT')

        <div class="row">
            
            <div class=" col-md-6">
                <x-forms.text class="mr-0 mr-lg-2 mr-md-2"
                            :fieldLabel="__('modules.customLinkSettings.linkTitle')"
                            :fieldPlaceholder="__('placeholders.linkTitle')"
                            fieldName="link_title" :fieldValue="$custom_link->link_title"
                            fieldId="link_title" fieldRequired="true"></x-forms.text>
            </div>

            <div class=" col-md-6">
                <x-forms.text class="mr-0 mr-lg-2 mr-md-2"
                            :fieldLabel="__('modules.customLinkSettings.url')"
                            :fieldPlaceholder="__('placeholders.url')"
                            fieldName="url" :fieldValue="$custom_link->url"
                            fieldId="url" fieldRequired="true"></x-forms.text>
            </div>
            <div class="col-md-6">
            @php
                $viewed = json_decode($custom_link->can_be_viewed_by);
            @endphp
                <x-forms.select fieldId="can_be_viewed_by" :fieldLabel="__('modules.customLinkSettings.canBeViewedBy')"
                    fieldName="can_be_viewed_by[]" fieldRequired="true" multiple="true">
                    @foreach ($roles as $item)
                        <option value="{{ $item->id }}" @if (in_array($item->id, $viewed)) selected @endif>
                            {{ $item->display_name }}</option>
                    @endforeach
                </x-forms.select>
            </div>

            <div class=" col-md-6">
                <x-forms.select class="mr-0 mr-lg-2 mr-md-2" fieldId="status" fieldLabel="Status"
                 fieldName="status" search="true">
                    <option value="active" @if($custom_link->status == 'active') selected @endif>@lang('app.active')</option>
                    <option value="inactive" @if($custom_link->status == 'inactive') selected @endif>@lang('app.inactive')</option>
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
            const url = "{{ route('custom-link-settings.update', [$custom_link->id]) }}";
            $.easyAjax({
                url: url,
                container: '#editCustomLink',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-form",
                data: $('#editCustomLink').serialize(),
                success: function (response) {
                    if (response.status == 'success') {
                        window.location.reload();
                    }
                }

            })
        });

</script>

