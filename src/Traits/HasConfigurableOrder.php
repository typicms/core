<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Traits;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasConfigurableOrder
{
    /** @param Builder<Model> $query */
    #[Scope]
    protected function order(Builder $query): void
    {
        $order = config("typicms.modules.{$this->getTable()}.order");

        if (! is_array($order)) {
            return;
        }

        foreach ($order as $field => $direction) {
            $direction = is_string($direction) ? mb_strtolower($direction) : null;

            if (! is_string($field) || ! in_array($direction, ['asc', 'desc'], true)) {
                continue;
            }

            $query->orderBy($field, $direction);
        }
    }
}
