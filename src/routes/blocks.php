<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use TypiCMS\Modules\Core\Http\Controllers\AdminController;
use TypiCMS\Modules\Core\Http\Controllers\ApiController;

/*
 * Admin routes
 */
Route::middleware('admin')
    ->prefix('admin')
    ->name('admin::')
    ->group(function (Router $router): void {
        $router->get('blocks', [AdminController::class, 'index'])->name('index-blocks')->middleware('can:read blocks');
        $router
            ->get('blocks/create', [AdminController::class, 'create'])
            ->name('create-block')
            ->middleware('can:create blocks');
        $router
            ->get('blocks/{block}/edit', [AdminController::class, 'edit'])
            ->name('edit-block')
            ->middleware('can:read blocks');
        $router
            ->post('blocks', [AdminController::class, 'store'])
            ->name('store-block')
            ->middleware('can:create blocks');
        $router
            ->put('blocks/{block}', [AdminController::class, 'update'])
            ->name('update-block')
            ->middleware('can:update blocks');
    });

/*
 * API routes
 */
Route::middleware(['web', 'api', 'auth'])->prefix('api')->group(function (Router $router): void {
    $router->get('blocks', [ApiController::class, 'index'])->middleware('can:read blocks');
    $router->patch('blocks/{block}', [ApiController::class, 'updatePartial'])->middleware('can:update blocks');
    $router->delete('blocks/{block}', [ApiController::class, 'destroy'])->middleware('can:delete blocks');
});
