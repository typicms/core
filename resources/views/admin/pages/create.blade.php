@extends('admin::core.master')

@section('title', __('New page'))

@section('content')
    {!! BootForm::open()->action(route('admin::index-pages'))->addClass('form') !!}
    @include('admin::pages._form')
    {!! BootForm::close() !!}
@endsection
