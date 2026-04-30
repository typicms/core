<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use TypiCMS\LaravelTranslatableBootForms\TranslatableBootFormsServiceProvider;
use TypiCMS\Modules\Core\Providers\ModuleServiceProvider;
use TypiCMS\Modules\Core\Providers\TranslationsServiceProvider;
use TypiCMS\Modules\Sidebar\SidebarServiceProvider;
use Typidesign\Translations\ArtisanTranslationsServiceProvider;

return [
    // Package Service Providers.
    SidebarServiceProvider::class,
    ArtisanTranslationsServiceProvider::class,
    TranslatableBootFormsServiceProvider::class,
    TranslationsServiceProvider::class,

    // TypiCMS Core Service Provider.
    ModuleServiceProvider::class,

    // TypiCMS Modules Service Providers.
    TypiCMS\Modules\News\Providers\ModuleServiceProvider::class,
    TypiCMS\Modules\Places\Providers\ModuleServiceProvider::class,
    TypiCMS\Modules\Partners\Providers\ModuleServiceProvider::class,
    TypiCMS\Modules\Events\Providers\ModuleServiceProvider::class,
    TypiCMS\Modules\Projects\Providers\ModuleServiceProvider::class,
    TypiCMS\Modules\Slides\Providers\ModuleServiceProvider::class,
    TypiCMS\Modules\Contacts\Providers\ModuleServiceProvider::class,

    // Application Service Providers.
    AppServiceProvider::class,
];
