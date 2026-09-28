<?php

declare(strict_types=1);

use Illuminate\Http\Request;

test('canonicalUrl strips query parameters that are not in the allow-list', function () {
    app()->instance('request', Request::create('https://typi.be/?utm_source=newsletter&foo=bar'));

    expect(canonicalUrl())->toBe('https://typi.be');
});

test('canonicalUrl keeps an allowed query parameter such as page', function () {
    app()->instance('request', Request::create('https://typi.be/projets?page=2'));

    expect(canonicalUrl())->toBe('https://typi.be/projets?page=2');
});

test('canonicalUrl drops page=1 since it is the default', function () {
    app()->instance('request', Request::create('https://typi.be/projets?page=1'));

    expect(canonicalUrl())->toBe('https://typi.be/projets');
});
