<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Filters;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * @implements Filter<Model>
 */
class FilterOr implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $searchTerm = is_array($value) ? implode(',', array_filter($value, is_scalar(...))) : $value;
        $searchTerm = is_scalar($searchTerm) ? (string) $searchTerm : '';
        $columns = explode(',', $property);
        $locale = request()->string('locale')->value();

        $query->where(function (Builder $query) use ($columns, $searchTerm, $locale): void {
            foreach ($columns as $column) {
                if (! $this->isTranslatable($query->getModel(), $column)) {
                    $query->orWhere($column, 'like', "%{$searchTerm}%");

                    continue;
                }

                /** @var Connection $connection */
                $connection = $query->getConnection();

                $driver = $connection->getDriverName();

                if ($driver === 'pgsql') {
                    $sql = "unaccent(\"{$column}\"::jsonb->>?) ILIKE unaccent(?)";
                    $bindings = [$locale, "%{$searchTerm}%"];
                } else {
                    $configuredCollation = $connection->getConfig('collation');
                    $collation = is_string($configuredCollation) && $configuredCollation !== ''
                        ? $configuredCollation
                        : 'utf8mb4_unicode_ci';
                    $collation = $driver !== 'mariadb' ? " COLLATE {$collation}" : '';
                    $sql = "JSON_UNQUOTE(JSON_EXTRACT(`{$column}`, ?)) LIKE ?{$collation}";
                    $bindings = ["$.{$locale}", "%{$searchTerm}%"];
                }

                $query->orWhereRaw($sql, $bindings);
            }
        });
    }

    private function isTranslatable(Model $model, string $column): bool
    {
        return property_exists($model, 'translatable') && in_array($column, (array) $model->translatable, true);
    }
}
