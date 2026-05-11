<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetContentLocale
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Store requested locale in session.
        $localeFromRequest = (string) $request->string('locale');

        if (in_array($localeFromRequest, locales(), true)) {
            session(['content_locale' => $localeFromRequest]);
        }

        // Set content locale.
        $contentLocale = session('content_locale', mainLocale());
        if (in_array($contentLocale, locales(), true)) {
            config(['typicms.content_locale' => $contentLocale]);
        }

        return $next($request);
    }
}
