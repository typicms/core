@extends('core::admin.master')

@section('title', __('New page'))

@section('content')
    {!! BootForm::open()->action(route('admin::index-pages'))->addClass('form') !!}
    @include('pages::admin._form')
    {!! BootForm::close() !!}
@endsection
