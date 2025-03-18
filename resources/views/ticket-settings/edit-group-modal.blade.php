<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.tickets.manageGroups')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span>
    </button>
</div>

<div class="modal-body">
    <div class="portlet-body">
        <x-form id="editTicketGroup" method="PUT" class="ajax-form">
            <div class="form-body">
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.tickets.groupName')"
                            :fieldPlaceholder="__('placeholders.tickets.ticketGroup')" fieldRequired="true" fieldName="group_name"
                            :fieldValue="$group->group_name" fieldId="group_name"/>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>

<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-group" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>

    $('#save-group').click(function () {
        $.easyAjax({
            url: "{{route('ticket-groups.update', $group->id)}}",
            container: '#editTicketGroup',
            type: "POST",
            blockUI: true,
            data: $('#editTicketGroup').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    window.location.reload();
                }
            }
        })
    });
</script>
