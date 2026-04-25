<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use TypiCMS\Modules\Core\Http\Requests\TaxonomyFormRequest;
use TypiCMS\Modules\Core\Models\Taxonomy;

final class TaxonomiesAdminController extends BaseAdminController
{
    public function index(): View
    {
        return view('admin::taxonomies.index');
    }

    public function create(): View
    {
        $model = new Taxonomy;
        $modules = array_filter(
            config('typicms.modules'),
            fn (array $item): bool => isset($item['has_taxonomies']) && $item['has_taxonomies'] === true,
        );

        return view('admin::taxonomies.create', ['model' => $model, 'modules' => $modules]);
    }

    public function edit(Taxonomy $taxonomy): View
    {
        $modules = array_filter(
            config('typicms.modules'),
            fn (array $item): bool => isset($item['has_taxonomies']) && $item['has_taxonomies'] === true,
        );

        return view('admin::taxonomies.edit', ['model' => $taxonomy, 'modules' => $modules]);
    }

    public function store(TaxonomyFormRequest $request): RedirectResponse
    {
        $taxonomy = Taxonomy::query()->create($request->validated());

        return $this->redirect($request, $taxonomy)->withMessage(__('Item successfully created.'));
    }

    public function update(Taxonomy $taxonomy, TaxonomyFormRequest $request): RedirectResponse
    {
        $taxonomy->update($request->validated());

        return $this->redirect($request, $taxonomy)->withMessage(__('Item successfully updated.'));
    }
}
