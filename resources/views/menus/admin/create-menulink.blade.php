@extends('core::admin.master')

@section('title', __('New menulink'))

@section('content')
    {!! BootForm::open()->action(route('admin::store-menulink', $menu->id))->addClass('form') !!}
    @include('menus::admin._form-menulink')
    {!! BootForm::close() !!}
@endsection
