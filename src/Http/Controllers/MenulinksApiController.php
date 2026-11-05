<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use TypiCMS\Modules\Core\Models\Menu;
use TypiCMS\Modules\Core\Models\Menulink;
use TypiCMS\Modules\Core\Models\User;
use TypiCMS\NestableCollection;

final class MenulinksApiController extends BaseApiController
{
    /** @return array{models: NestableCollection<int, Menulink>, total: int} */
    public function index(Menu $menu, Request $request): array
    {
        /** @var User $user */
        $user = $request->user();
        $userPreferences = $user->preferences ?? [];

        $query = Menulink::query()
            ->selectFields()
            ->where('menu_id', $menu->id)
            ->orderBy('position');

        $total = $query->count();

        $models = $query
            ->get()
            ->map(function (Model $menulink) use ($userPreferences): Model {
                /** @var Menulink $menulink */
                $menulink->data = $menulink->toArray();
                $menulink->isLeaf = false;
                $menulink->isExpanded = ! Arr::get($userPreferences, 'menulinks_'.$menulink->id.'_collapsed', false);

                return $menulink;
            })
            ->childrenName('children')
            ->nest();

        return ['models' => $models, 'total' => $total];
    }

    protected function updatePartial(Menu $menu, Menulink $menulink, Request $request): void
    {
        foreach ($request->only('status') as $key => $content) {
            if ($menulink->isTranslatableAttribute($key)) {
                if (! is_array($content)) {
                    continue;
                }

                foreach ($content as $lang => $value) {
                    $menulink->setTranslation($key, (string) $lang, $value);
                }
            } else {
                $menulink->{$key} = $content;
            }
        }

        $menulink->save();
    }

    public function sort(Menu $menu, Request $request): void
    {
        foreach ($request->array('item') as $position => $item) {
            if (! is_int($position) || ! is_array($item)) {
                continue;
            }

            $menulink = Menulink::query()->find($item['id'] ?? null);
            if (! $menulink instanceof Menulink) {
                continue;
            }

            $menulink->update([
                'position' => $position + 1,
                'parent_id' => $item['parent_id'] ?? null,
            ]);
        }
    }

    public function destroy(Menu $menu, Menulink $menulink): JsonResponse
    {
        $menulink->submenulinks()->update(['parent_id' => $menulink->parent_id]);
        $menulink->delete();

        return response()->json(status: 204);
    }
}
