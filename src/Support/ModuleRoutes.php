<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Support;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Route;
use TypiCMS\Modules\Core\Models\Page;

/**
 * Front office routes of the modules linked to a page.
 *
 * The page a module is linked to is editable content, so its URI cannot be
 * baked into the route table. Modules register their routes under a fixed
 * internal prefix instead, and RewriteModuleUri rewrites incoming requests
 * onto it, which leaves the router doing its normal job: verbs, route
 * middleware, form requests and controller attributes all keep working.
 */
class ModuleRoutes
{
    /**
     * The segment that marks an internal module path, never a page URI.
     */
    public const SEGMENT = '_module';

    /**
     * Container key of the pages linked to a module.
     */
    private const PAGES = 'typicms.module_pages';

    /**
     * Register the front office routes of a module, once per locale so that
     * each locale keeps its own URI, as the rest of the front office does.
     */
    public static function group(string $module, Closure $routes): void
    {
        foreach (static::prefixes($module) as $prefix) {
            Route::middleware(['public', 'module'])
                ->prefix($prefix)
                ->group($routes);
        }
    }

    /**
     * The internal path prefix of a module, per locale.
     *
     * @return array<string, string>
     */
    public static function prefixes(string $module): array
    {
        $prefixes = [];

        foreach (locales() as $locale) {
            $prefixes[$locale] = self::localePrefix($locale).static::SEGMENT.'/'.$module;
        }

        return $prefixes;
    }

    /**
     * Resolve a public path to the module page that owns it.
     *
     * @return array{page: Page, locale: string, path: string}|null
     */
    public static function resolve(string $path): ?array
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/')), fn (string $segment): bool => $segment !== ''));

        if ($segments === []) {
            return null;
        }

        $locale = mainLocale();

        if (in_array($segments[0], enabledLocales(), true)) {
            $locale = (string) array_shift($segments);
        } elseif (config('typicms.main_locale_in_url')) {
            // Without its locale the URL is a 404, leave it to VerifyLocalizedUrl.
            return null;
        }

        if ($segments === []) {
            return null;
        }

        $uri = implode('/', $segments);
        $page = self::modulePageFor($uri, $locale);

        if (! $page instanceof Page) {
            return null;
        }

        $pageUri = (string) $page->translate('uri', $locale);
        $remainder = $uri === $pageUri ? '' : mb_substr($uri, mb_strlen($pageUri) + 1);

        // A subpage of the module page owns this URI, it is not a module item.
        if ($remainder !== '' && Page::query()->published()->whereUriIs($uri)->exists()) {
            return null;
        }

        $path = self::localePrefix($locale).static::SEGMENT.'/'.$page->module;

        return [
            'page' => $page,
            'locale' => $locale,
            'path' => $remainder === '' ? $path : $path.'/'.$remainder,
        ];
    }

    /**
     * The pages linked to a module, read once per request.
     *
     * Not the typicms.routes container binding: that one is resolved while
     * routes are registered, long before a page save in the same request
     * could change it.
     *
     * @return Collection<int, Page>
     */
    public static function modulePages(): Collection
    {
        if (! app()->bound(self::PAGES)) {
            app()->instance(self::PAGES, Page::query()->whereNotNull('module')->get());
        }

        return app(self::PAGES);
    }

    /**
     * Forget the pages read for this request, after one of them changed.
     */
    public static function forgetModulePages(): void
    {
        app()->forgetInstance(self::PAGES);
    }

    /**
     * The longest module page whose URI opens the given one.
     */
    private static function modulePageFor(string $uri, string $locale): ?Page
    {
        $match = null;
        $matchedLength = 0;

        foreach (static::modulePages() as $page) {
            if ($page->module === null) {
                continue;
            }

            if (! $page->isPublished($locale)) {
                continue;
            }

            $pageUri = (string) $page->translate('uri', $locale);

            if ($pageUri === '') {
                continue;
            }

            if ($uri !== $pageUri && ! str_starts_with($uri, $pageUri.'/')) {
                continue;
            }

            if (mb_strlen($pageUri) > $matchedLength) {
                $match = $page;
                $matchedLength = mb_strlen($pageUri);
            }
        }

        return $match;
    }

    private static function localePrefix(string $locale): string
    {
        if (mainLocale() !== $locale || config('typicms.main_locale_in_url')) {
            return $locale.'/';
        }

        return '';
    }
}
