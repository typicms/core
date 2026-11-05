<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use TypiCMS\Modules\Core\Filters\FilterOr;
use TypiCMS\Modules\Core\Models\Block;

final class ApiController extends BaseApiController
{
    /** @return LengthAwarePaginator<int, Block> */
    public function index(Request $request): LengthAwarePaginator
    {
        $query = Block::query()->selectFields();
        $data = QueryBuilder::for($query)
            ->allowedSorts('status_translated', 'name', 'body_translated')
            ->allowedFilters(
                AllowedFilter::custom('name,body', new FilterOr),
            )
            ->paginate($request->integer('per_page'));

        $data->setCollection(collect($data->items())->map(function ($item) {
            if (property_exists($item, 'body_translated')) {
                $item->body_translated = mb_trim(strip_tags(html_entity_decode((string) $item->body_translated)), '"');
            }

            return $item;
        }));

        return $data;
    }

    protected function updatePartial(Block $block, Request $request): void
    {
        foreach ($request->only('status') as $key => $content) {
            if ($block->isTranslatableAttribute($key)) {
                foreach ($content as $lang => $value) {
                    $block->setTranslation($key, $lang, $value);
                }
            } else {
                $block->{$key} = $content;
            }
        }

        $block->save();
    }

    public function destroy(Block $block): JsonResponse
    {
        $block->delete();

        return response()->json(status: 204);
    }
}
