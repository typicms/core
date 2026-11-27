<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use TypiCMS\Modules\Core\Http\Controllers\TaxonomiesAdminController;
use TypiCMS\Modules\Core\Http\Controllers\TaxonomiesApiController;
use TypiCMS\Modules\Core\Http\Controllers\TermsAdminController;
use TypiCMS\Modules\Core\Http\Controllers\TermsApiController;

/*
 * Admin routes
 */
Route::middleware('admin')
    ->prefix('admin')
    ->name('admin::')
    ->group(function (Router $router): void {
        $router
            ->get('taxonomies', [TaxonomiesAdminController::class, 'index'])
            ->name('index-taxonomies')
            ->middleware('can:read taxonomies');
        $router
            ->get('taxonomies/export', [TaxonomiesAdminController::class, 'export'])
            ->name('export-taxonomies')
            ->middleware('can:read taxonomies');
        $router
            ->get('taxonomies/create', [TaxonomiesAdminController::class, 'create'])
            ->name('create-taxonomy')
            ->middleware('can:create taxonomies');
        $router
            ->get('taxonomies/{taxonomy}/edit', [TaxonomiesAdminController::class, 'edit'])
            ->name('edit-taxonomy')
            ->middleware('can:read taxonomies');
        $router
            ->post('taxonomies', [TaxonomiesAdminController::class, 'store'])
            ->name('store-taxonomy')
            ->middleware('can:create taxonomies');
        $router
            ->put('taxonomies/{taxonomy}', [TaxonomiesAdminController::class, 'update'])
            ->name('update-taxonomy')
            ->middleware('can:update taxonomies');

        $router
            ->get('taxonomies/{taxonomy}/terms', [TermsAdminController::class, 'index'])
            ->name('index-terms')
            ->middleware('can:read terms');
        $router
            ->get('taxonomies/{taxonomy}/terms/create', [TermsAdminController::class, 'create'])
            ->name('create-term')
            ->middleware('can:create terms');
        $router
            ->get('taxonomies/{taxonomy}/terms/{term}/edit', [TermsAdminController::class, 'edit'])
            ->name('edit-term')
            ->middleware('can:read terms');
        $router
            ->post('taxonomies/{taxonomy}/terms', [TermsAdminController::class, 'store'])
            ->name('store-term')
            ->middleware('can:create terms');
        $router
            ->put('taxonomies/{taxonomy}/terms/{term}', [TermsAdminController::class, 'update'])
            ->name('update-term')
            ->middleware('can:update terms');
    });

/*
 * API routes
 */
Route::middleware(['web', 'api', 'auth'])->prefix('api')->group(function (Router $router): void {
    $router->get('taxonomies', [TaxonomiesApiController::class, 'index'])->middleware('can:read taxonomies');
    $router
        ->patch('taxonomies/{taxonomy}', [TaxonomiesApiController::class, 'updatePartial'])
        ->middleware('can:update taxonomies');
    $router
        ->delete('taxonomies/{taxonomy}', [TaxonomiesApiController::class, 'destroy'])
        ->middleware('can:delete taxonomies');
    $router->get('taxonomies/{taxonomy}/terms', [TermsApiController::class, 'index'])->middleware('can:read terms');
    $router
        ->patch('taxonomies/{taxonomy}/terms/{term}', [TermsApiController::class, 'updatePartial'])
        ->middleware('can:update terms');
    $router
        ->delete('taxonomies/{taxonomy}/terms/{term}', [TermsApiController::class, 'destroy'])
        ->middleware('can:delete terms');
});
