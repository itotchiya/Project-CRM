@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
$viewBankAccount = user()->permission('view_bankaccount');
@endphp

@section('content')

    <div class="content-wrapper border-top-0">
        @include($view)
    </div>

@endsection

