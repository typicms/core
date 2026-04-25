@extends('admin::core.master')

@section('title', __('New menu'))

@section('content')
    {!! BootForm::open()->action(route('admin::index-menus'))->addClass('form') !!}
    @include('admin::menus._form')
    {!! BootForm::close() !!}
@endsection
