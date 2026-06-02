<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Traits;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasSelectableFields
{
    /** @var array<string, list<string>> */
    private static array $selectableColumnsCache = [];

    private function isFieldTranslatable(string $field): bool
    {
        /** @var array<string> $translatable */
        $translatable = $this->translatable ?? [];

        return in_array($field, $translatable, true);
    }

    /**
     * Real columns of the underlying table, used to allowlist requested fields.
     *
     * @param  Builder<Model>  $query
     * @return list<string>
     */
    private function selectableColumns(Builder $query): array
    {
        $table = $this->getTable();

        return self::$selectableColumnsCache[$table] ??= $query
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($table);
    }

    /** @param Builder<Model> $query */
    #[Scope]
    protected function selectFields(Builder $query): void
    {
        $locale = request('locale', app()->getLocale());
        $fields = explode(',', (string) request()->string('fields.'.$this->getTable()));
        $allowedColumns = $this->selectableColumns($query);

        foreach ($fields as $field) {
            $field = mb_trim($field);

            if (! $this->isFieldTranslatable($field)) {
                // Never let a user-supplied value dictate a column name: only
                // select columns that actually exist on the table.
                if (in_array($field, $allowedColumns, true)) {
                    $query->addSelect($field);
                }

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
                $collation = $driver !== 'mariadb'
                    ? ' COLLATE '.((string) $connection->getConfig('collation') ?: 'utf8mb4_unicode_ci')
                    : '';
                $sql = "CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(`{$field}`, ?)) = 'null' THEN NULL ELSE JSON_UNQUOTE(JSON_EXTRACT(`{$field}`, ?)) END{$collation} AS `{$field}_translated`";
                $bindings = ["$.{$locale}", "$.{$locale}"];
            }

            $query->selectRaw($sql, $bindings);
        }
    }
}
