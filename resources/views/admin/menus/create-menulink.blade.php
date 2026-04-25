@extends('admin::core.master')

@section('title', __('New menulink'))

@section('content')
    {!! BootForm::open()->action(route('admin::store-menulink', $menu->id))->addClass('form') !!}
    @include('admin::menus._form-menulink')
    {!! BootForm::close() !!}
@endsection
