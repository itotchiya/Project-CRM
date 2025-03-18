@push('datatable-styles')
    <link rel="stylesheet" href="{{ asset('vendor/css/daterangepicker.css') }}">
@endpush

<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.bankaccount.generateStatement')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="generateStatement">
        <input type="hidden" name="account_id" id="account_id" value="{{$accountId}}">
        <div class="select-box pr-2">
            <p class="mb-0 pr-3 f-14 text-dark-grey d-flex align-items-center">@lang('app.date')</p>
            <div class="select-status d-flex">
                <input type="text" name="statement_date" class=" form-control position-relative text-dark p-2 text-left f-14"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-date" icon="check">@lang('app.submit')</x-forms.button-primary>
</div>

<script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}"></script>

<script type="text/javascript">
    $(function() {
        var start = moment().subtract(89, 'days');
        var end = moment();

        $('#datatableRange').daterangepicker({
            autoUpdateInput: false,
            locale: daterangeLocale,
            linkedCalendars: false,
            startDate: start,
            endDate: end,
            ranges: daterangeConfig
        }, cb2);

        $('#datatableRange').on('apply.daterangepicker', (event, picker) => {
            cb2(picker.startDate, picker.endDate);
            $('#datatableRange').val(picker.startDate.format('{{ companyOrGlobalSetting()->moment_date_format }}') +
                ' @lang("app.to") ' + picker.endDate.format(
                    '{{ companyOrGlobalSetting()->moment_date_format }}'));
        });

        function cb2(start, end) {
            $('#datatableRange').val(start.format('{{ companyOrGlobalSetting()->moment_date_format }}') +
                ' @lang("app.to") ' + end.format(
                    '{{ companyOrGlobalSetting()->moment_date_format }}'));
        }


    });

    $('#save-date').click(function() {

        var dateRange = $('#datatableRange').data('daterangepicker');
        var startDate = $('#datatableRange').val();

        if (startDate == '') {
            Swal.fire({
                icon: 'error',
                text: '@lang("messages.selectDate")',
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
            return false;
        } else {
            startDate = dateRange.startDate.format('{{ company()->moment_date_format }}');
            endDate = dateRange.endDate.format('{{ company()->moment_date_format }}');
            var accountId = $('#account_id').val();

            startDate = encodeURIComponent(startDate);
            endDate = encodeURIComponent(endDate);

            var data = [];
            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['accountId'] = accountId;

        }

        var url = `{{ route('bankaccounts.get_bank_statement') }}`;

        string = `?startDate=${startDate}&endDate=${endDate}&accountId=${accountId}`;
        url += string;
        $(MODAL_LG).modal('hide');
        window.location.href = url;
    });

</script>
