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
use TypiCMS\Modules\Core\Models\Term;

final class TermsApiController extends BaseApiController
{
    /** @return LengthAwarePaginator<int, Term> */
    public function index(Taxonomy $taxonomy, Request $request): LengthAwarePaginator
    {
        $query = Term::query()->selectFields()->where('taxonomy_id', $taxonomy->id);

        return QueryBuilder::for($query)
            ->allowedSorts('title_translated', 'position')
            ->allowedFilters(
                AllowedFilter::custom('title', new FilterOr),
            )
            ->paginate($request->integer('per_page'));
    }

    protected function updatePartial(Taxonomy $taxonomy, Term $term, Request $request): void
    {
        foreach ($request->only('position') as $key => $content) {
            if ($term->isTranslatableAttribute($key)) {
                foreach ($content as $lang => $value) {
                    $term->setTranslation($key, $lang, $value);
                }
            } else {
                $term->{$key} = $content;
            }
        }

        $term->save();
    }

    public function destroy(Taxonomy $taxonomy, Term $term): JsonResponse
    {
        $term->delete();

        return response()->json(status: 204);
    }
}
