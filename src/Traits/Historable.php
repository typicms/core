<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use TypiCMS\Modules\Core\Models\History;

trait Historable
{
    public static function bootHistorable(): void
    {
        static::created(function (self $model): void {
            $model->writeHistory('created', Str::limit($model->presentTitle(), 200, '…'), [], $model->toArray());
        });

        static::updated(function (self $model): void {
            $new = [];
            $old = [];
            foreach ($model->getAttributes() as $key => $value) {
                if ($model->isTranslatableHistoryAttribute($key)) {
                    $values = (array) json_decode(is_string($value) ? $value : '');
                    $original = $model->getRawOriginal($key);
                    $originalValues = (array) json_decode(is_string($original) ? $original : '');
                    $newTranslations = [];
                    $oldTranslations = [];
                    foreach ($values as $locale => $newItem) {
                        if (isset($originalValues[$locale]) && $newItem !== $originalValues[$locale]) {
                            $newTranslations[$locale] = $newItem;
                            $oldTranslations[$locale] = $originalValues[$locale];
                        }
                    }

                    if ($newTranslations !== []) {
                        $new[$key] = $newTranslations;
                        $old[$key] = $oldTranslations;
                    }

                    continue;
                }

                $originalValue = $model->getRawOriginal($key) ?? '';
                if ($value !== $originalValue) {
                    $new[$key] = $value;
                    $old[$key] = $originalValue;
                }
            }

            $model->writeHistory('updated', Str::limit($model->presentTitle(), 200, '…'), $old, $new);
        });

        static::deleted(function (self $model): void {
            $model->writeHistory('deleted', Str::limit($model->presentTitle(), 200, '…'));
        });
    }

    private function isTranslatableHistoryAttribute(string $key): bool
    {
        return property_exists($this, 'translatable')
            && is_array($this->translatable)
            && in_array($key, $this->translatable, true);
    }

    /**
     * Write History row.
     *
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     */
    public function writeHistory(string $action, ?string $title = null, array $old = [], array $new = []): void
    {
        History::query()->create([
            'historable_id' => $this->getKey(),
            'historable_type' => $this::class,
            'user_id' => auth()->id(),
            'title' => $title,
            'historable_table' => $this->getTable(),
            'action' => $action,
            'old' => $old,
            'new' => $new,
        ]);
    }

    /** @return MorphMany<History, $this> */
    public function history(): MorphMany
    {
        return $this->morphMany(History::class, 'historable');
    }
}
