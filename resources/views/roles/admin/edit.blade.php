@extends('core::admin.master')

@section('title', $model->presentTitle())

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-role', $model->id))->addClass('form') !!}
    {!! BootForm::bind($model->toArray() + ['checked_permissions' => $checkedPermissions]) !!}
    @include('roles::admin._form')
    {!! BootForm::close() !!}
@endsection
