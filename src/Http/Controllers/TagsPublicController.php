<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\View\View;
use TypiCMS\Modules\Core\Models\Tag;

final class TagsPublicController extends BasePublicController
{
    public function index(): View
    {
        $perPage = config('typicms.modules.tags.per_page');
        $models = Tag::query()->paginate($perPage);

        return view('public::tags.index', ['models' => $models]);
    }

    public function show(string $slug): View
    {
        $model = Tag::query()->where('slug', $slug)->firstOrFail();

        return view('public::tags.show', ['model' => $model]);
    }
}
