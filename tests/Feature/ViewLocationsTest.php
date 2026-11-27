<?php

use Illuminate\Support\Facades\View;

/*
 * view:cache, which artisan optimize runs, hands every view location to
 * Symfony Finder, which throws on a folder that does not exist.
 */
test('views can be cached', function (): void {
    $this->artisan('view:cache')->assertSuccessful();

    $this->artisan('view:clear');
});

test('views are resolved from the packages', function (): void {
    expect(View::exists('admin.core._sidebar'))->toBeTrue()
        ->and(View::exists('public.news.index'))->toBeTrue()
        ->and(View::exists('mail.contacts.new-contact-request'))->toBeTrue();
});
