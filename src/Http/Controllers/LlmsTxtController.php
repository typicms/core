<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Spatie\MarkdownResponse\Attributes\DoNotProvideMarkdown;
use TypiCMS\Modules\Core\Models\Menu;
use TypiCMS\Modules\Core\Models\Menulink;
use TypiCMS\Modules\Core\Models\Page;

final class LlmsTxtController extends Controller
{
    #[DoNotProvideMarkdown]
    public function __invoke(): Response
    {
        $content = Cache::remember('llms-txt', 3600, fn (): string => $this->generateContent());

        return response($content, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
        ]);
    }

    private function generateContent(): string
    {
        $lines = [];

        $lines[] = '# '.websiteTitle();
        $lines[] = '';

        foreach (enabledLocales() as $locale) {
            $lines[] = '## '.mb_strtoupper($locale);
            $lines[] = '';

            $lines[] = '### Key Pages';
            $lines[] = '';

            $sectionPages = $this->getSectionPages($locale);
            foreach ($sectionPages as $label => $url) {
                $lines[] = "- [{$label}]({$url})";
            }

            $lines[] = '';

            $lines = $this->addLatestModuleItems($lines, $locale);
        }

        return implode("\n", $lines);
    }

    /** @return array<string, string> */
    private function getSectionPages(string $locale): array
    {
        $pages = [];

        /** @var Page|null $homePage */
        $homePage = Page::query()
            ->published()
            ->where('is_home', true)
            ->first();

        if ($homePage) {
            $pages['Home'] = $homePage->url($locale);
        }

        $menuNames = (array) config('typicms.llms_txt.menus', []);
        $menus = Menu::query()
            ->published()
            ->whereIn('name', $menuNames)
            ->get();

        foreach ($menus as $menu) {
            $menulinks = Menulink::query()
                ->where('menu_id', $menu->id)
                ->whereNull('parent_id')
                ->published()
                ->orderBy('position')
                ->with('page')
                ->get();

            foreach ($menulinks as $menulink) {
                $page = $menulink->page;
                if ($page instanceof Page && $page->isPublished($locale)) {
                    $title = $page->translate('title', $locale);
                    if ($title) {
                        $pages[$title] = $page->url($locale);
                    }
                }
            }
        }

        return $pages;
    }

    /**
     * @param  array<int, string>  $lines
     * @return array<int, string>
     */
    private function addLatestModuleItems(array $lines, string $locale): array
    {
        foreach ((array) config('typicms.modules') as $moduleName => $properties) {
            if (! ($properties['llms_txt'] ?? false)) {
                continue;
            }

            $modelClass = $properties['model'] ?? null;
            if (! is_string($modelClass)) {
                continue;
            }

            if (! is_subclass_of($modelClass, Model::class)) {
                continue;
            }

            $page = getPageLinkedToModule($moduleName);
            if (! $page instanceof Page) {
                continue;
            }

            if (! $page->isPublished($locale)) {
                continue;
            }

            $items = $modelClass::query()
                ->published()
                ->latest()
                ->take(20)
                ->get();

            if ($items->isEmpty()) {
                continue;
            }

            $lines[] = "### {$page->translate('title', $locale)}";
            $lines[] = '';

            foreach ($items as $item) {
                if (! $item->isPublished($locale)) {
                    continue;
                }

                $url = $item->url($locale);
                $title = $this->getTitle($item, $locale);

                if ($url && $title) {
                    $lines[] = "- [{$title}]({$url})";
                }
            }

            $lines[] = '';
        }

        return $lines;
    }

    private function getTitle(Model $item, string $locale): ?string
    {
        if (method_exists($item, 'isTranslatableAttribute') && $item->isTranslatableAttribute('title')) {
            $title = $item->translate('title', $locale);
        } else {
            $title = $item->title ?? null;
        }

        return is_string($title) && $title !== '' ? $title : null;
    }
}
