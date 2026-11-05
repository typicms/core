<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
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

        $data->setCollection(collect($data->items())->map(function (Model $item): Model {
            $body = $item->getAttribute('body_translated');
            if (is_string($body)) {
                $item->setAttribute('body_translated', mb_trim(strip_tags(html_entity_decode($body)), '"'));
            }

            return $item;
        }));

        return $data;
    }

    protected function updatePartial(Block $block, Request $request): void
    {
        foreach ($request->only('status') as $key => $content) {
            if ($block->isTranslatableAttribute($key)) {
                if (! is_array($content)) {
                    continue;
                }

                foreach ($content as $lang => $value) {
                    $block->setTranslation($key, (string) $lang, $value);
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
