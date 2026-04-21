@extends('core::admin.master')

@section('title', __('New user'))

@section('content')
    {!! BootForm::open()->action(route('admin::index-users'))->addClass('form') !!}
    @include('users::admin._form')
    {!! BootForm::close() !!}
@endsection
