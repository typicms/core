@extends('admin::core.master')

@section('title', __('New tag'))

@section('content')
    {!! BootForm::open()->action(route('admin::index-tags'))->addClass('form') !!}
    @include('admin::tags._form')
    {!! BootForm::close() !!}
@endsection
