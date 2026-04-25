@extends('admin::core.master')

@section('title', __('New translation'))

@section('content')
    {!! BootForm::open()->action(route('admin::index-translations'))->addClass('form') !!}
    @include('admin::translations._form')
    {!! BootForm::close() !!}
@endsection
