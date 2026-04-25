@extends('admin::core.master')

@section('title', $model->presentTitle())

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-menu', $model->id))->addClass('form') !!}
    {!! BootForm::bind($model) !!}
    @include('admin::menus._form')
    {!! BootForm::close() !!}
@endsection
