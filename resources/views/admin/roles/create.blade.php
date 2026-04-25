@extends('admin::core.master')

@section('title', __('New role'))

@section('content')
    {!! BootForm::open()->action(route('admin::index-roles'))->addClass('form') !!}
    @include('admin::roles._form')
    {!! BootForm::close() !!}
@endsection
