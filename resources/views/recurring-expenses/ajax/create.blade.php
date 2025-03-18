@php
$addExpenseCategoryPermission = user()->permission('manage_expense_category');

$projectName = '';
foreach ($projects as $project) {
    if ($projectId == $project->id) {
        $projectName = $project->project_name;
    }
}

@endphp
<style>
    .information-box {
        border-style: dotted;
        margin-bottom: 30px;
        margin-top:10px;
        padding-top: 10px;
        border-radius: 4px;
    }
</style>
<div class="row">
    <div class="col-sm-12">
        <x-form id="save-expense-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.expenseDetails')</h4>
                <div class="row p-20">
                    <div class="col-md-6 col-lg-4">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.expenses.itemName')"
                            fieldName="item_name" fieldRequired="true" fieldId="item_name"
                            :fieldPlaceholder="__('placeholders.expense.item')" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <input type="hidden" id="currency_id" name="currency_id" value="{{company()->currency_id}}">
                        <x-forms.select :fieldLabel="__('modules.invoices.currency')" fieldName="currency"
                            fieldRequired="true" fieldId="currency">
                            @foreach ($currencies as $currency)
                                <option @if ($currency->id == company()->currency_id) selected @endif value="{{ $currency->id }}" data-currency-name="{{$currency->currency_code}}">
                                    {{ $currency->currency_code }} ({{ $currency->currency_symbol }})
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.price')" fieldName="price"
                            fieldRequired="true" fieldId="price" :fieldPlaceholder="__('placeholders.price')" />

                    </div>

                    @if (user()->permission('add_expenses') == 'all')
                        <div class="col-md-6 col-lg-4">
                            <x-forms.label class="mt-3" fieldId="user_id" :fieldLabel="__('app.employee')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="user_id" id="user_id"
                                    data-live-search="true" data-size="8">
                                    <option value="">--</option>
                                    @foreach ($employees as $item)
                                        <x-user-option :user="$item" />
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>
                    @else
                        <input type="hidden" name="user_id" value="{{ user()->id }}">
                    @endif

                    <div class="col-md-6 col-lg-4">
                        @if (!empty($projectName))
                            <input type="hidden" name="project_id" id="project_id" value="{{ $projectId }}">
                            <x-forms.text :fieldLabel="__('app.project')" fieldName="projectName" fieldId="projectName"
                                :fieldValue="$projectName" fieldReadOnly="true" />
                        @else
                            <x-forms.select fieldId="project_id" fieldName="project_id" :fieldLabel="__('app.project')"
                                search="true">
                                <option value="">--</option>
                                @foreach ($projects as $project)
                                    <option data-currency-id="{{ $project->currency_id }}" @if ($projectId == $project->id) selected @endif value="{{ $project->id }}">
                                        {{ $project->project_name }}
                                    </option>
                                @endforeach
                            </x-forms.select>
                        @endif
                    </div>

                    @if($linkExpensePermission == 'all')
                        <div class="col-md-6 col-lg-4">
                            <x-forms.select fieldId="bank_account_id" :fieldLabel="__('app.menu.bankaccount')" fieldName="bank_account_id"
                                search="true">
                                <option value="">--</option>
                                @if($viewBankAccountPermission != 'none')
                                    @foreach ($bankDetails as $bankDetail)
                                        <option value="{{ $bankDetail->id }}">@if($bankDetail->type == 'bank')
                                            {{ $bankDetail->bank_name }} | @endif {{ $bankDetail->account_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </x-forms.select>
                        </div>
                    @endif

                    <div class="col-md-6">
                        <x-forms.label class="mt-3" fieldId="category_id"
                            :fieldLabel="__('modules.expenses.expenseCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="expense_category_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($addExpenseCategoryPermission == 'all' || $addExpenseCategoryPermission == 'added')
                                <x-slot name="append">
                                    <button id="addExpenseCategory" type="button"
                                        class="btn btn-outline-secondary border-grey"
                                        data-toggle="tooltip" data-original-title="{{__('modules.expenseCategory.addExpenseCategory') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-6">
                        <x-forms.text :fieldLabel="__('modules.expenses.purchaseFrom')" fieldName="purchase_from"
                            fieldId="purchase_from" :fieldPlaceholder="__('placeholders.expense.vendor')" />
                    </div>

                    <div class="col-lg-12">
                        <x-forms.file :fieldLabel="__('app.bill')" fieldName="bill" fieldId="bill" allowedFileExtensions="txt pdf doc xls xlsx docx rtf png jpg jpeg svg" :popover="__('messages.fileFormat.multipleImageFile')" />
                    </div>


                </div>

                <hr class="m-0 border-top-grey">
                <div class="row px-lg-4 px-md-4 px-3 pt-3">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-4 mt-4">
                                <x-forms.label fieldId="rotation" :fieldLabel="__('modules.invoices.billingFrequency')"
                                            fieldRequired="true">
                                </x-forms.label>
                                <div class="form-group c-inv-select">
                                    <select class="form-control select-picker" data-live-search="true" data-size="8" name="rotation"
                                            id="rotation">
                                        <option value="daily">@lang('app.daily')</option>
                                        <option value="weekly">@lang('app.weekly')</option>
                                        <option value="bi-weekly">@lang('app.bi-weekly')</option>
                                        <option value="monthly">@lang('app.monthly')</option>
                                        <option value="quarterly">@lang('app.quarterly')</option>
                                        <option value="half-yearly">@lang('app.half-yearly')</option>
                                        <option value="annually">@lang('app.annually')</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8 mt-4">
                                <div class="form-group">
                                    <div class="d-flex">
                                        <x-forms.label class="mr-3" fieldId="start_date" :fieldLabel="__('app.startDate')">
                                        </x-forms.label>
                                        <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2 mt-0"
                                                        :fieldLabel="__('modules.expensesRecurring.immediateExpense')"
                                                        fieldName="immediate_expense"
                                                        fieldId="immediate_expense" fieldValue="true" fieldRequired="true"/>
                                    </div>
                                    <div class="input-group">
                                        <input type="text" id="start_date" name="issue_date"
                                            class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                            placeholder="@lang('placeholders.date')"
                                            value="{{ now(company()->timezone)->format(company()->date_format) }}">
                                    </div>
                                    <small class="form-text text-muted">@lang('modules.recurringInvoice.invoiceDate')</small>
                                </div>
                            </div>
                            <div class="col-lg-4 mt-0">
                                <x-forms.number class="mr-0 mr-lg-2 mr-md-2 mt-0" :fieldLabel="__('modules.invoices.totalCount')"
                                                fieldName="billing_cycle" fieldId="billing_cycle"
                                                :fieldHelp="__('modules.invoices.noOfBillingCycle')"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mt-4 information-box">
                        <p id="plan">@lang('app.expenseGeneratedDaily')</p>
                        <p id="current_date">@lang('modules.expensesRecurring.currentExpenseDate') {{now()->translatedFormat(company()->date_format)}}</p>
                        <p id="next_date">@lang('modules.expensesRecurring.nextExpenseDate') {{now()->addDay()->translatedFormat(company()->date_format)}}</p>
                        <p>@lang('modules.recurringInvoice.soOn')</p>
                        <span id="billing"></span>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-expense-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('expenses.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script>
    $(document).ready(function() {
        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        const dp1 = datepicker('#start_date', {
            position: 'bl',
            onSelect: (instance, date) => {
                var rotation = $('#rotation').val();
                nextDate(rotation, date);
            },
            ...datepickerConfig
        });

        $('#rotation').trigger("change");

        $('#save-expense-form').click(function() {
            const url = "{{ route('recurring-expenses.store') }}";
            var data = $('#save-expense-data-form').serialize();

            $.easyAjax({
                url: url,
                container: '#save-expense-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-expense-form",
                data: data,
                file: true,
                success: function(response) {
                    window.location.href = response.redirectUrl;
                }
            });
        });

        $('#addExpenseCategory').click(function() {
            const url = "{{ route('expenseCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('change', '#user_id', function() {
            let userId = $(this).val();

            const url = "{{ route('expenses.get_employee_projects') }}";
            let data = $('#save-expense-data-form').serialize();

            $.easyAjax({
                url: url,
                type: "GET",
                data: {
                    'userId': userId
                },
                success: function(response) {
                    $('#project_id').html('<option value="">--</option>' + response.data);
                    $('#project_id').selectpicker('refresh')
                    if($('#project_id').val() == '')
                    {
                        $('#currency').prop('disabled', false);
                        $('#currency').selectpicker('refresh');
                    }
                }
            });

        });

        init(RIGHT_MODAL);
    });

    $('body').on('change keyup', '#rotation, #billing_cycle', function () {
        var billingCycle = $('#billing_cycle').val();
        billingCycle != '' ? $('#billing').html("{{__('modules.recurringInvoice.billingCycle')}}" + ' ' + billingCycle) : $('#billing').html('');

        var rotation = $('#rotation').val();
        switch (rotation) {
            case 'daily':
                var rotationType = "{{__('app.daily')}}";
                break;
            case 'weekly':
                var rotationType = "{{__('app.every')}}"+' '+"{{__('modules.recurringInvoice.week')}}";
                break;
            case 'bi-weekly':
                var rotationType = "{{__('app.every')}}"+' '+"{{__('app.bi-week')}}";
                break;
            case 'monthly':
                var rotationType = "{{__('app.every')}}"+' '+"{{__('app.month')}}";
                break;
            case 'quarterly':
                var rotationType = "{{__('app.every')}}"+' '+"{{__('app.quarter')}}";
                break;
            case 'half-yearly':
                var rotationType = "{{__('app.every')}}"+' '+"{{__('app.half-year')}}";
                break;
            case 'annually':
                var rotationType = "{{__('app.every')}}"+' '+"{{__('app.year')}}";
                break;
            default:
            //
        }

        $('#plan').html("{{__('modules.expensesRecurring.expenseGenerated')}}" + ' ' + rotationType);

        if ($('#immediate_expense').is(':checked')) {
            var date = moment().toDate();
        } else {
            var startDate = $('#start_date').val();
            var date = moment(startDate, 'DD-MM-YYYY').toDate();
        }

        nextDate(rotation, date);
    })

    $('#immediate_expense').change(function () {
        var rotation = $('#rotation').val();

        if ($(this).is(':checked')) {
            var date = moment().toDate();
            $('#start_date').val(moment(date, "DD-MM-YYYY").format('{{ company()->moment_date_format }}'));
            $('#start_date').prop('disabled', true)
        } else {
            $('#start_date').prop('disabled', false)
            var startDate = $('#start_date').val();
            var date = moment(startDate, 'DD-MM-YYYY').toDate();
        }

        nextDate(rotation, date);

    })

    function nextDate(rotation, date) {
        var nextDate = moment(date, "DD-MM-YYYY");
        var currentValue = nextDate.format('{{ company()->moment_date_format }}');

        switch (rotation) {
            case 'daily':
                var rotationDate = nextDate.add(1, 'days');
                break;
            case 'weekly':
                var rotationDate = nextDate.add(1, 'weeks');
                break;
            case 'bi-weekly':
                var rotationDate = nextDate.add(2, 'weeks');
                break;
            case 'monthly':
                var rotationDate = nextDate.add(1, 'months');
                break;
            case 'quarterly':
                var rotationDate = nextDate.add(1, 'quarters');
                break;
            case 'half-yearly':
                var rotationDate = nextDate.add(2, 'quarters');
                break;
            case 'annually':
                var rotationDate = nextDate.add(1, 'years');
                break;
            default:
            //
        }

        var value = rotationDate.format('{{ company()->moment_date_format }}');

        $('#current_date').html("{{__('modules.expensesRecurring.currentExpenseDate')}}" + ' ' + currentValue);

        $('#next_date').html("{{__('modules.expensesRecurring.nextExpenseDate')}}" + ' ' + value);
    }

    $('body').on("change", '#currency, #project_id', function() {
        if ($('#project_id').val() != '') {
            var curId = $('#project_id option:selected').attr('data-currency-id');
            $('#currency').removeAttr('disabled');
            $('#currency').selectpicker('refresh');
            $('#currency').val(curId);
            $('#currency').prop('disabled', true);
            $('#currency').selectpicker('refresh');
        } else {
            $('#currency').prop('disabled', false);
            $('#currency').selectpicker('refresh');
        }

        var id = $('#currency').val();
        $('#currency_id').val(id);
        var currencyId = $('#currency_id').val();

        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: "{{ route('payments.account_list') }}",
            type: "GET",
            blockUI: true,
            data: { 'curId' : currencyId , _token: token},
            success: function(response) {
                if (response.status == 'success') {
                    $('#bank_account_id').html(response.data);
                    $('#bank_account_id').selectpicker('refresh');
                }
            }
        });
    });
</script>
