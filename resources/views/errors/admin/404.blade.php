@extends('core::admin.master')

@section('title', 'Error 404')

@section('bodyClass', 'error-404')

@section('content')
    <div class="error">
        <div class="error-header">
            <h1 class="error-title">@lang('Error :code', ['code' => '404'])</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">
                @lang('Sorry, this page was not found.')
            </p>
        </div>
    </div>
@endsection
