<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use TypiCMS\Modules\Core\Http\Requests\PageFormRequest;
use TypiCMS\Modules\Core\Models\Page;

final class PagesAdminController extends BaseAdminController
{
    public function index(): View
    {
        return view('admin::pages.index');
    }

    public function create(): View
    {
        $model = new Page;

        return view('admin::pages.create', ['model' => $model]);
    }

    public function edit(Page $page): View
    {
        return view('admin::pages.edit', ['model' => $page]);
    }

    public function store(PageFormRequest $request): RedirectResponse
    {
        $page = Page::query()->create($request->validated());

        return $this->redirect($request, $page)->withMessage(__('Item successfully created.'));
    }

    public function update(Page $page, PageFormRequest $request): RedirectResponse
    {
        $page->update($request->validated());

        return $this->redirect($request, $page)->withMessage(__('Item successfully updated.'));
    }

    public function notFound(): never
    {
        abort(404);
    }
}
