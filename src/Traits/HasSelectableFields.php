<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Traits;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasSelectableFields
{
    private function isFieldTranslatable(string $field): bool
    {
        /** @var array<string> $translatable */
        $translatable = $this->translatable ?? [];

        return in_array($field, $translatable, true);
    }

    /** @param Builder<Model> $query */
    #[Scope]
    protected function selectFields(Builder $query): void
    {
        $locale = request()->string('locale', app()->getLocale())->toString();
        $fields = explode(',', (string) request()->string('fields.'.$this->getTable()));

        foreach ($fields as $field) {
            if (! $this->isFieldTranslatable($field)) {
                $query->addSelect($field);

                continue;
            }

            /** @var Connection $connection */
            $connection = $query->getConnection();
            $driver = $connection->getDriverName();

            if ($field === 'status') {
                if ($driver === 'pgsql') {
                    $sql = "({$field}::json->>?)::int AS {$field}_translated";
                    $bindings = [$locale];
                } else {
                    $sql = "CAST(JSON_UNQUOTE(JSON_EXTRACT(`{$field}`, ?)) AS UNSIGNED) AS `{$field}_translated`";
                    $bindings = ["$.{$locale}"];
                }

                $query->selectRaw($sql, $bindings);

                continue;
            }

            if ($driver === 'pgsql') {
                $sql = "{$field}::json->>? AS {$field}_translated";
                $bindings = [$locale];
            } else {
                $configuredCollation = $connection->getConfig('collation');
                $collation = is_string($configuredCollation) && $configuredCollation !== ''
                    ? $configuredCollation
                    : 'utf8mb4_unicode_ci';
                $collation = $driver !== 'mariadb' ? " COLLATE {$collation}" : '';
                $sql = "CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(`{$field}`, ?)) = 'null' THEN NULL ELSE JSON_UNQUOTE(JSON_EXTRACT(`{$field}`, ?)) END{$collation} AS `{$field}_translated`";
                $bindings = ["$.{$locale}", "$.{$locale}"];
            }

            $query->selectRaw($sql, $bindings);
        }
    }
}
