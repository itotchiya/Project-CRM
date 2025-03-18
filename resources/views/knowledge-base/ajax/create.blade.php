<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-knowledgebase-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.knowledgeBase.knowledgeDetails')</h4>
                <div class="row p-20">
                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <div class="d-flex">
                                <x-forms.radio fieldId="toEmployee"
                                    :fieldLabel="__('modules.knowledgeBase.toEmployee')" fieldName="to"
                                    fieldValue="employee" checked="true">
                                </x-forms.radio>
                                @if (in_array('clients', user_modules()))
                                    <x-forms.radio fieldId="toClient" :fieldLabel="__('modules.knowledgeBase.toClients')"
                                        fieldValue="client" fieldName="to"></x-forms.radio>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <x-forms.text fieldId="heading" :fieldLabel="__('modules.knowledgeBase.knowledgeHeading')"
                                fieldName="heading" fieldRequired="true"
                                :fieldPlaceholder="__('modules.knowledgeBase.knowledgeHeading')">
                            </x-forms.text>
                        </div>
                    </div>

                    <div class="col-md-6 knowledgecategory">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="knowledgebasecategory" fieldRequired="true" :fieldLabel="__('modules.knowledgeBase.knowledgeCategory')">
                            </x-forms.label>

                            <x-forms.input-group >
                                <select class="form-control select-picker" name="category" id="category"
                                    data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($categories as $category)
                                        <option
                                        {{ isset($selected_category_id) && $selected_category_id == $category->id ? 'selected' : '' }}
                                         value="{{ $category->id }}">
                                            {{ $category->name }}</option>
                                    @endforeach
                                </select>

                                <x-slot name="append">
                                    <button id="addKnowledgeCategory" type="button"
                                        class="btn btn-outline-secondary border-grey"
                                        data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('modules.knowledgeBase.knowledgeCategory') }}">@lang('app.add')</button>
                                </x-slot>

                            </x-forms.input-group>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <x-forms.label class="my-3"  fieldId="description-textt"
                                :fieldLabel="__('modules.knowledgeBase.knowledgeDesc')">
                            </x-forms.label>
                            <div id="description"></div>
                            <textarea name="description" id="description-text" class="d-none"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">

                        <x-forms.file-multiple :fieldLabel="__('modules.knowledgeBase.uploadFile')"
                            fieldName="file" fieldId="file-upload-dropzone" />
                        </div>
                        <input type="hidden" name="knowledge_base_id" id="knowledge_base_id">
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-knowledgebase" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('knowledgebase.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {
        quillMention(null, '#description');

        // show/hide project detail
        $(document).on('change', 'input[type=radio][name=to]', function() {
            $('.department').toggleClass('d-none');
        });

        Dropzone.autoDiscover = false;
            //Dropzone class
            knowledgeBaseDropzone = new Dropzone("div#file-upload-dropzone", {
                dictDefaultMessage: "{{ __('app.dragDrop') }}",
                url: "{{ route('knowledgebase-files.store') }}",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                paramName: "file",
                maxFilesize: DROPZONE_MAX_FILESIZE,
                maxFiles: DROPZONE_MAX_FILES,
                autoProcessQueue: false,
                uploadMultiple: true,
                addRemoveLinks: true,
                parallelUploads: DROPZONE_MAX_FILES,
                acceptedFiles: DROPZONE_FILE_ALLOW,
                init: function () {
                    knowledgeBaseDropzone = this;
                }
            });

            knowledgeBaseDropzone.on('sending', function (file, xhr, formData) {
                var knowledgeBaseId = $('#knowledge_base_id').val();
                formData.append('knowledge_base_id', knowledgeBaseId);
                $.easyBlockUI();
            });
            knowledgeBaseDropzone.on('uploadprogress', function () {
                $.easyBlockUI();
            });
            knowledgeBaseDropzone.on('queuecomplete', function () {
                window.location.href = "{{ route('knowledgebase.index') }}"
            });
            knowledgeBaseDropzone.on('removedfile', function () {
                var grp = $('div#file-upload-dropzone').closest(".form-group");
                var label = $('div#file-upload-box').siblings("label");
                $(grp).removeClass("has-error");
                $(label).removeClass("is-invalid");
            });
            knowledgeBaseDropzone.on('error', function (file, message) {
                knowledgeBaseDropzone.removeFile(file);
                var grp = $('div#file-upload-dropzone').closest(".form-group");
                var label = $('div#file-upload-box').siblings("label");
                $(grp).find(".help-block").remove();
                var helpBlockContainer = $(grp);

                if (helpBlockContainer.length == 0) {
                    helpBlockContainer = $(grp);
                }

                helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
                $(grp).addClass("has-error");
                $(label).addClass("is-invalid");

            });


        $('#save-knowledgebase').click(function() {
            const url = "{{ route('knowledgebase.store') }}";

            var note = document.getElementById('description').children[0].innerHTML;
            document.getElementById('description-text').value = note;

            $.easyAjax({
                url: url,
                container: '#save-knowledgebase-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-knowledgebase",

                data: $('#save-knowledgebase-data-form').serialize(),
                success: function(response) {
                        if (response.status == 'success') {
                            if (knowledgeBaseDropzone.getQueuedFiles().length > 0) {
                                knowledgeBaseId = response.knowledgeBaseId;
                                $('#knowledge_base_id').val(knowledgeBaseId);
                                knowledgeBaseDropzone.processQueue();
                            }
                            else {
                                if ($(MODAL_XL).hasClass('show')) {
                                    $(MODAL_XL).modal('hide');
                                    window.location.reload();
                                } else {
                                    window.location.href = response.redirectUrl;
                                }
                            }
                        }
                }
            });
        });

        $('#addKnowledgeCategory').click(function() {
            const url = "{{ route('knowledgebasecategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        })

        init(RIGHT_MODAL);
    });
</script>
