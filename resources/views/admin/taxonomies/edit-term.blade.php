@extends('admin::core.master')

@section('title', $model->title)

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-term', [$taxonomy->id, $model->id]))->addClass('form') !!}
    {!! BootForm::bind($model) !!}
    @include('admin::taxonomies._form-term')
    {!! BootForm::close() !!}
@endsection
