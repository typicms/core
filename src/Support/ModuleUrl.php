<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Support;

use TypiCMS\Modules\Core\Models\Page;

/**
 * Front office URLs of the modules linked to a page.
 *
 * Module URLs are not named routes: the page a module is linked to is editable
 * content, so its URI is resolved at request time by PagesPublicController
 * instead of being frozen into the route table. This builds the matching URLs.
 */
class ModuleUrl
{
    /**
     * URL of the page a module is linked to, null when the module is not
     * linked to a page published in this locale.
     */
    public static function index(string $module, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return static::page($module, $locale)?->url($locale);
    }

    /**
     * URL of a single item of a module, for example a tag.
     */
    public static function item(string $module, ?string $slug, ?string $locale = null): ?string
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        return static::to($module, [$slug], $locale);
    }

    /**
     * URL of a path below the page a module is linked to, for example the
     * category and the slug of a project.
     *
     * @param  array<int, string>  $segments
     */
    public static function to(string $module, array $segments = [], ?string $locale = null): ?string
    {
        $index = static::index($module, $locale);

        if ($index === null) {
            return null;
        }

        $segments = array_filter($segments, fn (string $segment): bool => $segment !== '');

        if ($segments === []) {
            return $index;
        }

        return rtrim($index, '/').'/'.implode('/', $segments);
    }

    /**
     * The published page a module is linked to.
     */
    public static function page(string $module, ?string $locale = null): ?Page
    {
        $locale ??= app()->getLocale();

        $page = ModuleRoutes::modulePages()->firstWhere('module', $module);

        if (! $page instanceof Page) {
            return null;
        }

        if (! $page->isPublished($locale)) {
            return null;
        }

        if ($page->path($locale) === '') {
            return null;
        }

        return $page;
    }
}
