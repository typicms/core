<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocaleFromUser
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $userLocale = auth()->user()?->locale;
        if (in_array($userLocale, adminLocales(), true)) {
            app()->setLocale($userLocale);
        }

        return $next($request);
    }
}
