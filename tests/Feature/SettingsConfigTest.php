<?php

use TypiCMS\Modules\Core\Models\Setting;
use TypiCMS\Modules\Core\Providers\ModuleServiceProvider;

/*
 * config:cache boots the providers to collect the configuration, so it bakes
 * a copy of the settings into the cached file. The settings read from the
 * database must win over that copy, otherwise a setting edited in the admin
 * panel would do nothing until someone rebuilt the cache.
 */
test('a setting wins over the same key in the configuration', function (): void {
    $settings = new Setting()->allToArray();
    $key = array_key_first($settings);

    expect($key)->not->toBeNull()
        ->and(config('typicms.'.$key))->toBe($settings[$key]);

    config(['typicms' => array_merge(
        config('typicms', []),
        [$key => 'baked into the config cache'],
    )]);

    app()->register(ModuleServiceProvider::class, true);

    expect(config('typicms.'.$key))->toBe($settings[$key]);
});
