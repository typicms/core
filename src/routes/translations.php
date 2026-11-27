<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use TypiCMS\Modules\Core\Http\Controllers\TranslationsAdminController;
use TypiCMS\Modules\Core\Http\Controllers\TranslationsApiController;

/*
 * Admin routes
 */
Route::middleware('admin')
    ->prefix('admin')
    ->name('admin::')
    ->group(function (Router $router): void {
        $router
            ->get('translations', [TranslationsAdminController::class, 'index'])
            ->name('index-translations')
            ->middleware('can:read translations');
        $router
            ->get('translations/create', [TranslationsAdminController::class, 'create'])
            ->name('create-translation')
            ->middleware('can:create translations');
        $router
            ->get('translations/{translation}/edit', [TranslationsAdminController::class, 'edit'])
            ->name('edit-translation')
            ->middleware('can:read translations');
        $router
            ->post('translations', [TranslationsAdminController::class, 'store'])
            ->name('store-translation')
            ->middleware('can:create translations');
        $router
            ->put('translations/{translation}', [TranslationsAdminController::class, 'update'])
            ->name('update-translation')
            ->middleware('can:update translations');
    });

/*
 * API routes
 */
Route::middleware(['web', 'api', 'auth'])->prefix('api')->group(function (Router $router): void {
    $router->get('translations', [TranslationsApiController::class, 'index'])->middleware('can:read translations');
    $router
        ->patch('translations/{translation}', [TranslationsApiController::class, 'updatePartial'])
        ->middleware('can:update translations');
    $router
        ->delete('translations/{translation}', [TranslationsApiController::class, 'destroy'])
        ->middleware('can:delete translations');
});
