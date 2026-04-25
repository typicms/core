@extends('admin::core.master')

@section('title', __('New content block'))

@section('content')
    {!! BootForm::open()->action(route('admin::index-blocks'))->addClass('form') !!}
    @include('admin::blocks._form')
    {!! BootForm::close() !!}
@endsection
