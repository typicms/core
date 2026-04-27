@extends('public::core.master')

@section('title', $page->metaTitle() . ' – ' . websiteTitle())
@section('ogTitle', $page->metaTitle())
@section('description', $page->meta_description ?? '')
@section('keywords', $page->meta_keywords ?? '')
@section('ogImage', $page->ogImageUrl())

@section('bodyClass', 'body-page body-page-' . $page->id)

@section('content')

<x-core::json-ld :schema="[
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => $page->title,
    'description' => $page->meta_description ?: null,
    'url' => $page->url(),
    'dateModified' => $page->updated_at->toIso8601String(),
    'inLanguage' => app()->getLocale(),
]" />

@section('page-header')
    <header class="page-header">
        <div class="page-header-container">
            <h1>{{ $page->title }}</h1>
        </div>
    </header>
@show

@yield('page')
@endsection
