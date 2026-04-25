@extends('admin::core.master')

@section('title', $model->presentTitle())

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-user', $model->id))->addClass('form') !!}
    {!! BootForm::bind($model->toArray() + ['checked_roles' => $checkedRoles]) !!}
    @include('admin::users._form')
    {!! BootForm::close() !!}
@endsection
