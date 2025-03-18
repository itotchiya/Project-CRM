<style>
    :root {
        /* For Logged in user take header color a app theme*/
        /* For public pages use company specific header color example invoice,estimate public page*/
        /* For all other pages use like auth use global setting header*/
        $purple: @if(isset($appTheme)) {{ $appTheme->header_color}} @elseif(isset($company)) {{$company->header_color}} @else {{ global_setting()->header_color}} @endif;
    }

    .btn-primary,
    .btn-primary.disabled:hover,
    .btn-primary:disabled:hover {
        background-color: $purple !important;
        border: 1px solid $purple !important;
    }

    .text-primary {
        color: $purple !important;
    }

    .bg-primary {
        background: $purple !important;
    }

    .datepicker table tr td, .datepicker table tr th {
        font-size: 14px;
    }

    .project-header .project-menu .p-sub-menu.active:after,
    .project-header .project-menu .p-sub-menu::after,
    .qs-current, .datepicker table tr td.active.active {
        background: $purple !important;
        text-shadow: none;
        border-color: $purple !important;
    }

    .sidebar-brand-box .sidebar-brand-dropdown a.dropdown-item:hover,
    .dropdown-item.active,
    .close-task-detail {
        background-color: $purple !important;
    }

    .pagination .page-item.active .page-link,
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: $purple !important;
        border-color: $purple !important;
    }

    .close-task-detail span {
        border: 1px solid $purple !important;
    }

    .tabs .nav .nav-link.active,
    .tabs .nav .nav-item:hover {
        border-bottom: 3px solid $purple !important;
    }

    .sidebar-light .sidebar-menu li .nav-item:focus,
    .sidebar-light .sidebar-menu li .nav-item:hover,
    .sidebar-light .sidebar-menu li .accordionItemContent a:hover {
        color: $purple !important;
    }

    .sidebar-light .accordionItem a.active {
        color: $purple !important;
    }

    .menu-item-count, .unread-notifications-count, .active-timer-count {
        background-color: $purple !important;
    }

    .dropdown-item.active .text-dark-grey {
        color: #ffffff;
    }

</style>
