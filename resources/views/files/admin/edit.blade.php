@extends('core::admin.master')

@section('title', $model->presentTitle())

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-file', $model->id))->addClass('form')->multipart() !!}
    {!! BootForm::bind($model) !!}
    @include('files::admin._form')
    {!! BootForm::close() !!}
@endsection
