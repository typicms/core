<?php

use Illuminate\Http\Request;
use TypiCMS\Modules\Core\Http\Middleware\RewriteModuleUri;
use TypiCMS\Modules\Core\Models\Page;

function rewrite(string $uri): Request
{
    $passed = null;

    new RewriteModuleUri()->handle(
        Request::create($uri, 'GET'),
        function (Request $request) use (&$passed): Request {
            return $passed = $request;
        },
    );

    return $passed;
}

beforeEach(function (): void {
    $this->page = Page::query()->create([
        'module' => 'tags',
        'title' => array_fill_keys(locales(), 'Labels'),
        'slug' => array_fill_keys(locales(), 'labels'),
        'status' => array_fill_keys(locales(), '1'),
        'position' => 999,
    ]);
});

test('the page of a module is rewritten onto its internal path', function (): void {
    expect(rewrite('/en/labels')->path())->toBe('en/_module/tags');
});

test('a segment below the page is carried over', function (): void {
    expect(rewrite('/en/labels/laravel')->path())->toBe('en/_module/tags/laravel');
});

test('the query string is carried over as it was sent', function (): void {
    $request = rewrite('/en/labels?z=1&a=2');

    expect($request->getRequestUri())->toBe('/en/_module/tags?z=1&a=2')
        ->and($request->query->all())->toBe(['z' => '1', 'a' => '2']);
});

test('the resolved page is passed along', function (): void {
    expect(rewrite('/en/labels')->attributes->get('typicms.module_page')->id)->toBe($this->page->id);
});

test('a path that belongs to no module is left alone', function (): void {
    $request = rewrite('/en/not-a-module-page');

    expect($request->path())->toBe('en/not-a-module-page')
        ->and($request->attributes->has('typicms.module_page'))->toBeFalse();
});

test('the back office is left alone', function (): void {
    expect(rewrite('/admin/labels')->path())->toBe('admin/labels');
});
