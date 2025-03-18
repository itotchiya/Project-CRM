@extends('vendor.installer.layouts.master')

@section('title', trans('installer_messages.final.title'))
@section('container')
    <p @class([
            'alert alert-success',
            'alert-danger'=> session()->has('message') && session('message')['status'] !=='success',
        ])
       style="text-align: center;">{{ session()->has('message')? session('message')['message']:trans('installer_messages.final.finished') }}</p>
    <div class="buttons">
        <a href="{{ url('/') }}" class="button">{{ trans('installer_messages.final.exit') }}</a>
    </div>
@stop
