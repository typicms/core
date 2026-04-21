@extends('core::admin.master')

@section('title', $model->presentTitle())

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-menu', $model->id))->addClass('form') !!}
    {!! BootForm::bind($model) !!}
    @include('menus::admin._form')
    {!! BootForm::close() !!}
@endsection
