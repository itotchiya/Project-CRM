<div class="alert alert-primary">
    <h6>Please set the following cron command on your server (Ignore if already done)</h6>
    <code>* * * * * (Every Minute)</code>
    <br>
    <br>
    @php
        try {
            $phpPath = PHP_BINDIR.'/php';
        } catch (\Throwable $th) {
            $phpPath = 'php';
        }
           echo '<code  id="cron-command" class="f-12"> ' . $phpPath . ' ' . base_path() . '/artisan schedule:run >> /dev/null 2>&1</code>';
    @endphp
    <button type="button" data-clipboard-target="#cron-command"
            data-toggle="tooltip"
            data-original-title="@lang('app.copyAboveLink')"
            class="btn-copy-cron btn btn-sm btn-secondary p-1 f-10">
        <i class="fa fa-copy "></i>
    </button>

    <div class="mt-3"><strong>Note:</strong>

        <ins>{{$phpPath}}</ins>
        in the above command is the path of PHP on your server. To ensure it works correctly, please enter the correct PHP path for your server and provide the path to your script. If you're unsure how to set up a cron job, you may want to consult with your server administrator or hosting provider.
    </div>
</div>

@push('scripts')
    <script>
        var clipboard = new ClipboardJS('.btn-copy-cron');

        clipboard.on('success', function (e) {
            Swal.fire({
                icon: 'success',
                text: "{{ __('app.copied') }}",
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
            })
        });
    </script>
@endpush
