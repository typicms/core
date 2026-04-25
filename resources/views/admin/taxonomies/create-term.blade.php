@extends('admin::core.master')

@section('title', __('New term'))

@section('content')
    {!! BootForm::open()->action(route('admin::index-terms', $taxonomy->id))->addClass('form') !!}
    @include('admin::taxonomies._form-term')
    {!! BootForm::close() !!}
@endsection
