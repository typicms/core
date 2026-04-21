@extends('core::admin.master')

@section('title', 'Error 429')

@section('bodyClass', 'error-429')

@section('content')
    <div class="error">
        <div class="error-header">
            <h1 class="error-title">@lang('Error :code', ['code' => '429'])</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">
                @lang('Too Many Requests')
            </p>
        </div>
    </div>
@endsection
