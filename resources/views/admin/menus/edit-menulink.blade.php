@extends('admin::core.master')

@section('title', $model->title)

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-menulink', [$menu->id, $model->id]))->addClass('form') !!}
    {!! BootForm::bind($model) !!}
    @include('admin::menus._form-menulink')
    {!! BootForm::close() !!}
@endsection
