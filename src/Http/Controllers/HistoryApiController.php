<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use TypiCMS\Modules\Core\Filters\FilterOr;
use TypiCMS\Modules\Core\Models\History;
use TypiCMS\Modules\Core\Models\User;

final class HistoryApiController extends BaseApiController
{
    /** @return LengthAwarePaginator<int, History> */
    public function index(Request $request): LengthAwarePaginator
    {
        $query = History::query();

        return QueryBuilder::for($query)
            ->allowedFields(
                'history.id',
                'history.created_at',
                'history.title',
                'history.locale',
                'history.historable_id',
                'history.historable_type',
                'history.action',
                'history.user_id',
            )
            ->allowedSorts('created_at', 'title', 'historable_type', 'action', 'user_name')
            ->allowedFilters(
                AllowedFilter::custom('title,historable_type,action,user_name', new FilterOr),
            )
            ->selectSub(
                User::query()->selectRaw('CONCAT(`first_name`, " ", `last_name`)')->whereColumn('user_id', 'users.id'),
                'user_name',
            )
            ->paginate($request->integer('per_page'));
    }

    public function destroy(): JsonResponse
    {
        History::query()->truncate();

        return response()->json(status: 204);
    }
}
