<!-- ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">

        <x-cards.data :title="__('modules.client.profileInfo')">

            <x-slot name="action">
                <div class="dropdown">
                    <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-h"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                        aria-labelledby="dropdownMenuLink" tabindex="0">

                        @if (
                            $editLeadPermission == 'all'
                            || $editLeadPermission == 'both' && (user()->id == $leadContact->added_by || user()->id == $leadContact->lead_owner)
                            || ($editLeadPermission == 'owned' && user()->id == $leadContact->lead_owner )
                            || ($editLeadPermission == 'added' && user()->id == $leadContact->added_by))
                        <a class="dropdown-item openRightModal"
                            href="{{ route('lead-contact.edit', $leadContact->id) }}">@lang('app.edit')</a>
                        @endif

                        @if (
                            $deleteLeadPermission == 'all'
                            || ($deleteLeadPermission == 'added' && user()->id == $leadContact->added_by)
                            || ($deleteLeadPermission == 'owned' && user()->id == $leadContact->lead_owner)
                            || ($deleteLeadPermission == 'both' && user()->id == $leadContact->added_by
                                    || user()->id == $leadContact->lead_owner))
                            <a class="dropdown-item delete-table-row" href="javascript:;" data-id="{{ $leadContact->id }}">
                                    @lang('app.delete')
                                </a>
                        @endif

                        @if ($leadContact->client_id == null || $leadContact->client_id == '')
                            <a class="dropdown-item" href="{{route('clients.create') . '?lead=' . $leadContact->id }}">
                                @lang('modules.lead.changeToClient')
                            </a>
                        @endif
                    </div>
                </div>
            </x-slot>
            <x-cards.data-row :label="__('app.name')" :value="$leadContact->client_name_salutation ?? '--'" />

            <x-cards.data-row :label="__('app.email')" :value="$leadContact->client_email ?? '--'" />

            @if(!is_null($leadContact->added_by))
                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                        @lang('app.addedBy')</p>
                    <p class="mb-0 text-dark-grey f-14 ">
                        <x-employee :user="$leadContact->addedBy" />
                    </p>
                </div>
            @endif

            @if(!is_null($leadContact->lead_owner))
                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                        @lang('app.owner')</p>
                    <p class="mb-0 text-dark-grey f-14 ">
                        <x-employee :user="$leadContact->leadOwner" />
                    </p>
                </div>
            @else
            <x-cards.data-row :label="__('app.owner')" :value="'--'" />
            @endif

            <x-cards.data-row :label="__('modules.lead.source')" :value="$leadContact->leadSource ? $leadContact->leadSource->type : '--'" />

            <x-cards.data-row :label="__('modules.lead.companyName')" :value="!empty($leadContact->company_name) ? $leadContact->company_name : '--'" />

            <x-cards.data-row :label="__('modules.lead.website')" :value="$leadContact->website ?? '--'" />

            <x-cards.data-row :label="__('modules.lead.mobile')" :value="$leadContact->mobile ?? '--'" />

            <x-cards.data-row :label="__('modules.client.officePhoneNumber')" :value="$leadContact->office ?? '--'" />

            <x-cards.data-row :label="__('app.country')" :value="$leadContact->country ?? '--'" />

            <x-cards.data-row :label="__('modules.stripeCustomerAddress.state')" :value="$leadContact->state ?? '--'" />

            <x-cards.data-row :label="__('modules.stripeCustomerAddress.city')" :value="$leadContact->city ?? '--'" />

            <x-cards.data-row :label="__('modules.stripeCustomerAddress.postalCode')" :value="$leadContact->postal_code ?? '--'" />

            <x-cards.data-row :label="__('modules.lead.address')" :value="$leadContact->address ?? '--'" />

            {{-- Custom fields data --}}
            <x-forms.custom-field-show :fields="$fields" :model="$leadContact"></x-forms.custom-field-show>

        </x-cards.data>
    </div>
    <!--  USER CARDS END -->
</div>
<!-- ROW END -->
