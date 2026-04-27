<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use TypiCMS\Modules\Core\Filters\FilterOr;
use TypiCMS\Modules\Core\Models\Page;
use TypiCMS\Modules\Core\Models\User;
use TypiCMS\NestableCollection;

final class PagesApiController extends BaseApiController
{
    /** @return array{models: NestableCollection<int, Page>, total: int} */
    public function index(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();
        $userPreferences = $user->preferences ?? [];

        $query = Page::query()->selectFields()->orderBy('position');

        $hasSearchFilter = $request->has('filter');

        if ($hasSearchFilter) {
            $queryBuilder = QueryBuilder::for($query)->allowedFilters(
                AllowedFilter::custom('title', new FilterOr),
            );

            $matchingPages = $queryBuilder->get();
            $parentIds = collect();

            // Collect all parent IDs
            foreach ($matchingPages as $page) {
                $currentParentId = $page->parent_id;
                while ($currentParentId !== null) {
                    $parentIds->push($currentParentId);
                    $parent = Page::query()->find($currentParentId);
                    $currentParentId = $parent?->parent_id;
                }
            }

            // Get all matching pages plus their parents
            $allPageIds = $matchingPages->pluck('id')->merge($parentIds)->unique();
            $models = Page::query()
                ->selectFields()
                ->whereIn('id', $allPageIds)
                ->orderBy('position')
                ->get();

            $total = $matchingPages->count();
        } else {
            $models = $query->get();
            $total = $models->count();
        }

        $models = $models
            ->map(function (Model $page) use ($userPreferences, $hasSearchFilter): Model {
                /** @var Page $page */
                $page->data = $page->toArray();
                $page->isLeaf = $page->module !== null;

                if ($hasSearchFilter) {
                    $page->isExpanded = true;
                } else {
                    $page->isExpanded = ! Arr::get($userPreferences, 'pages_'.$page->id.'_collapsed', false);
                }

                return $page;
            })
            ->childrenName('children')
            ->nest();

        return ['models' => $models, 'total' => $total];
    }

    /** @return array<int, array{string, string}> */
    public function linksForEditor(Request $request): array
    {
        $data = Page::query()
            ->select(['id', 'parent_id', 'title'])
            ->order()
            ->get()
            ->nest()
            ->listsFlattened();

        $titleCounts = collect($data)->countBy();
        $titleIndices = [];

        return collect($data)->map(function ($title, $id) use ($titleCounts, &$titleIndices): array {
            $displayTitle = $title;

            if ($titleCounts[$title] > 1) {
                $titleIndices[$title] = ($titleIndices[$title] ?? 0) + 1;
                $displayTitle = sprintf('%s (%d)', $title, $titleIndices[$title]);
            }

            return [$displayTitle, sprintf('{!! page:%s !!}', $id)];
        })->values()->all();
    }

    public function sort(Request $request): void
    {
        $data = $request->only('moved', 'item');
        foreach ($data['item'] as $position => $item) {
            /** @var Page|null $page */
            $page = Page::query()->find($item['id']);
            $page?->update([
                'position' => (int) $position + 1,
                'parent_id' => $item['parent_id'],
                'private' => $item['private'],
            ]);
        }
    }

    public function destroy(Page $page): JsonResponse
    {
        if ($page->isHome()) {
            return response()->json(['message' => 'The home page cannot be deleted.'], 403);
        }

        $parent = $page->parent;

        $page->subpages()->update(['parent_id' => $page->parent_id]);
        $page->delete();

        if ($parent && $parent->redirect && $parent->subpages()->doesntExist()) {
            $parent->update(['redirect' => false]);
        }

        return response()->json(status: 204);
    }

    protected function updatePartial(Page $page, Request $request): void
    {
        foreach ($request->only('status') as $key => $content) {
            if ($page->isTranslatableAttribute($key)) {
                foreach ($content as $lang => $value) {
                    $page->setTranslation($key, $lang, $value);
                }
            } else {
                $page->{$key} = $content;
            }
        }

        $page->save();
    }
}
