<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use TypiCMS\Modules\Core\Http\Controllers\SearchPublicController;
use TypiCMS\Modules\Core\Support\ModuleRoutes;

/*
 * Front office routes
 */
ModuleRoutes::group('search', function (Router $router): void {
    $router->get('/', [SearchPublicController::class, 'search']);
});
