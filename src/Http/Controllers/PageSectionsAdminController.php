<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use TypiCMS\Modules\Core\Http\Requests\PageSectionFormRequest;
use TypiCMS\Modules\Core\Models\Page;
use TypiCMS\Modules\Core\Models\PageSection;

final class PageSectionsAdminController extends BaseAdminController
{
    public function create(Page $page): View
    {
        $model = new PageSection;

        return view('admin::pages.create-section', ['model' => $model, 'page' => $page]);
    }

    public function edit(Page $page, PageSection $section): View
    {
        return view('admin::pages.edit-section', [
            'model' => $section,
            'page' => $page,
        ]);
    }

    public function store(Page $page, PageSectionFormRequest $request): RedirectResponse
    {
        $section = PageSection::query()->create($request->validated());

        return $this->redirect($request, $section)->withMessage(__('Item successfully created.'));
    }

    public function update(Page $page, PageSection $section, PageSectionFormRequest $request): RedirectResponse
    {
        $section->update($request->validated());

        return $this->redirect($request, $section)->withMessage(__('Item successfully updated.'));
    }
}
