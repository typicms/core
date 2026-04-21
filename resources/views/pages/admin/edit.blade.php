@extends('core::admin.master')

@section('title', $model->presentTitle())

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-page', $model->id))->addClass('form') !!}
    {!! BootForm::bind($model) !!}
    @include('pages::admin._form')
    {!! BootForm::close() !!}
@endsection
