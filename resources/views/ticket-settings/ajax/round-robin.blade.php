<div class="table-responsive p-20">

    <x-form id="save-lead-status" method="POST">
        <input type="hidden" name="company_id" id="company_id" value="{{$company->id}}" />
        <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}" />
        <div class="col-lg-4">
            <x-forms.toggle-switch class="mr-0 mr-lg-12" :checked="$ticketSettings != null ? $ticketSettings->ticket_round_robin_status : false"
                :fieldLabel="__('modules.deal.dealMethod')" fieldName="lead_setting_status"
                fieldId="lead_setting_status"/>
        </div>
    </x-form>
    <x-alert type="warning">
    <div><br><b>@lang('modules.deal.information')</b><br><br>@lang('modules.deal.roundrobinNote')<br>
        <br>&bull;@lang('modules.deal.equalDistribution')
        <br>&bull;@lang('modules.deal.sequentialAssignment')
        <br>&bull;@lang('modules.deal.fairRotation')
        <br>@lang('modules.deal.roundrobinEx')
    </div>
</x-alert>
</div>

<script>
$(document).ready(function () {
    $('#lead_setting_status').click(function () {
        var status = document.getElementById('lead_setting_status').checked ? 1 : 0;
        var companyID = document.getElementById('company_id').value;
        var userID = document.getElementById('user_id').value;
        var token = '{{ csrf_token() }}';

        var url = "{{ route('lead-setting.update_status', ':id') }}";
        url = url.replace(':id', companyID);

        $.easyAjax({
            type: 'POST',
            url: url,
            container: '#save-lead-status',
            blockUI: true,
            data: {
                '_token': token,
                lead_setting_status: status,
                id: companyID,
                userId: userID,
                requestFromTicket: 'yes',
            },
            success: function (response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        })
    });
});
</script>
