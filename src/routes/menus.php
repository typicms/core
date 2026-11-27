<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use TypiCMS\Modules\Core\Http\Controllers\MenulinksAdminController;
use TypiCMS\Modules\Core\Http\Controllers\MenulinksApiController;
use TypiCMS\Modules\Core\Http\Controllers\MenusAdminController;
use TypiCMS\Modules\Core\Http\Controllers\MenusApiController;

/*
 * Admin routes
 */
Route::middleware('admin')
    ->prefix('admin')
    ->name('admin::')
    ->group(function (Router $router): void {
        $router
            ->get('menus', [MenusAdminController::class, 'index'])
            ->name('index-menus')
            ->middleware('can:read menus');
        $router
            ->get('menus/create', [MenusAdminController::class, 'create'])
            ->name('create-menu')
            ->middleware('can:create menus');
        $router
            ->get('menus/{menu}/edit', [MenusAdminController::class, 'edit'])
            ->name('edit-menu')
            ->middleware('can:read menus');
        $router
            ->post('menus', [MenusAdminController::class, 'store'])
            ->name('store-menu')
            ->middleware('can:create menus');
        $router
            ->put('menus/{menu}', [MenusAdminController::class, 'update'])
            ->name('update-menu')
            ->middleware('can:update menus');

        $router
            ->get('menus/{menu}/menulinks/create', [MenulinksAdminController::class, 'create'])
            ->name('create-menulink')
            ->middleware('can:create menulinks');
        $router
            ->get('menus/{menu}/menulinks/{menulink}/edit', [MenulinksAdminController::class, 'edit'])
            ->name('edit-menulink')
            ->middleware('can:read menulinks');
        $router
            ->post('menus/{menu}/menulinks', [MenulinksAdminController::class, 'store'])
            ->name('store-menulink')
            ->middleware('can:create menulinks');
        $router
            ->put('menus/{menu}/menulinks/{menulink}', [MenulinksAdminController::class, 'update'])
            ->name('update-menulink')
            ->middleware('can:update menulinks');
    });

/*
 * API routes
 */
Route::middleware(['web', 'api', 'auth'])->prefix('api')->group(function (Router $router): void {
    $router->get('menus', [MenusApiController::class, 'index'])->middleware('can:read menus');
    $router->patch('menus/{menu}', [MenusApiController::class, 'updatePartial'])->middleware('can:update menus');
    $router->delete('menus/{menu}', [MenusApiController::class, 'destroy'])->middleware('can:delete menus');

    $router->get('menus/{menu}/menulinks', [MenulinksApiController::class, 'index'])->middleware('can:read menulinks');
    $router
        ->patch('menus/{menu}/menulinks/{menulink}', [MenulinksApiController::class, 'updatePartial'])
        ->middleware('can:update menulinks');
    $router
        ->post('menus/{menu}/menulinks/sort', [MenulinksApiController::class, 'sort'])
        ->middleware('can:update menulinks');
    $router
        ->delete('menus/{menu}/menulinks/{menulink}', [MenulinksApiController::class, 'destroy'])
        ->middleware('can:delete menulinks');
});
