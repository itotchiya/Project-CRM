    <x-form id="acceptEstimate">
        <div class="row">
            <div class="col-sm-12 bg-grey p-4 signature">
                <x-forms.label fieldId="sign-pad" fieldRequired="true" :fieldLabel="__('modules.estimates.signature')" />
                <div class="signature_wrap wrapper border-0 form-control">
                    <canvas id="sign-pad" class="signature-pad rounded" width=400 height=150></canvas>
                </div>
            </div>
            <div class="col-sm-12 p-4 d-none upload-image">
                <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.estimates.signature')" fieldName="image"
                    fieldId="sign_image" :popover="__('messages.fileFormat.ImageFile')" fieldRequired="true" />
            </div>
            <div class="col-sm-12 mt-3">
                <x-forms.button-secondary id="undo-sign" class="signature">@lang('modules.estimates.undo')</x-forms.button-secondary>
                <x-forms.button-secondary class="ml-2 signature" id="clear-sign">@lang('modules.estimates.clear')</x-forms.button-secondary>
                <x-forms.button-secondary class="ml-2 " id="toggle-pad-uploader">@lang('modules.estimates.uploadSignature')
                </x-forms.button-secondary>
            </div>

        </div>
    </x-form>

    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-sign" icon="check">@lang('app.sign')</x-forms.button-primary>






<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>

<script>
        var canvas = document.getElementById('sign-pad');

        var signPad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)' // necessary for saving image as JPEG; can be removed is only saving as PNG or SVG
        });

        document.getElementById('clear-sign').addEventListener('click', function(e) {
            e.preventDefault();
            signPad.clear();
        });

        document.getElementById('undo-sign').addEventListener('click', function(e) {
            e.preventDefault();
            var data = signPad.toData();
            if (data) {
                data.pop(); // remove the last dot or line
                signPad.fromData(data);
            }
        });

        $('#toggle-pad-uploader').click(function() {
            var text = $('.signature').hasClass('d-none') ? '{{ __("modules.estimates.uploadSignature") }}' : '{{ __("app.sign") }}';

            $(this).html(text);

            $('.signature').toggleClass('d-none');
            $('.upload-image').toggleClass('d-none');
        });

        $('#save-sign').click(function() {
            var signature = signPad.toDataURL('image/png');
            var image = $('#sign_image').val();

    // this parameter is used for type of signature used and will be used on validation and upload signature image
    var signature_type = !$('.signature').hasClass('d-none') ? 'signature' : 'upload';

    if (signPad.isEmpty() && !$('.signature').hasClass('d-none')) {
        Swal.fire({
            icon: 'error',
            text: '{{ __('messages.signatureRequired') }}',

            customClass: {
                confirmButton: 'btn btn-primary',
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        });
        return false;
    }

    $.easyAjax({
        url: "{{ route('company.sign', '$contract->id') }}",
        container: '#acceptEstimate',
        type: "POST",
        blockUI: true,
        file: true,
        disableButton: true,
        buttonSelector : '#save-sign',
        data: {
            signature: signature,
            image: image,
            signature_type: signature_type,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.status == 'success') {
                window.location.reload();
            }
        }
    })
    });
</script>
