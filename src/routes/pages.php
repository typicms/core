<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use TypiCMS\Modules\Core\Http\Controllers\PagesAdminController;
use TypiCMS\Modules\Core\Http\Controllers\PagesApiController;
use TypiCMS\Modules\Core\Http\Controllers\PageSectionsAdminController;
use TypiCMS\Modules\Core\Http\Controllers\PageSectionsApiController;
use TypiCMS\Modules\Core\Http\Controllers\PagesPublicController;
use TypiCMS\Modules\Core\Http\Middleware\InheritParentPrivacy;

/*
 * Admin routes
 */
Route::middleware('admin')
    ->prefix('admin')
    ->name('admin::')
    ->group(function (Router $router): void {
        $router
            ->get('pages', [PagesAdminController::class, 'index'])
            ->name('index-pages')
            ->middleware('can:read pages');
        $router
            ->get('pages/create', [PagesAdminController::class, 'create'])
            ->name('create-page')
            ->middleware('can:create pages');
        $router
            ->get('pages/{page}/edit', [PagesAdminController::class, 'edit'])
            ->name('edit-page')
            ->middleware('can:read pages');
        $router
            ->post('pages', [PagesAdminController::class, 'store'])
            ->name('store-page')
            ->middleware(['can:create pages', InheritParentPrivacy::class]);
        $router
            ->put('pages/{page}', [PagesAdminController::class, 'update'])
            ->name('update-page')
            ->middleware('can:update pages');

        $router
            ->get('pages/{page}/sections/create', [PageSectionsAdminController::class, 'create'])
            ->name('create-page_section')
            ->middleware('can:create page_sections');
        $router
            ->get('pages/{page}/sections/{section}/edit', [PageSectionsAdminController::class, 'edit'])
            ->name('edit-page_section')
            ->middleware('can:read page_sections');
        $router
            ->post('pages/{page}/sections', [PageSectionsAdminController::class, 'store'])
            ->name('store-page_section')
            ->middleware('can:create page_sections');
        $router
            ->put('pages/{page}/sections/{section}', [PageSectionsAdminController::class, 'update'])
            ->name('update-page_section')
            ->middleware('can:update page_sections');
        $router
            ->post('pages/{page}/sections/sort', [PageSectionsAdminController::class, 'sort'])
            ->name('sort-page_sections');

        $router
            ->get('sections', [PageSectionsAdminController::class, 'index'])
            ->name('index-page_sections')
            ->middleware('can:read page_sections');
        $router
            ->delete('sections/{section}', [PageSectionsAdminController::class, 'destroyMultiple'])
            ->name('destroy-page_section')
            ->middleware('can:delete page_sections');

        $router->get('{uri}', [PagesAdminController::class, 'notFound'])->name('show-404-page-in-admin')->where(
            'uri',
            '(.*)',
        );
    });

/*
 * API routes
 */
Route::middleware(['api', 'auth:api'])->prefix('api')->group(function (Router $router): void {
    $router->get('pages', [PagesApiController::class, 'index'])->middleware('can:read pages');
    $router->get('pages/links-for-editor', [PagesApiController::class, 'linksForEditor'])->middleware('can:read pages');
    $router->patch('pages/{page}', [PagesApiController::class, 'updatePartial'])->middleware('can:update pages');
    $router->post('pages/sort', [PagesApiController::class, 'sort'])->middleware('can:update pages');
    $router->delete('pages/{page}', [PagesApiController::class, 'destroy'])->middleware('can:delete pages');
    $router->get('pages/{page}/sections', [PageSectionsApiController::class, 'index'])->middleware(
        'can:read page_sections',
    );
    $router
        ->patch('pages/{page}/sections/{section}', [PageSectionsApiController::class, 'updatePartial'])
        ->middleware('can:update page_sections');
    $router
        ->delete('pages/{page}/sections/{section}', [PageSectionsApiController::class, 'destroy'])
        ->middleware('can:delete page_sections');
});

/*
 * Front office routes
 */
Route::middleware('public')->group(function (Router $router): void {
    if (config('typicms.main_locale_in_url')) {
        if (config('typicms.lang_chooser')) {
            $router->get('/', [PagesPublicController::class, 'langChooser']);
        } else {
            $router->get('/', [PagesPublicController::class, 'redirectToHomepage']);
        }
    }

    foreach (locales() as $locale) {
        if (mainLocale() !== $locale || config('typicms.main_locale_in_url')) {
            $router->prefix($locale)->get('{uri}', [PagesPublicController::class, 'uri'])->where('uri', '(.*)');
        }
    }

    $router->get('{uri}', [PagesPublicController::class, 'uri'])->where('uri', '(.*)');
});
