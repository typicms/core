<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use TypiCMS\Modules\Core\Models\Page;
use UnexpectedValueException;

class InheritParentPrivacy
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->filled('parent_id')) {
            $parent = Page::query()->find($request->integer('parent_id'));

            if ($parent?->private) {
                $request->merge(['private' => true]);
            }
        }

        $response = $next($request);

        if (! $response instanceof Response) {
            throw new UnexpectedValueException('The next middleware must return a response.');
        }

        return $response;
    }
}
