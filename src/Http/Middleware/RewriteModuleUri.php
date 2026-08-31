<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use TypiCMS\Modules\Core\Models\Page;
use TypiCMS\Modules\Core\Support\ModuleRoutes;

/**
 * Rewrite a request that lands on a page linked to a module onto the internal
 * path that module registered its routes under.
 *
 * This runs before routing, so the router still matches a real route: the
 * module keeps its verbs, its route middleware, its form requests and its
 * controller attributes. Only the URI is editable content.
 */
class RewriteModuleUri
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->isBackOffice($request)) {
            return $next($request);
        }

        $resolved = ModuleRoutes::resolve($request->path());

        if ($resolved === null) {
            return $next($request);
        }

        return $next($this->rewrite($request, $resolved));
    }

    private function isBackOffice(Request $request): bool
    {
        return in_array($request->segment(1), ['admin', 'api'], true);
    }

    /**
     * @param  array{page: Page, locale: string, path: string}  $resolved
     */
    private function rewrite(Request $request, array $resolved): Request
    {
        $server = $request->server->all();
        $queryString = $request->getQueryString();

        $server['REQUEST_URI'] = '/'.$resolved['path'].($queryString === null ? '' : '?'.$queryString);

        $rewritten = $request->duplicate(null, null, null, null, null, $server);
        $rewritten->attributes->set('typicms.module_page', $resolved['page']);

        return $rewritten;
    }
}
