@extends('admin::core.master')

@section('title', $model->presentTitle())

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-translation', $model->id))->addClass('form') !!}
    {!! BootForm::bind($model) !!}
    @include('admin::translations._form')
    {!! BootForm::close() !!}
@endsection
