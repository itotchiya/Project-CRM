<style>
    @media print {
        .printable-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Optional: This will make the container take the full height of the viewport */
        }
        .non-printable {
            display: none !important;
        }
        body * {
            visibility: hidden;
        }
        #qrCode, #printBtn {
            visibility: visible;
        }


    }
</style>


{{-- @endif --}}

<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
    @method('PUT')
    <div class="row">
        <div class="col-lg-12">

            <x-forms.toggle-switch class="mr-0 mr-lg-12" :checked="($attendanceSetting->qr_enable)" :fieldLabel="__('app.qrCode')"
                fieldName="qr_status" fieldId="qr_status" />

        </div>
        <x-alert id="qrStatusError" class="alert alert-danger ml-3 w-100 mr-3" type="danger" style="display: none;" icon="info-circle">
            <span class="alert-text"></span>
        </x-alert>
        <input type="hidden" id="qrStatusValue" name="qr_status_value" value="0">

        <!-- QR Code URL and Copy Button -->
<div class="w-100 qrSection @if($attendanceSetting->qr_enable == 0)d-none @endif">
    <div class="row mt-3 ">
        <div class="col-lg-8 pr-0">
            <input type="text" class="form-control p-1" id="qrCodeUrl" value="{{ route('settings.qr-login', ['hash' => company()->hash]) }}"
            readonly>
        </div>
        <div class="col-lg-4 position-relative">
            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyQRCodeUrl()">
                <i class="fas fa-copy"></i> <!-- Font Awesome copy icon -->
            </button>
            <!-- Message displayed near the button -->
            <div id="copyMessage" class="position-absolute bg-grey p-1 rounded" style="display: none; bottom: 100%; left: 0;">

            </div>
        </div>
    </div>
    <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
        <div class="row">
            <div class="col-lg-12">
                <div class="row" id="qrcode">
                    <img class="mx-auto" id="qrCodeImage" src="{{ $qr->getDataUri() }}">
                </div>
            </div>
        </div>
    </div>

    <!-- URL Display and Copy Button -->



<!-- Buttons Start -->
<div class="w-100 border-top-grey qrSection ">
    <x-setting-form-actions>
        <button id="downloadBtn" class="btn btn-primary btn-sm mr-3" onclick="downloadQRCode()">
            <i class="fas fa-download"></i> @lang('app.download')
        </button>
        <button id="printBtn" class="btn btn-secondary btn-sm mr-3" onclick="printQRCode()">
            <i class="fas fa-print"></i> @lang('app.print')
        </button>
    </x-setting-form-actions>
</div>
</div>
<!-- Buttons End -->

<script>
    // Function to handle downloading QR code
    $("body").on("click", "#downloadBtn", function(event) {
        var qrCode = document.getElementById('qrCodeImage'); // Corrected element ID
        var url = qrCode.src.replace(/^data:image\/[^;]/, 'data:application/octet-stream');
        var link = document.createElement('a');
        link.download = 'QR_Code.png';
        link.href = url;
        link.click();
    });

    // Function to print QR code
    $("body").on("click", "#printBtn", function(event) {
        let printFrame = document.createElement('iframe');
        let html = '<html><head><title>Print</title></head><body>';
        html += $('#qrcode').html();
        html += '</body></html>';
        printFrame.style.display = 'none';
        document.body.appendChild(printFrame);

        printFrame.contentDocument.open();
        printFrame.contentDocument.write(html);
        printFrame.contentDocument.close();

        printFrame.onload = function() {
            printFrame.contentWindow.print();
            printFrame.contentWindow.onafterprint = function() {
                document.body.removeChild(printFrame);
            };
        };
    });

    // Toggle QR code visibility based on toggle switch state
    function updateQRStatus(status) {
        var token = "{{ csrf_token() }}";
        $.ajax({
            type: 'POST',
            url: "{{ route('settings.change-qr-code-status') }}",
            data: { qr_status: status, '_token' : token },
            success: function(response) {
                if (response.status === 'success') {
                    if(status == 1){
                        $('.qrSection').removeClass('d-none');
                    }
                    else{
                        $('.qrSection').addClass('d-none');
                    }
                    console.log('QR status updated successfully');
                }else{
                    $('#qrStatusError .alert-text').text(response.message).show();
                    $('#qr_status').prop('checked', !status);
                    $('#qrStatusError').css('display', 'block');
                }
            },

        });
    }

    // Toggle QR code visibility based on toggle switch state
    $("body").on("click", "#qr_status", function(event) {

        var status = $('#qr_status').prop('checked') ? 1 : 0;

        updateQRStatus(status); // Update the server-side status
        // Update the disabled state of the print button
    });
    function copyQRCodeUrl() {
    var qrCodeUrlInput = document.getElementById('qrCodeUrl');
    qrCodeUrlInput.select();
    document.execCommand('copy');

    // Show the message
    Swal.fire({
        icon: 'success',
        text: 'Link Copied!',
        toast: true,
        position: 'top-end',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        customClass: {
            confirmButton: 'btn btn-primary',
        },
        showClass: {
            popup: 'swal2-noanimation',
            backdrop: 'swal2-noanimation'
        },
    });
}


</script>
