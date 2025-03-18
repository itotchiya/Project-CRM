<div class="row">
    <div class="col-sm-12">
        <x-cards.data :title="__('app.menu.expenses') . ' ' . __('app.details')" class=" mt-4">
            @if (is_null($expense->expenses_recurring_id))
                <x-slot name="action">
                    <div class="dropdown">
                        <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                            @php
                                $trashBtn = (!is_null($expense->project) && is_null($expense->project->deleted_at)) ? true : (is_null($expense->project) ? true : false) ;
                            @endphp

                            @if ($trashBtn && $editExpensePermission == 'all' || ($editExpensePermission == 'added' && user()->id == $expense->added_by))
                                <a class="dropdown-item openRightModal" href="{{ route('expenses.edit', [$expense->id]) }}">@lang('app.edit')
                                        </a>
                                        @endif
                                @if ($deleteExpensePermission == 'all' || ($deleteExpensePermission == 'added' && user()->id == $expense->added_by))
                                    <a class="dropdown-item delete-table-row" href="javascript:;" data-expense-id="{{ $expense->id }}">@lang('app.delete')
                                    </a>
                                @endif
                        </div>
                    </div>
                </x-slot>
            @endif
            <x-cards.data-row :label="__('modules.expenses.itemName')" :value="$expense->item_name" />

            <x-cards.data-row :label="__('app.category')" :value="$expense->category->category_name ?? '--'" />

            <x-cards.data-row :label="__('app.price')" :value="$expense->total_amount" />

            <x-cards.data-row :label="__('modules.expenses.purchaseDate')"
                :value="(!is_null($expense->purchase_date) ? $expense->purchase_date->translatedFormat(company()->date_format) : '--')" />

            <x-cards.data-row :label="__('modules.expenses.purchaseFrom')" :value="$expense->purchase_from ?? '--'" />

            <x-cards.data-row :label="__('app.project')"
                :value="(!is_null($expense->project) && !is_null($expense->project->withTrashed()) ? $expense->project->project_name : '--')" />

            @php
                $bankName = !is_null($expense->bankAccount) ? ($expense->bankAccount->bank_name . ' | ' . $expense->bankAccount->account_name ?? '') : '--';
            @endphp
            <x-cards.data-row :label="__('app.menu.bankaccount')" :value="$bankName !== '' ? $bankName : '--'" />

            <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                <p class="mb-0 text-lightest f-14 w-30 ">
                    @lang('app.bill')</p>
                <p class="mb-0 text-dark-grey f-14">
                    @if (!is_null($expense->bill))
                        <a target="_blank" href="{{ $expense->bill_url }}" class="text-darkest-grey">@lang('app.view')
                            @lang('app.bill') <i class="fa fa-link"></i></a>&nbsp
                            <a href="{{ $expense->bill_url }}" class="text-darkest-grey" download>@lang('app.download')
                            <i class="fa fa-download f-w-500 mr-1 f-11"></i></a>
                    @else
                        --
                    @endif
                </p>
            </div>

            <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                <p class="mb-0 text-lightest f-14 w-30 ">
                    @lang('app.employee')</p>
                <p class="mb-0 text-dark-grey f-14">
                    <x-employee :user="$expense->user" />
                </p>
            </div>

            <x-cards.data-row :label="__('app.description')"
            :value="!empty($expense->description) ? $expense->description : '--'"
            html="true"/>

            <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                <p class="mb-0 text-lightest f-14 w-30 ">
                    @lang('app.status')</p>
                <p class="mb-0 text-dark-grey f-14">
                    @if ($expense->status == 'pending')
                        <x-status :value="__('app.'.$expense->status)" color="yellow" />
                    @elseif ($expense->status == 'approved')
                        <x-status :value="__('app.'.$expense->status)" color="dark-green" />
                    @else
                        <x-status :value="__('app.'.$expense->status)" color="red" />
                    @endif
                </p>
            </div>

            @if ($expense->status == 'approved')
                <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                    <p class="mb-0 text-lightest f-14 w-30 ">
                        @lang('modules.expenses.approvedBy')</p>
                    <p class="mb-0 text-dark-grey f-14">
                        <x-employee :user="$expense->approver" />
                    </p>
                </div>
            @endif


            <x-forms.custom-field-show :fields="$fields" :model="$expense"></x-forms.custom-field-show>

        </x-cards.data>
    </div>
</div>
<script>
    $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('expense-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('expenses.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                window.location.href = "{{ route('expenses.index')}}";
                            }
                        }
                    });
                }
            });
        });
</script>
