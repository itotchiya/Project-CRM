<!-- ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-sm-9 mb-4 mb-xl-0 mb-lg-4 mb-md-0">

        <x-cards.data :title="__('modules.deal.dealInfo')">

            <x-slot name="action">
                <div class="dropdown">
                    <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-h"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                         aria-labelledby="dropdownMenuLink" tabindex="0">
                        <a class="dropdown-item openRightModal"
                           href="{{ route('deals.edit', $deal->id).'?tab=overview' }}">@lang('app.edit')</a>
                        @if (
                            $deleteLeadPermission == 'all'
                            || ($deleteLeadPermission == 'added' && user()->id == $deal->added_by)
                            || ($deleteLeadPermission == 'owned' && ((!is_null($deal->agent_id) && user()->id == $deal->leadAgent->user->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)))
                            || ($deleteLeadPermission == 'both' &&  (((!is_null($deal->agent_id) && user()->id == $deal->leadAgent->user->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)) || user()->id == $deal->added_by))
                        )
                            <a class="dropdown-item delete-table-row" href="javascript:;" data-id="{{ $deal->id }}">
                                @lang('app.delete')
                            </a>
                        @endif

                    </div>
                </div>
            </x-slot>

            <p class="f-w-500">
                <x-status style="color: {{ $deal->pipeline->label_color }}" color="yellow"
                          :value="$deal->pipeline->name"/>
                <i class="bi bi-arrow-right mx-"></i>
                <x-status style="color: {{ $deal->leadStage->label_color }}" color="yellow"
                          :value="$deal->leadStage->name"/>
            </p>
            <x-cards.data-row :label="__('modules.deal.dealName')" :value="$deal->name ?? '--'"/>



            <x-cards.data-row :label="__('modules.leadContact.leadContact')"
                              :value="$deal->contact->client_name_salutation ?? '--'"/>

            <x-cards.data-row :label="__('app.email')" :value="$deal->contact->client_email ?? '--'"/>

            <x-cards.data-row :label="__('modules.lead.companyName')"
                              :value="!empty($deal->contact->company_name) ? $deal->contact->company_name : '--'"/>

            <div class="col-12 px-0 pb-3 d-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                    @lang('modules.deal.dealAgent')</p>
                <p class="mb-0 text-dark-grey f-14">
                    @if (!is_null($deal->leadAgent))
                        <x-employee :user="$deal->leadAgent->user"/>
                    @else
                        --
                    @endif
                </p>
            </div>

            <div class="col-12 px-0 pb-3 d-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">{{ __('app.dealWatcher') }}</p>
                <p class="mb-0 text-dark-grey f-14">
                    @if (!is_null($deal->dealWatcher))
                        <x-employee :user="$deal->dealWatcher"/>
                    @else
                        --
                    @endif
                </p>
            </div>

            @if ($deal->leadStatus)
                <div class="col-12 px-0 pb-3 d-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">@lang('app.status')</p>
                    <p class="mb-0 text-dark-grey f-14">
                        <x-status :value="$deal->leadStatus->type"
                                  :style="'color:'.$deal->leadStatus->label_color"/>
                    </p>

                </div>
            @endif

            <x-cards.data-row :label="__('modules.deal.closeDate')"
                              :value="($deal->close_date) ? $deal->close_date->translatedFormat(company()->date_format) : '--'"/>
            <x-cards.data-row :label="__('modules.deal.dealValue')"
                              :value="($deal->value) ? currency_format($deal->value, $deal->currency_id) : '--'"/>

            <x-cards.data-row :label="__('modules.lead.products')"
                              :value="($productNames) ? implode(', ' , $productNames) : '--'"/>

            {{-- Custom fields data --}}
            <x-forms.custom-field-show :fields="$fields" :model="$deal"></x-forms.custom-field-show>

        </x-cards.data>
    </div>
    <!--  USER CARDS END -->

    <div class="col-sm-3">


        <x-cards.data :title="__('modules.leadContact.leadDetails')">

            <x-cards.data-row :label="__('modules.leadContact.leadContact')"
                              value="<a href='{{ route('lead-contact.show', $deal->contact->id) }}' class='text-darkest-grey'> {{ $deal->contact->client_name_salutation }}</a>"/>

            <x-cards.data-row :label="__('app.email')" :value="$deal->contact->client_email ?? '--'"/>
            <x-cards.data-row :label="__('modules.lead.mobile')" :value="$deal->contact->mobile ?? '--'"/>

            <x-cards.data-row :label="__('modules.lead.companyName')"
                              :value="!empty($deal->contact->company_name) ? $deal->contact->company_name : '--'"/>

            <div class="d-flex">
                @if ($deal->contact->client_email)
                    <x-forms.link-secondary class="mr-3" link='mailto:{{ $deal->contact->client_email }}'
                                            icon="envelope">@lang('app.email')</x-forms.link-secondary>
                @endif

                @if ($deal->contact->mobile )
                    <x-forms.button-secondary class="btn-copy" data-clipboard-text="{{ $deal->contact->mobile }}"
                                              icon="phone">@lang('app.mobile')</x-forms.button-secondary>
                @endif
            </div>

        </x-cards.data>
    </div>
</div>
<!-- ROW END -->
<script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>

<script>
    var clipboard = new ClipboardJS('.btn-copy');

    clipboard.on('success', function (e) {
        Swal.fire({
            icon: 'success',
            text: '@lang("app.phoneCopied")',
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
