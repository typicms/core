<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;
use TypiCMS\Modules\Core\Filters\FilterOr;
use TypiCMS\Modules\Core\Models\User;

final class UsersApiController extends BaseApiController
{
    /** @return LengthAwarePaginator<int, mixed> */
    public function index(Request $request): LengthAwarePaginator
    {
        return QueryBuilder::for(User::class)
            ->allowedFields(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.activated',
                'users.superuser',
                'roles.name',
            )
            ->allowedIncludes(
                'roles',
                AllowedInclude::count('rolesCount'),
            )
            ->allowedSorts('first_name', 'last_name', 'email', 'superuser', 'activated', 'roles_count')
            ->allowedFilters(
                AllowedFilter::custom('first_name,last_name,email', new FilterOr),
            )
            ->paginate($request->integer('per_page'));
    }

    public function updatePreferences(Request $request): void
    {
        /** @var User $user */
        $user = $request->user();
        $user->preferences = array_merge((array) $user->preferences, $request->input());
        $user->save();
    }

    public function destroy(User $user): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if ($currentUser->id === $user->id) {
            return response()->json([
                'error' => true,
                'message' => __('The current logged in user cannot be deleted.'),
            ], 403);
        }

        if (method_exists($user, 'hasRunningSubscription') && $user->hasRunningSubscription()) {
            return response()->json([
                'error' => true,
                'message' => __('The user :name can not be deleted because he has a running subscription.', ['name' => sprintf(
                    '%s %s',
                    $user->first_name,
                    $user->last_name,
                )]),
            ], 403);
        }

        $user->delete();

        return response()->json(status: 204);
    }
}
