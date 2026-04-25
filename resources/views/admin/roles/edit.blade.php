@extends('admin::core.master')

@section('title', $model->presentTitle())

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-role', $model->id))->addClass('form') !!}
    {!! BootForm::bind($model->toArray() + ['checked_permissions' => $checkedPermissions]) !!}
    @include('admin::roles._form')
    {!! BootForm::close() !!}
@endsection
