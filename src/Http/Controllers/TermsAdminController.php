<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use TypiCMS\Modules\Core\Http\Requests\TermFormRequest;
use TypiCMS\Modules\Core\Models\Taxonomy;
use TypiCMS\Modules\Core\Models\Term;

final class TermsAdminController extends BaseAdminController
{
    public function index(Taxonomy $taxonomy): View
    {
        return view('admin::taxonomies.index-terms', ['taxonomy' => $taxonomy]);
    }

    public function create(Taxonomy $taxonomy): View
    {
        $model = new Term;

        return view('admin::taxonomies.create-term', ['model' => $model, 'taxonomy' => $taxonomy]);
    }

    public function edit(Taxonomy $taxonomy, Term $term): View
    {
        return view('admin::taxonomies.edit-term', [
            'model' => $term,
            'taxonomy' => $taxonomy,
        ]);
    }

    public function store(Taxonomy $taxonomy, TermFormRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['taxonomy_id'] = $taxonomy->id;
        $term = Term::query()->create($data);

        return $this->redirect($request, $term);
    }

    public function update(Taxonomy $taxonomy, Term $term, TermFormRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['taxonomy_id'] = $taxonomy->id;
        $term->update($data);

        return $this->redirect($request, $term);
    }
}
