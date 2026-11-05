<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use TypiCMS\Modules\Core\Filters\FilterOr;
use TypiCMS\Modules\Core\Models\Taxonomy;

final class TaxonomiesApiController extends BaseApiController
{
    /** @return LengthAwarePaginator<int, Taxonomy> */
    public function index(Request $request): LengthAwarePaginator
    {
        $query = Taxonomy::query()->selectFields();

        return QueryBuilder::for($query)
            ->allowedSorts('title_translated', 'validation_rule', 'result_string_translated', 'position', 'name')
            ->allowedFilters(
                AllowedFilter::custom('title,name,validation_rule,result_string', new FilterOr),
            )
            ->paginate($request->integer('per_page'));
    }

    protected function updatePartial(Taxonomy $taxonomy, Request $request): void
    {
        foreach ($request->only('position') as $key => $content) {
            if ($taxonomy->isTranslatableAttribute($key)) {
                if (! is_array($content)) {
                    continue;
                }

                foreach ($content as $lang => $value) {
                    $taxonomy->setTranslation($key, (string) $lang, $value);
                }
            } else {
                $taxonomy->{$key} = $content;
            }
        }

        $taxonomy->save();
    }

    public function destroy(Taxonomy $taxonomy): JsonResponse
    {
        if ($taxonomy->terms->count() > 0) {
            return response()->json(['message' => __('This taxonomy cannot be deleted as it contains terms.')], 403);
        }

        $taxonomy->delete();

        return response()->json(status: 204);
    }
}
