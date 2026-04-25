@extends('admin::core.master')

@section('title', 'Error 403')

@section('bodyClass', 'error-403')

@section('content')
    <div class="error">
        <div class="error-header">
            <h1 class="error-title">@lang('Error :code', ['code' => '403'])</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">
                @lang('Sorry, you are not authorized to view this page.')
            </p>
        </div>
    </div>
@endsection
