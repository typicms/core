<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use TypiCMS\Modules\Core\Models\Page;

/**
 * Guard the internal routes of a module.
 *
 * They are only reachable through RewriteModuleUri, which resolved the page
 * the module is linked to. That page carries the privacy of the whole module
 * and is shared with the views, so a module never has to look it up.
 */
class ModulePage
{
    public function handle(Request $request, Closure $next): mixed
    {
        $page = $request->attributes->get('typicms.module_page');

        if (! $page instanceof Page) {
            abort(404);
        }

        if ($page->private && ! Auth::check()) {
            return redirect()->guest(route(app()->getLocale().'::login'));
        }

        view()->share('page', $page);

        return $next($request);
    }
}
