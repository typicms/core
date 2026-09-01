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
     * Rewrite the URI the way the router itself does when it retries a request
     * without its trailing slash, see CompiledRouteCollection: duplicate the
     * request, which drops the cached path, then set the new URI on it.
     *
     * The query string is carried over untouched rather than read back with
     * getQueryString(), which sorts and re-encodes it and would change the
     * full URL the response cache is keyed on.
     *
     * @param  array{page: Page, locale: string, path: string}  $resolved
     */
    private function rewrite(Request $request, array $resolved): Request
    {
        $rewritten = $request->duplicate();

        $query = explode('?', (string) $request->server->get('REQUEST_URI'), 2)[1] ?? null;

        $rewritten->server->set('REQUEST_URI', '/'.$resolved['path'].($query === null ? '' : '?'.$query));
        $rewritten->attributes->set('typicms.module_page', $resolved['page']);

        return $rewritten;
    }
}
