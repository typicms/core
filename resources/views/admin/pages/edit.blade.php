@extends('admin::core.master')

@section('title', $model->presentTitle())

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-page', $model->id))->addClass('form') !!}
    {!! BootForm::bind($model) !!}
    @include('admin::pages._form')
    {!! BootForm::close() !!}
@endsection
