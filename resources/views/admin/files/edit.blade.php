@extends('admin::core.master')

@section('title', $model->presentTitle())

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-file', $model->id))->addClass('form')->multipart() !!}
    {!! BootForm::bind($model) !!}
    @include('admin::files._form')
    {!! BootForm::close() !!}
@endsection
