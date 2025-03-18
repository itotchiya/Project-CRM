<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<style>
    .tagify {
        width: 100%;
    }

    .tags-look .tagify__dropdown__item {
        display: inline-block;
        border-radius: 3px;
        padding: .3em .5em;
        border: 1px solid #CCC;
        background: #F3F3F3;
        margin: .2em;
        font-size: .85em;
        color: black;
        transition: 0s;
    }

    .tags-look .tagify__dropdown__item--active {
        color: white;
    }

    .tags-look .tagify__dropdown__item:hover {
        background: var(--header_color);
    }

    #datatable {
        margin-bottom: -20px;
    }

</style>

<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
    <div class="row">
        <div class="col-md-12">
            @php
                $uploadMaxFilesize = \App\Helper\Files::getUploadMaxFilesize();
                $postMaxSize = \App\Helper\Files::getPostMaxSize();
            @endphp

            <span class="text-info">
                Server upload_max_filesize = <strong>{{\App\Helper\Files::getUploadMaxFilesize()['size']}}</strong>
                &nbsp; &nbsp; Server post_max_size = <strong>{{\App\Helper\Files::getUploadMaxFilesize()['size']}}</strong>
            </span>

        </div>
        <div class="col-lg-3">

            <label for="allowed_file_size" class="mt-3">
                @lang('modules.accountSettings.allowedFileSize') <sup class="f-14">*</sup>
            </label>

            <x-forms.input-group>
                <input type="number" name="allowed_file_size" id="allowed_file_size"
                       value="{{ global_setting()->allowed_file_size }}"
                       placeholder="32"
                       class="form-control height-35 f-14"/>
                <x-slot name="preappend">
                    <label class="input-group-text border-grey bg-white height-35">MB</label>
                </x-slot>
            </x-forms.input-group>
            <small>{{__('messages.lowerValue')}} {{\App\Helper\Files::getUploadMaxFilesize()['size']}}</small>

        </div>
        <div class="col-lg-4">
            <label for="allow_max_no_of_files" class="mt-3">
                @lang('modules.accountSettings.maxNumberOfFiles') <sup class="f-14">*</sup>
            </label>

            <x-forms.input-group>
                <input type="number" name="allow_max_no_of_files" id="allow_max_no_of_files"
                       value="{{ global_setting()->allow_max_no_of_files }}"
                       placeholder="5"
                       class="form-control height-35 f-14"/>
            </x-forms.input-group>
        </div>

        <div class="col-lg-12 mt-4">
            <label for="allowed_file_types">
                @lang('modules.accountSettings.allowedFileType') <sup class="f-14">*</sup>
            </label>
            <textarea type="text" name="allowed_file_types" id="allowed_file_types"
                      placeholder="@lang('placeholders.fileSetting')"
                      class="form-control f-14">{{ global_setting()->allowed_file_types }}</textarea>
        </div>

    </div>
</div>

<div class="w-100 border-top-grey set-btns">
    <x-setting-form-actions>
        <x-forms.button-primary id="save-file-upload-setting-form" class="mr-3" icon="check">@lang('app.save')
        </x-forms.button-primary>

    </x-setting-form-actions>
</div>


<script src="{{ asset('vendor/jquery/tagify.min.js') }}"></script>

<script>

    $(document).ready(function () {
        var input = document.querySelector('textarea[id=allowed_file_types]');

        var whitelist = [
            'image/*', 'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/docx',
            'application/pdf', 'text/plain', 'application/msword',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip',
            'application/x-zip-compressed', 'application/x-compressed', 'multipart/x-zip', '.xlsx', 'video/x-flv',
            'video/mp4', 'application/x-mpegURL', 'video/MP2T', 'video/3gpp', 'video/quicktime', 'video/x-msvideo',
            'video/x-ms-wmv', 'application/sla', '.stl'
        ];
        // init Tagify script on the above inputs
        tagify = new Tagify(input, {
            whitelist: whitelist,
            userInput: false,
            dropdown: {
                classname: "tags-look",
                enabled: 0,
                closeOnSelect: false
            }
        });

        $('body').on('click', '#save-file-upload-setting-form', function () {
            const url = "{{ route('app-settings.update', [company()->id]) }}?page=file-upload-setting";

            $.easyAjax({
                url: url,
                container: '#editSettings',
                type: "POST",
                disableButton: true,
                buttonSelector: "#save-file-upload-setting-form",
                data: $('#editSettings').serialize(),
            })
        });
    });


</script>
