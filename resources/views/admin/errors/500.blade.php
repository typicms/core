@extends('admin::core.master')

@section('title', 'Error 500')

@section('bodyClass', 'error-500')

@section('content')
    <div class="error">
        <div class="error-header">
            <h1 class="error-title">@lang('Error :code', ['code' => '500'])</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">
                @lang('Sorry, a server error occurred.')
            </p>
        </div>
    </div>
@endsection
