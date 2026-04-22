<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use TypiCMS\Modules\Core\Models\Page;

class InheritParentPrivacy
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->filled('parent_id')) {
            $parent = Page::query()->find($request->input('parent_id'));

            if ($parent?->private) {
                $request->merge(['private' => true]);
            }
        }

        return $next($request);
    }
}
