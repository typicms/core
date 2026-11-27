<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use TypiCMS\Modules\Core\Models\User;
use TypiCMS\Modules\Core\Services\FileUploader;

class JavaScriptData
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $data = [
            'content_locale' => config('typicms.content_locale'),
            'locale' => app()->getLocale(),
            'locale_region' => localeAndRegion('-'),
            'max_file_upload_size' => config('typicms.max_file_upload_size'),
            'allowed_file_extensions' => FileUploader::SAFE_EXTENSIONS,
            'compressor_js_configuration' => config('typicms.compressor_js_configuration'),
        ];
        foreach (locales() as $locale) {
            $data['locales'][] = [
                'short' => $locale,
                'long' => trans('languages.'.$locale),
            ];
        }

        if (auth()->check()) {
            /** @var User $user */
            $user = $request->user();
            $data['permissions'] = $user->all_permissions;
        }

        resolve('JavaScript')->put($data);

        return $next($request);
    }
}
