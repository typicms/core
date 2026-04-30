@props([
    'page',
    'title' => null,
    'description' => null,
    'keywords' => null,
    'ogTitle' => null,
    'ogImage' => null,
    'canonical' => null,
    'bodyClass' => null,
    'model' => null,
])

<x-core::layouts.public
    :title="$title ?? $page->metaTitle() . ' – ' . websiteTitle()"
    :og-title="$ogTitle ?? $page->metaTitle()"
    :description="$description ?? $page->meta_description ?? ''"
    :keywords="$keywords ?? $page->meta_keywords ?? ''"
    :og-image="$ogImage ?? $page->ogImageUrl()"
    :canonical="$canonical"
    :body-class="$bodyClass ?? 'body-page body-page-' . $page->id"
    :page="$page"
    :model="$model"
>
    @isset($headerTitle)
        <x-slot:header-title>{{ $headerTitle }}</x-slot:header-title>
    @endisset

    <x-core::json-ld :schema="[
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $page->title,
        'description' => $page->meta_description ?: null,
        'url' => $page->url(),
        'dateModified' => $page->updated_at->toIso8601String(),
        'inLanguage' => app()->getLocale(),
    ]" />

    @isset($pageHeader)
        {{ $pageHeader }}
    @else
        <header class="page-header">
            <div class="page-header-container">
                <h1>{{ $page->title }}</h1>
            </div>
        </header>
    @endisset

    {{ $slot }}
</x-core::layouts.public>
