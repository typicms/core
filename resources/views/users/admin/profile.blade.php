@extends('core::admin.master')

@section('title')
    {{ __('Profile') }}
@endsection

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-profile'))->addClass('form') !!}
    {!! BootForm::bind($model->toArray()) !!}
    @include('users::admin._profile_form')
    {!! BootForm::close() !!}
@endsection
