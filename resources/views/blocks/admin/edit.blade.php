@extends('core::admin.master')

@section('title', $model->presentTitle())

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-block', $model->id))->addClass('form') !!}
    {!! BootForm::bind($model) !!}
    @include('blocks::admin._form')
    {!! BootForm::close() !!}
@endsection
