<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use TypiCMS\Modules\Core\Filters\FilterOr;
use TypiCMS\Modules\Core\Models\Tag;

final class TagsApiController extends BaseApiController
{
    /** @return LengthAwarePaginator<int, Tag> */
    public function index(Request $request): LengthAwarePaginator
    {
        $query = Tag::query()->selectFields();

        return QueryBuilder::for($query)
            ->allowedSorts('tag', 'uses')
            ->allowedFilters(
                AllowedFilter::custom('tag', new FilterOr),
            )
            ->addSelect([
                'uses' => DB::table('taggables')->selectRaw('COUNT(*)')->whereColumn('tags.id', 'taggables.tag_id'),
            ])
            ->paginate($request->integer('per_page'));
    }

    /** @return Collection<int, Tag> */
    public function tagsList(): Collection
    {
        return QueryBuilder::for(Tag::class)->get();
    }

    public function destroy(Tag $tag): JsonResponse
    {
        if (DB::table('taggables')->where('tag_id', $tag->id)->count() > 0) {
            return response()->json(['message' => __(
                'This tag cannot be deleted because it is associated with existing items.',
            )], 403);
        }

        $tag->delete();

        return response()->json(status: 204);
    }
}
