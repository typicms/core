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

describe('homepage', function (): void {
    test('redirects to default locale', function (): void {
        $homepage = Page::query()->where('is_home', 1)->firstOrFail();

        $this->get('/')->assertRedirect($homepage->url(mainLocale()));
    });

    test('returns 200', function (): void {
        $homepage = Page::query()->where('is_home', 1)->firstOrFail();

        $this->get($homepage->url('en'))->assertOk();
    });

    test('is available in all locales', function (string $locale): void {
        $homepage = Page::query()->where('is_home', 1)->firstOrFail();

        $this->get($homepage->url($locale))->assertOk();
    })->with(['en', 'fr', 'nl']);
});

test('login page returns 200', function (): void {
    $this->get('/en/login')->assertOk();
});

test('search page returns 200', function (): void {
    $page = Page::query()
        ->where('module', 'search')
        ->first();

    if (! $page) {
        $this->markTestSkipped('No page linked to the search module found');
    }

    $this->get($page->url('en'))->assertOk();
});

test('sitemap returns 200', function (): void {
    $this->get('/sitemap.xml')->assertOk();
});

test('admin redirects to login for guests', function (): void {
    $this->get('/admin')->assertRedirect();
});

test('admin dashboard redirects to login for guests', function (): void {
    $this->get('/admin/dashboard')->assertRedirect();
});

test('page returns 200', function (): void {
    $page = Page::query()
        ->published()
        ->whereNotNull('slug->en')
        ->first();

    $this->assertNotNull($page, 'No published page with English slug found');
    $this->get($page->url('en'))->assertOk();
});

describe('lang switcher', function (): void {
    test('on a page links to the same page in another language', function (): void {
        $page = Page::query()
            ->published()
            ->whereNotNull('slug->en')
            ->whereNotNull('slug->fr')
            ->firstOrFail();

        $enUrl = $page->url('en');

        $this->assertNotNull($enUrl, 'Page must have an English URL');

        $response = $this->get($enUrl);
        $response->assertOk();

        $frUrl = $page->url('fr');

        $this->assertNotNull($frUrl, 'Page must have a French URL');

        $response->assertSee('hreflang="fr"', false);
        $response->assertSee($frUrl, false);
    });
});
