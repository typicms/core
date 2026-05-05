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
        $columns = ['id', 'slug', 'title'];
        if ($category_id){
            $columns[] = 'category_id';
        }
        $collection = static::query()
            ->published()
            ->when($category_id, fn ($query) => $query->where('category_id', $category_id))
            ->order()
            ->get($columns);

        $key = $collection->search(fn (self $model): bool => $model->id === $this->id);

        if ($key === false) {
            return null;
        }

        return $collection[$key + $direction] ?? null;
    }
}
