@extends('admin::core.master')

@section('title', $model->presentTitle())

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-tag', $model->id))->addClass('form') !!}
    {!! BootForm::bind($model) !!}
    @include('admin::tags._form')
    {!! BootForm::close() !!}
@endsection
