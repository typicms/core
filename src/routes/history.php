<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use TypiCMS\Modules\Core\Http\Controllers\HistoryApiController;

/*
 * API routes
 */
Route::middleware(['web', 'api', 'auth'])->prefix('api')->group(function (Router $router): void {
    $router->get('history', [HistoryApiController::class, 'index'])->middleware('can:see history');
    $router->delete('history', [HistoryApiController::class, 'destroy'])->middleware('can:clear history');
});
