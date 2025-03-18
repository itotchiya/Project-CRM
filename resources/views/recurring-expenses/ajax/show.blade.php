<div class="row">
    <div class="col-sm-12">
        <x-cards.data :title="__('app.recurring') . ' ' . __('app.details')" class=" mt-4">

            <x-cards.data-row :label="__('modules.invoices.billingFrequency')"
                :value="__('app.' . $expense->rotation)" />

            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                    @lang('modules.expensesRecurring.completedTotalExpense')</p>
                <p class="mb-0 text-dark-grey f-14 ">
                    @if(!is_null($expense->billing_cycle))
                        {{$expense->recurrings->count()}}/{{$expense->billing_cycle}}
                    @else
                        {{$expense->recurrings->count()}}/<span class="px-1"><label class="badge badge-primary">@lang('app.infinite')</label></span>
                    @endif
                </p>
            </div>
            @if (count($expense->recurrings)>0)
                <x-cards.data-row :label="__('app.last').' '.__('app.expense').' '.__('app.date')"
                :value="$expense->recurrings->last()->purchase_date->translatedFormat(company()->date_format)" />
            @else
                <x-cards.data-row :label="__('modules.expensesRecurring.firstExpenseDate')"
                :value="$expense->issue_date ? $expense->issue_date->translatedFormat(company()->date_format) : '----'" />
            @endif

            <x-cards.data-row :label="__('modules.expensesRecurring.nextExpense').' '.__('app.date')"
            :value="$expense->next_expense_date ? $expense->next_expense_date->translatedFormat(company()->date_format) : '---'" />

            <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                <p class="mb-0 text-lightest f-14 w-30 ">
                    @lang('app.status')</p>
                <p class="mb-0 text-dark-grey f-14">
                    @if ($expense->status == 'active')
                        <x-status :value="__('app.'.$expense->status)" color="dark-green" />
                    @else
                        <x-status :value="__('app.'.$expense->status)" color="red" />
                    @endif
                </p>
            </div>
        </x-cards.data>
        <x-cards.data :title="__('app.menu.expenses') . ' ' . __('app.details')" class=" mt-4">
            <x-cards.data-row :label="__('modules.expenses.itemName')" :value="$expense->item_name" />

            <x-cards.data-row :label="__('app.category')" :value="$expense->category->category_name ?? '--'" />

            <x-cards.data-row :label="__('app.price')" :value="currency_format($expense->price, $expense->currency_id)" />

            <x-cards.data-row :label="__('modules.expenses.purchaseFrom')" :value="$expense->purchase_from ?? '--'" />

            <x-cards.data-row :label="__('app.project')"
                :value="(!is_null($expense->project_id) ? $expense->project->project_name : '--')" />

            <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                <p class="mb-0 text-lightest f-14 w-30 ">
                    @lang('app.bill')</p>
                <p class="mb-0 text-dark-grey f-14">
                    @if (!is_null($expense->bill))
                        <a target="_blank" href="{{ $expense->bill_url }}" class="text-darkest-grey">@lang('app.view')
                            @lang('app.bill') <i class="fa fa-link"></i></a>
                    @else
                        --
                    @endif
                </p>
            </div>

            <x-cards.data-row :label="__('app.bankaccount')"
                :value="(!is_null($expense->bank) ? $expense->bank->bank_name : '--')" />

            <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                <p class="mb-0 text-lightest f-14 w-30 ">
                    @lang('app.employee')</p>
                <p class="mb-0 text-dark-grey f-14">
                    <x-employee :user="$expense->user" />
                </p>
            </div>
        </x-cards.data>

    </div>
</div>
