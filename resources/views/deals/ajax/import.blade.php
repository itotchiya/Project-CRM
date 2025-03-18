<div class="row" id="import_table">
    <div class="col-sm-12">
        <x-form id="import-deal-data-form">
            <div class="add-deal bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.importDeal')</h4>
                <div class="col-sm-12 pt-2">
                    <div class="alert alert-warning" role="alert">
                        @lang('app.importDealExcelInfo')
                    </div>
                </div>
                <div class="row py-20">
                    <div class="col-md-12">
                        <x-forms.link-secondary :link="asset('sample-import/deal-sample.xlsx')" icon="download">@lang('app.downloadSampleImport')</x-forms.link-secondary>

                        <x-forms.file :fieldLabel="__('modules.import.file')" fieldName="import_file"
                                      fieldId="deal_import"/>
                    </div>
                    <div class="col-md-12">
                        <x-forms.toggle-switch class="mr-0 mr-lg-12"
                                               :fieldLabel="__('modules.import.containsHeadings')"
                                               fieldName="heading"
                                               fieldId="heading"/>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="import-deal-form" class="mr-3"
                                            icon="arrow-right">@lang('app.uploadNext')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('deals.index')" class="border-0">@lang('app.back')
                    </x-forms.button-cancel>

                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>

    $(document).ready(function () {

        $("#deal_import").dropify({
            messages: dropifyMessages
        });

        $('body').on('click', '#import-deal-form', function () {
            const url = "{{ route('deals.import.store') }}";

            $.easyAjax({
                url: url,
                container: '#import-deal-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#import-deal-form",
                file: true,
                data: $('#import-deal-data-form').serialize(),
                success: function (response) {
                    if (response.status == 'success') {
                        $('#import_table').html(response.view);
                    }
                }
            });
        });
    });
</script>
