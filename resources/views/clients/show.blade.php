@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
$viewClientDoc = user()->permission('view_client_document');
$viewTicket = user()->permission('view_tickets');
$viewClientNote = user()->permission('view_client_note');
$viewClientContact = user()->permission('view_client_contacts');
$viewClientOrder = user()->permission('view_order');
@endphp

@section('filter-section')
    <!-- FILTER START -->
    <!-- PROJECT HEADER START -->

    <div class="d-flex d-lg-block filter-box project-header bg-white">
        <div class="mobile-close-overlay w-100 h-100" id="close-client-overlay"></div>

        <div class="project-menu" id="mob-client-detail">
            <a class="d-none close-it" href="javascript:;" id="close-client-detail">
                <i class="fa fa-times"></i>
            </a>

            <nav class="tabs">
                <ul class="-primary">
                    <li>
                        <x-tab :href="route('clients.show', $client->id)" :text="__('modules.employees.profile')" class="profile" ajax="false" />
                    </li>

                    @if (in_array('projects', user_modules()))
                    <li>
                        <x-tab :href="route('clients.show', $client->id).'?tab=projects'" ajax="false" :text="__('app.menu.projects')"
                            class="projects" />
                    </li>
                    @endif

                    @if (in_array('invoices', user_modules()))
                    <li>
                        <x-tab :href="route('clients.show', $client->id).'?tab=invoices'" ajax="false" :text="__('app.menu.invoices')"
                            class="invoices" />
                    </li>
                    @endif

                    @if (in_array('estimates', user_modules()))
                    <li>
                        <x-tab :href="route('clients.show', $client->id).'?tab=estimates'" ajax="false" :text="__('app.menu.estimates')"
                            class="estimates" />
                    </li>
                    @endif

                    <li>
                        <x-tab :href="route('clients.show', $client->id).'?tab=creditnotes'" ajax="false" :text="__('app.menu.credit-note')"
                            class="creditnotes" />
                    </li>

                    @if (in_array('payments', user_modules()))
                    <li>
                        <x-tab :href="route('clients.show', $client->id).'?tab=payments'" ajax="false" :text="__('app.menu.payments')"
                            class="payments" />
                    </li>
                    @endif

                    @if ($viewClientContact == 'all' || $viewClientContact == 'added')
                    <li>
                        <x-tab :href="route('clients.show', $client->id).'?tab=contacts'" ajax="false" :text="__('app.menu.contacts')"
                            class="contacts" />
                    </li>
                    @endif

                    @if ($viewClientDoc == 'all' || ($viewClientDoc == 'added' && $client->clientDetails->added_by == user()->id) || ($viewClientDoc == 'owned' && $client->clientDetails->user_id == user()->id) || ($viewClientDoc == 'both' && ($client->clientDetails->added_by == user()->id || $client->clientDetails->user_id == user()->id)))
                    <li>
                        <x-tab :href="route('clients.show', $client->id).'?tab=documents'" ajax="false" :text="__('app.menu.documents')"
                            class="documents" />
                    </li>
                    @endif

                    @if ($viewClientNote != 'none')
                    <li>
                        <x-tab :href="route('clients.show', $client->id).'?tab=notes'" ajax="false" :text="__('app.menu.notes')"
                            class="notes" />
                    </li>
                    @endif

                    @if ($viewTicket == 'all' || $viewTicket == 'added')
                    <li>
                        <x-tab :href="route('clients.show', $client->id).'?tab=tickets'" ajax="false" :text="__('app.menu.tickets')"
                            class="tickets" />
                    </li>
                    @endif

                    @if (in_array('orders', user_modules()) && $viewClientOrder == 'all' || ($viewClientOrder == 'added' && $client->clientDetails->added_by == user()->id) || ($viewClientOrder == 'owned' && $client->clientDetails->user_id == user()->id) || ($viewClientOrder == 'both' && ($client->clientDetails->added_by == user()->id || $client->clientDetails->user_id == user()->id)))

                    <li>
                        <x-tab :href="route('clients.show', $client->id).'?tab=orders'" ajax="false" :text="__('app.menu.orders')"
                            class="orders" />
                    </li>
                    @endif

                    @if ($gdpr->enable_gdpr)
                    <li>
                        <x-tab :href="route('clients.show', $client->id).'?tab=gdpr'" ajax="false" :text="__('app.menu.gdpr')"
                            class="gdpr" />
                    </li>
                    @endif
                </ul>
            </nav>
        </div>

        <a class="mb-0 d-block d-lg-none text-dark-grey ml-auto mr-2 border-left-grey" onclick="openClientDetailSidebar()"><i class="fa fa-ellipsis-v "></i></a>

    </div>
    <!-- FILTER END -->
    <!-- PROJECT HEADER END -->

@endsection

@push('styles')
<script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>
@endpush

@section('content')

    <div class="content-wrapper border-top-0 client-detail-wrapper">
        @include($view)
    </div>

@endsection

@push('scripts')
    <script>
        $("body").on("click", ".project-menu .ajax-tab", function(event) {
            event.preventDefault();

            $('.project-menu .p-sub-menu').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: ".content-wrapper",
                historyPush: true,
                success: function(response) {
                    if (response.status == "success") {
                        $('.content-wrapper').html(response.html);
                        init('.content-wrapper');
                    }
                }
            });
        });

    </script>
    <script>
        const activeTab = "{{ $activeTab }}";
        $('.project-menu .' + activeTab).addClass('active');
    </script>
    <script>
        /*******************************************************
                 More btn in projects menu Start
        *******************************************************/

        const container = document.querySelector('.tabs');
        const primary = container.querySelector('.-primary');
        const primaryItems = container.querySelectorAll('.-primary > li:not(.-more)');
        container.classList.add('--jsfied'); // insert "more" button and duplicate the list

        primary.insertAdjacentHTML('beforeend', `
        <li class="-more">
            <button type="button" class="px-4 h-100 bg-grey d-none d-lg-flex align-items-center" aria-haspopup="true" aria-expanded="false">
            {{__('app.more')}} <span>&darr;</span>
            </button>
            <ul class="-secondary" id="hide-project-menues">
            ${primary.innerHTML}
            </ul>
        </li>
        `);
        const secondary = container.querySelector('.-secondary');
        const secondaryItems = secondary.querySelectorAll('li');
        const allItems = container.querySelectorAll('li');
        const moreLi = primary.querySelector('.-more');
        const moreBtn = moreLi.querySelector('button');
        moreBtn.addEventListener('click', e => {
            e.preventDefault();
            container.classList.toggle('--show-secondary');
            moreBtn.setAttribute('aria-expanded', container.classList.contains('--show-secondary'));
        }); // adapt tabs

        const doAdapt = () => {
            // reveal all items for the calculation
            allItems.forEach(item => {
                item.classList.remove('--hidden');
            }); // hide items that won't fit in the Primary

            let stopWidth = moreBtn.offsetWidth;
            let hiddenItems = [];
            const primaryWidth = primary.offsetWidth;
            primaryItems.forEach((item, i) => {
                if (primaryWidth >= stopWidth + item.offsetWidth) {
                    stopWidth += item.offsetWidth;
                } else {
                    item.classList.add('--hidden');
                    hiddenItems.push(i);
                }
            }); // toggle the visibility of More button and items in Secondary

            if (!hiddenItems.length) {
                moreLi.classList.add('--hidden');
                container.classList.remove('--show-secondary');
                moreBtn.setAttribute('aria-expanded', false);
            } else {
                secondaryItems.forEach((item, i) => {
                    if (!hiddenItems.includes(i)) {
                        item.classList.add('--hidden');
                    }
                });
            }
        };

        doAdapt(); // adapt immediately on load

        window.addEventListener('resize', doAdapt); // adapt on window resize
        // hide Secondary on the outside click

        document.addEventListener('click', e => {
            let el = e.target;

            while (el) {
                if (el === secondary || el === moreBtn) {
                    return;
                }

                el = el.parentNode;
            }

            container.classList.remove('--show-secondary');
            moreBtn.setAttribute('aria-expanded', false);
        });
        /*******************************************************
                 More btn in projects menu End
        *******************************************************/
    </script>
@endpush
