<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use TypiCMS\Modules\Core\Http\Controllers\RolesAdminController;
use TypiCMS\Modules\Core\Http\Controllers\RolesApiController;

/*
 * Admin routes
 */
Route::middleware('admin')
    ->prefix('admin')
    ->name('admin::')
    ->group(function (Router $router): void {
        $router
            ->get('roles', [RolesAdminController::class, 'index'])
            ->name('index-roles')
            ->middleware('can:read roles');
        $router
            ->get('roles/create', [RolesAdminController::class, 'create'])
            ->name('create-role')
            ->middleware('can:create roles');
        $router
            ->get('roles/{role}/edit', [RolesAdminController::class, 'edit'])
            ->name('edit-role')
            ->middleware('can:read roles');
        $router
            ->post('roles', [RolesAdminController::class, 'store'])
            ->name('store-role')
            ->middleware('can:create roles');
        $router
            ->put('roles/{role}', [RolesAdminController::class, 'update'])
            ->name('update-role')
            ->middleware('can:update roles');
    });

/*
 * API routes
 */
Route::middleware(['web', 'api', 'auth'])->prefix('api')->group(function (Router $router): void {
    $router->get('roles', [RolesApiController::class, 'index'])->middleware('can:read roles');
    $router->patch('roles/{role}', [RolesApiController::class, 'updatePartial'])->middleware('can:update roles');
    $router->delete('roles/{role}', [RolesApiController::class, 'destroy'])->middleware('can:delete roles');
});
