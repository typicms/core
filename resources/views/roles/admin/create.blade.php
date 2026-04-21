@extends('core::admin.master')

@section('title', __('New role'))

@section('content')
    {!! BootForm::open()->action(route('admin::index-roles'))->addClass('form') !!}
    @include('roles::admin._form')
    {!! BootForm::close() !!}
@endsection
