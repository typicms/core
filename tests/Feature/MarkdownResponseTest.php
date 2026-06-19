<?php

use TypiCMS\Modules\Core\Models\Page;

beforeEach(function (): void {
    $this->obLevel = ob_get_level();
});

afterEach(function (): void {
    while (ob_get_level() > $this->obLevel) {
        ob_end_clean();
    }
});

function publishedPage(): Page
{
    $page = Page::query()
        ->published()
        ->whereNotNull('slug->' . app()->getLocale())
        ->where('redirect', false)
        ->whereNull('module')
        ->first();

    expect($page)->not->toBeNull('No published page with English slug found');

    return $page;
}

test('regular browser requests receive HTML responses', function (): void {
    $this->get(publishedPage()->url())->assertOk()->assertHeader('Content-Type', 'text/html; charset=UTF-8');
});

test('requests with Accept text/markdown header receive markdown', function (): void {
    $this->get(publishedPage()->url(), ['Accept' => 'text/markdown'])->assertOk()->assertHeader(
        'Content-Type',
        'text/markdown; charset=UTF-8',
    );
});

test('requests from ClaudeBot user agent receive markdown', function (): void {
    $this->get(publishedPage()->url(), [
        'User-Agent' => 'ClaudeBot/1.0',
    ])->assertOk()->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
});

test('requests to .md URLs receive markdown', function (): void {
    $this->get(publishedPage()->url() . '.md')->assertOk()->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
});

test('admin routes are not affected by markdown middleware', function (): void {
    $this->get('/admin', ['Accept' => 'text/markdown'])->assertRedirect();
});

test('API routes are not affected by markdown middleware', function (): void {
    $this->get('/api/pages', ['Accept' => 'text/markdown'])->assertRedirect();
});

test('sitemap returns XML even when markdown is requested', function (): void {
    $response = $this->get('/sitemap.xml', ['Accept' => 'text/markdown']);

    $response->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('xml');
    expect($response->headers->get('Content-Type'))->not->toContain('text/markdown');
});
