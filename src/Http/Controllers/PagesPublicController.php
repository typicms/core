<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use TypiCMS\Modules\Core\Models\Page;

final class PagesPublicController extends BasePublicController
{
    public function uri(?string $uri = null): RedirectResponse|View
    {
        if ($this->isLocaleOnlyUri($uri)) {
            $homepage = $this->findHomepage();

            if ($homepage->url() !== request()->url()) {
                return redirect($homepage->url());
            }

            return $this->renderPage($homepage);
        }

        $page = $this->findPageByUri($uri);

        if ($page->private && ! Auth::check()) {
            return redirect()->guest(route(app()->getLocale().'::login'));
        }

        if ($page->redirect && $page->publishedSubpages->count() > 0) {
            $child = $page->publishedSubpages->first();

            if ($child instanceof Page) {
                return redirect()->to($child->url());
            }
        }

        return $this->renderPage($page);
    }

    public function redirectToHomepage(Request $request): RedirectResponse
    {
        $homepage = $this->findHomepage();
        $locale = $request->getPreferredLanguage(enabledLocales());

        return redirect($homepage->url($locale));
    }

    public function langChooser(): View
    {
        $homepage = $this->findHomepage();

        if (! $homepage) {
            Log::error('No homepage found.');
            abort(404);
        }

        return view('public::core.lang-chooser', [
            'homepage' => $homepage,
            'locales' => enabledLocales(),
        ]);
    }

    private function findPageByUri(?string $uri): Page
    {
        return $this->pageQuery()
            ->when($uri === null, fn ($query) => $query->where('is_home', 1))
            ->when($uri !== null, fn ($query) => $query->whereUriIs($uri))
            ->firstOrFail();
    }

    private function findHomepage(): Page
    {
        return $this->pageQuery()
            ->where('is_home', 1)
            ->firstOrFail();
    }

    private function pageQuery()
    {
        return Page::query()
            ->published()
            ->with([
                'image',
                'images',
                'documents',
                'publishedSections.image',
                'publishedSections.images',
                'publishedSections.documents',
            ]);
    }

    private function isLocaleOnlyUri(?string $uri): bool
    {
        return $uri !== null
            && in_array($uri, enabledLocales(), true)
            && (mainLocale() !== $uri || config('typicms.main_locale_in_url'));
    }

    private function renderPage(Page $page): View
    {
        $templateDir = config('typicms.template_dir', 'public').'::pages.';
        $template = $page->template ?: 'default';

        if (! view()->exists($templateDir.$template)) {
            info("Template {$template} not found, switching to default template.");
            $template = 'default';
        }

        return view($templateDir.$template, ['page' => $page, 'templateDir' => $templateDir]);
    }
}
