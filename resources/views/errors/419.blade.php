@extends('public::core.master')

@section('title', __('Error :code', ['code' => '419']) . ' – ' . $websiteTitle)

@section('bodyClass', 'error-419')

@section('content')
    <header class="page-header">
        <div class="page-header-container">
            <h1 class="page-title">
                @lang('Error :code', ['code' => '419'])
            </h1>
        </div>
    </header>

    <div class="page-body">
        <div class="page-body-container">
            <p>
                @lang('Page Expired')
                <br>
                @lang('Go to our homepage?', ['a_open' => '<a href="/">', 'a_close' => '</a>'])
            </p>
        </div>
    </div>
@endsection
