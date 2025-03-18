<x-form id="createAgent" method="POST" class="form-horizontal">
    <div class="modal-header">
        <h5 class="modal-title">@lang('app.addNewDealAgent')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
                <div class="row">
                    <input type="hidden" name="deal_category_id" value="{{request()->categoryId}}">
                    <div class="col-lg-6">
                        <div class="my-3">
                            <x-forms.select fieldId="deal_agents_id"     :fieldLabel="__('modules.tickets.chooseAgents')"
                                            fieldName="agent_id" search="true" fieldRequired="true">
                                @foreach ($employees as $emp)
                                    <x-user-option :user="$emp" :pill="true"/>
                                @endforeach
                            </x-forms.select>
                        </div>
                    </div>
                <div class="col-lg-6">
                    <div class="my-3">
                        <x-forms.select fieldId="lead_category" :fieldLabel="__('modules.deal.dealCategory')"
                            fieldName="category_id[]" search="true" multiple="true" fieldRequired="true">
                            @foreach ($leadCategories as $leadCategory)
                                <option value="{{$leadCategory->id}}">{{$leadCategory->category_name}}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                </div>
                </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-agent" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>

<script>

        var id = $("#deal_agents_id").val();
        agentCategories(id);

    $("#deal_agents_id").selectpicker({
        actionsBox: true,
        selectAllText: "{{ __('modules.permission.selectAll') }}",
        deselectAllText: "{{ __('modules.permission.deselectAll') }}",
        multipleSeparator: " ",
        selectedTextFormat: "count > 8",
        countSelectedText: function (selected, total) {
            return selected + " {{ __('app.membersSelected') }} ";
        }
    });

    $(".select-picker").selectpicker();

    // save agent
    $('#save-agent').click(function () {

        $.easyAjax({
            url: "{{ route('lead-agent-settings.store') }}",
            container: '#createAgent',
            type: "POST",
            blockUI: true,
            data: $('#createAgent').serialize(),
            disableButton: true,
            buttonSelector: "#save-agent",
            success: function (response) {
                if (response.status == "success") {
                    if ($('table#example').length) {
                        window.location.reload();
                    } else {
                        $('#deal_agents_id').html(response.data);
                        $('#deal_agents_id').selectpicker('refresh');
                        $('#deal_agent_id').html(response.data);
                        $('#deal_agent_id').selectpicker('refresh');
                        $(MODAL_LG).modal('hide');
                    }
                }
            }
        })
    });

    $('#deal_agents_id').change(function(){
        var agentId = $(this).val();
        agentCategories(agentId);
    });

        function agentCategories(agentId) {
            $.easyAjax({
                url: "{{ route('lead_agent.categories')}}",
                container: '#createMethods',
                type: "GET",
                blockUI: true,
                data: {agent_id:agentId},
                success: function(response) {
                        var options = [];
                        var rData = [];
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var selectData = '';
                            selectData = '<option value="' + value.id + '">' +
                                value
                                .category_name + '</option>';
                                options.push(selectData);
                        });
                        $('#lead_category').html(options);
                        $('#lead_category').selectpicker('refresh');

                }
            })
        }


</script>
