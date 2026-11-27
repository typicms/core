<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use TypiCMS\Modules\Core\Http\Controllers\TagsAdminController;
use TypiCMS\Modules\Core\Http\Controllers\TagsApiController;
use TypiCMS\Modules\Core\Http\Controllers\TagsPublicController;
use TypiCMS\Modules\Core\Support\ModuleRoutes;

/*
 * Front office routes
 */
ModuleRoutes::group('tags', function (Router $router): void {
    $router->get('/', [TagsPublicController::class, 'index']);
    $router->get('{slug}', [TagsPublicController::class, 'show']);
});

/*
 * Admin routes
 */
Route::middleware('admin')
    ->prefix('admin')
    ->name('admin::')
    ->group(function (Router $router): void {
        $router->get('tags', [TagsAdminController::class, 'index'])->name('index-tags')->middleware('can:read tags');
        $router
            ->get('tags/create', [TagsAdminController::class, 'create'])
            ->name('create-tag')
            ->middleware('can:create tags');
        $router
            ->get('tags/{tag}/edit', [TagsAdminController::class, 'edit'])
            ->name('edit-tag')
            ->middleware('can:read tags');
        $router->post('tags', [TagsAdminController::class, 'store'])->name('store-tag')->middleware('can:create tags');
        $router
            ->put('tags/{tag}', [TagsAdminController::class, 'update'])
            ->name('update-tag')
            ->middleware('can:update tags');
    });

/*
 * API routes
 */
Route::middleware('api')
    ->prefix('api')
    ->group(function (Router $router): void {
        $router
            ->middleware('auth:api')
            ->group(function (Router $router): void {
                $router->get('tags-list', [TagsApiController::class, 'tagsList']);
                $router->get('tags', [TagsApiController::class, 'index'])->middleware('can:read tags');
                $router
                    ->patch('tags/{tag}', [TagsApiController::class, 'updatePartial'])
                    ->middleware('can:update tags');
                $router->delete('tags/{tag}', [TagsApiController::class, 'destroy'])->middleware('can:delete tags');
            });
    });
