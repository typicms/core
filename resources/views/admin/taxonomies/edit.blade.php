@extends('admin::core.master')

@section('title', $model->title)

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-taxonomy', $model->id))->addClass('form') !!}
    {!! BootForm::bind($model) !!}
    @include('admin::taxonomies._form')
    {!! BootForm::close() !!}
@endsection
