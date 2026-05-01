<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Traits;

use Illuminate\Database\Eloquent\Model;

trait Navigable
{
    public function next(?int $category_id = null): ?Model
    {
        return $this->adjacent(1, $category_id);
    }

    public function prev(?int $category_id = null): ?Model
    {
        return $this->adjacent(-1, $category_id);
    }

    public function adjacent(int $direction, ?int $category_id = null): ?Model
    {
        $models = static::query()
            ->published()
            ->order()
            ->get(['id', 'slug', 'title']);

        foreach ($models as $key => $model) {
            if ($this->id === $model->id) {
                $adjacentKey = $key + $direction;

                return $models[$adjacentKey] ?? null;
            }
        }

        return null;
    }
}
