<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use TypiCMS\Modules\Core\Http\Requests\TagsFormRequest;
use TypiCMS\Modules\Core\Models\Tag;

final class TagsAdminController extends BaseAdminController
{
    public function index(): View
    {
        return view('admin::tags.index');
    }

    public function create(): View
    {
        $model = new Tag;

        return view('admin::tags.create', ['model' => $model]);
    }

    public function edit(Tag $tag): View
    {
        return view('admin::tags.edit', ['model' => $tag]);
    }

    public function store(TagsFormRequest $request): RedirectResponse
    {
        $tag = Tag::query()->create($request->validated());

        return $this->redirect($request, $tag)->withMessage(__('Item successfully created.'));
    }

    public function update(Tag $tag, TagsFormRequest $request): RedirectResponse
    {
        $tag->update($request->validated());

        return $this->redirect($request, $tag)->withMessage(__('Item successfully updated.'));
    }
}
