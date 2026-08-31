<?php

use Illuminate\Support\Facades\Route;
use TypiCMS\Modules\Core\Models\Page;
use TypiCMS\Modules\Core\Models\Tag;
use TypiCMS\Modules\Core\Support\ModuleRoutes;
use TypiCMS\Modules\Core\Support\ModuleUrl;

function pageLinkedToTags(string $slug = 'labels'): Page
{
    return Page::query()->create([
        'module' => 'tags',
        'title' => array_fill_keys(locales(), 'Labels'),
        'slug' => array_fill_keys(locales(), $slug),
        'status' => array_fill_keys(locales(), '1'),
        'position' => 999,
    ]);
}

test('no module route carries a page uri', function (): void {
    $moduleRoutes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route): bool => str_contains($route->uri(), ModuleRoutes::SEGMENT));

    expect($moduleRoutes)->not->toBeEmpty();

    $moduleRoutes->each(function ($route): void {
        expect($route->uri())->toMatch('#^([a-z]{2}/)?'.ModuleRoutes::SEGMENT.'/#');
    });

    expect(Route::has('en::index-tags'))->toBeFalse();
    expect(Route::has('en::search'))->toBeFalse();
});

test('the page of a module renders the module index', function (): void {
    $page = pageLinkedToTags();

    $this->get($page->url('en'))
        ->assertOk()
        ->assertViewIs('public::tags.index');
});

test('a segment below the page of a module renders the module item', function (): void {
    $page = pageLinkedToTags();
    $tag = Tag::query()->firstOrFail();

    $this->get($page->url('en').'/'.$tag->slug)
        ->assertOk()
        ->assertViewIs('public::tags.show');
});

test('an unknown item below the page of a module is not found', function (): void {
    $page = pageLinkedToTags();

    $this->get($page->url('en').'/no-such-tag')->assertNotFound();
});

test('the internal path of a module is not reachable', function (): void {
    pageLinkedToTags();

    $this->get('/en/'.ModuleRoutes::SEGMENT.'/tags')->assertNotFound();
});

test('a subpage wins over a module item', function (): void {
    $page = pageLinkedToTags();

    $subpage = Page::query()->create([
        'parent_id' => $page->id,
        'title' => array_fill_keys(locales(), 'A subpage'),
        'slug' => array_fill_keys(locales(), 'a-subpage'),
        'status' => array_fill_keys(locales(), '1'),
        'position' => 1,
    ]);

    $this->get($subpage->fresh()->url('en'))
        ->assertOk()
        ->assertViewIs('public::pages.default');
});

test('a module follows the page uri as soon as it is edited', function (): void {
    $page = pageLinkedToTags();
    $originalUrl = $page->url('en');

    $this->get($originalUrl)->assertOk();

    $page->update(['slug' => array_fill_keys(locales(), 'keywords')]);

    $this->get($page->fresh()->url('en'))->assertOk();
    $this->get($originalUrl)->assertNotFound();
});

test('module urls are built from the page', function (): void {
    $page = Page::query()->where('module', 'search')->first();

    if (! $page) {
        $this->markTestSkipped('No page linked to the search module found');
    }

    expect(ModuleUrl::index('search', 'en'))->toBe($page->url('en'));
    expect(ModuleUrl::item('search', 'a-slug', 'en'))->toBe($page->url('en').'/a-slug');
    expect(ModuleUrl::to('search', ['a', 'b'], 'en'))->toBe($page->url('en').'/a/b');
    expect(ModuleUrl::index('no-such-module', 'en'))->toBeNull();
    expect(ModuleUrl::item('search', null, 'en'))->toBeNull();
});
