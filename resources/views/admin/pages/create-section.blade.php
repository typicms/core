@extends('admin::core.master')

@section('title', __('New page section'))

@section('content')
    {!! BootForm::open()->action(route('admin::store-page_section', $page->id))->addClass('form') !!}
    @include('admin::pages._form-section')
    {!! BootForm::close() !!}
@endsection
