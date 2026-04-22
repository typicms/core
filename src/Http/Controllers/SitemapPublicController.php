<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use TypiCMS\Modules\Core\Models\Page;

final class SitemapPublicController extends Controller
{
    public function generate(): Response
    {
        // create new sitemap object
        $sitemap = resolve('sitemap');

        // set cache (key (string), duration in minutes (Carbon|Datetime|int), turn on/off (boolean))
        // by default cache is disabled
        if (config('typicms.cache')) {
            $sitemap->setCache('laravel.sitemap', 3600);
        }

        // check if there is cached sitemap and build new only if is not
        if (! $sitemap->isCached()) {
            foreach (enabledLocales() as $locale) {
                app()->setLocale($locale);

                /** @var Collection<int, Page> $pages */
                $pages = Page::query()
                    ->published()
                    ->where('private', 0)
                    ->get();

                foreach ($pages as $page) {
                    $url = $page->url($locale);
                    if ($url) {
                        $sitemap->add($url, $page->updated_at?->toDateTimeString());
                    }

                    $modelClass = config("typicms.modules.{$page->module}.model");
                    if (! is_string($modelClass)) {
                        continue;
                    }

                    if (! is_subclass_of($modelClass, Model::class)) {
                        continue;
                    }

                    foreach ($modelClass::published()->get() as $item) {
                        $url = $item->url($locale);
                        if ($url) {
                            $sitemap->add($url, $item->updated_at?->toDateTimeString());
                        }
                    }
                }
            }
        }

        // show your sitemap (options: 'xml' (default), 'html', 'txt', 'ror-rss', 'ror-rdf')
        return $sitemap->render('xml');
    }
}
