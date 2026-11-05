<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\TranslationServiceProvider as LaravelTranslationServiceProvider;
use Override;
use TypiCMS\Modules\Core\Loaders\MixedLoader;

class TranslationsServiceProvider extends LaravelTranslationServiceProvider
{
    /**
     * Register the translation line loader.
     */
    #[Override]
    protected function registerLoader()
    {
        $this->app->singleton(
            'translation.loader',
            fn (Application $app): MixedLoader => new MixedLoader($app->make(Filesystem::class), $app->langPath()),
        );
    }
}
