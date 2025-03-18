<style>
    .description {
        margin-top: 5px;
        font-size: 0.9rem;
        color: #6c757d; /* Adjust the color as needed */
        padding-left: 24px;
    }
</style>

<div class="p-20 w-100">
    <div>@lang('modules.ticketSetting.heading')</div>
    <x-form id="save-ticket-agent-setting-data-form">
        <div class="col-lg-12">
            <input type="hidden" name="company_id" id="company_id" value="{{$company->id}}" />
            <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}" />

            <div class="form-group my-3">
                <x-forms.label fieldId="ticket-visibility-scope" :fieldLabel="__('modules.ticketSetting.ticketVisibilityScope')">
                </x-forms.label>
                <div class="">
                    <div class="">
                        <x-forms.radio fieldId="tickets-all" :fieldLabel="__('modules.ticketSetting.allTickets')"
                                       fieldName="ticket_scope" fieldValue="all_tickets"
                                       :checked="($ticketAgentSettings!= null ? $ticketAgentSettings->ticket_scope == 'all_tickets' : false)">
                        </x-forms.radio>
                        <div class="description">
                            @lang('modules.ticketSetting.allTicketsDescription')
                        </div>
                    </div>
                    <div class="">
                        <x-forms.radio fieldId="tickets-group" :fieldLabel="__('modules.ticketSetting.groupTickets')"
                                       fieldValue="group_tickets" :checked="($ticketAgentSettings!= null ? $ticketAgentSettings->ticket_scope == 'group_tickets' : false)"
                                       fieldName="ticket_scope"></x-forms.radio>
                        <div class="description">
                            @lang('modules.ticketSetting.groupTicketsDescription')
                        </div>
                    </div>
                    <div class="">
                        <x-forms.radio fieldId="tickets-assigned" :fieldLabel="__('modules.ticketSetting.assignedTickets')"
                                       fieldValue="assigned_tickets" :checked="($ticketAgentSettings!= null ? $ticketAgentSettings->ticket_scope == 'assigned_tickets' : true)"
                                       fieldName="ticket_scope"></x-forms.radio>
                        <div class="description">
                            @lang('modules.ticketSetting.assignedTicketsDescription')
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group my-3" id="group-select-container" style="display: none;">
                <x-forms.select fieldId="ticket_group_id" :fieldLabel="__('modules.ticketSetting.groups')"
                        fieldName="group_id[]" search="true" multiple="true">
                        @foreach ($groups as $group)
                                <option value="{{ $group->id }}" {{ in_array($group->id, $ticketAgentSettings?->group_id ?? []) ? 'selected' : '' }}>{{ $group->group_name }}</option>
                        @endforeach
                </x-forms.select>
            </div>

            <x-form-actions class="c-inv-btns">
                <div class="d-flex">
                    <x-forms.button-primary class="mr-3" id="save-ticket-agent-setting-form" icon="check">@lang('app.save')</x-forms.button-primary>
                </div>
            </x-form-actions>

        </div>
    </x-form>
</div>

<script>
    $(document).ready(function() {

        function toggleGroupSelect() {

            const ticketScope = $('input[name="ticket_scope"]:checked').val();

            if (ticketScope === 'group_tickets') {
                $('#group-select-container').show();
            } else {
                $('#group-select-container').hide();
                $('#ticket_group_id').val([]).trigger('change'); // Clear the group select field
            }
        }

        // Initial check to show/hide the group select field on page load
        toggleGroupSelect();

        // Event listener for changes in the ticket scope radio buttons
        $('input[name="ticket_scope"]').change(function() {
            toggleGroupSelect();
        });

        $('#save-ticket-agent-setting-form').click(function() {

            var companyID = $('#company_id').val();
            var userID = $('#user_id').val();
            var ticketScope = $('input[name="ticket_scope"]:checked').val();
            var groupID = $('#ticket_group_id').val();
            var token = '{{ csrf_token() }}';

            var url = "{{ route('ticket-agent-settings.update', ':id') }}";
            url = url.replace(':id', companyID);

            $.easyAjax({
                url: url,
                container: '#save-ticket-agent-setting-data-form',
                type: "POST",
                blockUI: true,
                data:  {
                    '_token': token,
                    id: companyID,
                    userId: userID,
                    ticketScope: ticketScope,
                    groupId: groupID,
                },
                success: function(response) {
                    if (response.status == "success") {
                        window.location.reload();
                    }
                }
            });
        });

    });
</script>
