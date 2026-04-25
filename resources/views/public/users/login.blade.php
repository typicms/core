@extends('admin::core.master')

@section('title', __('Login'))
@section('bodyClass', 'auth-background')
@section('sidebar', '')
@section('mainClass', '')

@section('content')
    <div id="login" class="container-login auth auth-sm">
        <x-core::auth-header />

        <x-core::authenticate-passkey />

        <x-core::status />

        <x-core::register-info />

        <x-core::back-to-website-link />

    </div>
@endsection
