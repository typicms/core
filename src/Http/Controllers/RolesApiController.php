<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use TypiCMS\Modules\Core\Filters\FilterOr;
use TypiCMS\Modules\Core\Models\Role;

final class RolesApiController extends BaseApiController
{
    /** @return LengthAwarePaginator<int, Role> */
    public function index(Request $request): LengthAwarePaginator
    {
        return QueryBuilder::for(Role::class)
            ->allowedSorts('name')
            ->allowedFilters(
                AllowedFilter::custom('name', new FilterOr),
            )
            ->paginate($request->integer('per_page'));
    }

    public function destroy(Role $role): JsonResponse
    {
        $role->delete();

        return response()->json(status: 204);
    }
}
