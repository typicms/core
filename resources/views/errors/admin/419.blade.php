@extends('core::admin.master')

@section('title', 'Error 419')

@section('bodyClass', 'error-419')

@section('content')
    <div class="error">
        <div class="error-header">
            <h1 class="error-title">@lang('Error :code', ['code' => '419'])</h1>
        </div>
        <div class="error-body">
            <p class="mb-0">
                @lang('Page Expired')
            </p>
        </div>
    </div>
@endsection
