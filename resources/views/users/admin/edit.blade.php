@extends('core::admin.master')

@section('title', $model->presentTitle())

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-user', $model->id))->addClass('form') !!}
    {!! BootForm::bind($model->toArray() + ['checked_roles' => $checkedRoles]) !!}
    @include('users::admin._form')
    {!! BootForm::close() !!}
@endsection
