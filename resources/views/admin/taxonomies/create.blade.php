@extends('admin::core.master')

@section('title', __('New taxonomy'))

@section('content')
    {!! BootForm::open()->action(route('admin::index-taxonomies'))->addClass('form') !!}
    @include('admin::taxonomies._form')
    {!! BootForm::close() !!}
@endsection
