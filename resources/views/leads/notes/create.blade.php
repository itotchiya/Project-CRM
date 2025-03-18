<div class="row">
    <div class="col-sm-12">
        <x-form id="save-lead-note-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.deal.dealNoteDetails')</h4>

                <input type="hidden" name="lead_id" value="{{ $leadId }}">

                <div class="row px-4">

                    <div class="col-md-6">
                        <x-forms.text fieldId="title" :fieldLabel="__('modules.client.noteTitle')" fieldName="title"
                            :fieldPlaceholder="__('placeholders.note')">
                        </x-forms.text>
                    </div>

                </div>

                <div class="row px-4">
                    <div class="col-md-12 col-lg-12">
                        <div class="form-group">
                            <x-forms.label class="my-3" fieldId="notes" fieldRequired="true" :fieldLabel="__('modules.client.noteDetail')">
                            </x-forms.label>
                            <div id="details"></div>
                            <textarea name="details" id="details-text" class="d-none"></textarea>
                        </div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-lead-note-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('deals.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>

        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {

        quillImageLoad('#details');

        $('#save-lead-note-form').click(function() {
            var comment = document.getElementById('details').children[0].innerHTML;
            document.getElementById('details-text').value = comment;

            const url = "{{ route('deal-notes.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-lead-note-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-lead-note-form",
                data: $('#save-lead-note-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            })
        });


        init(RIGHT_MODAL);
    });
</script>
