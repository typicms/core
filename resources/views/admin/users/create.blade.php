@extends('admin::core.master')

@section('title', __('New user'))

@section('content')
    {!! BootForm::open()->action(route('admin::index-users'))->addClass('form') !!}
    @include('admin::users._form')
    {!! BootForm::close() !!}
@endsection
