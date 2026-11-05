<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugObserver
{
    public function saving(Model $model): void
    {
        if (method_exists($model, 'getTranslations') && method_exists($model, 'setTranslation')) {
            $this->saveTranslated($model);

            return;
        }

        $this->saveMonolingual($model);
    }

    private function saveTranslated(Model $model): void
    {
        if (! method_exists($model, 'getTranslations') || ! method_exists($model, 'setTranslation')) {
            return;
        }

        $slugs = (array) $model->getTranslations('slug');

        foreach ((array) $model->getTranslations('title') as $locale => $title) {
            $slug = $slugs[$locale] ?? null;
            $baseSlug = is_string($slug) ? $slug : Str::slug(is_string($title) ? $title : '');

            if ($baseSlug === '') {
                continue;
            }

            $count = 1;
            while ($this->translatedSlugExists($model, (string) $locale)) {
                $model->setTranslation('slug', $locale, sprintf('%s-%d', $baseSlug, $count));
                $count++;
            }
        }
    }

    private function saveMonolingual(Model $model): void
    {
        if ($model->getAttribute('slug') === null) {
            $title = $model->getAttribute('title');
            $generatedSlug = Str::slug(is_string($title) ? $title : '');
            $model->setAttribute('slug', $generatedSlug !== '' ? $generatedSlug : null);
        }

        $baseSlug = $model->getAttribute('slug');

        if (! is_string($baseSlug)) {
            return;
        }

        $counter = 0;

        while ($this->slugExists($model)) {
            $counter++;
            $model->setAttribute('slug', sprintf('%s-%d', $baseSlug, $counter));
        }
    }

    private function translatedSlugExists(Model $model, string $locale): bool
    {
        if (! method_exists($model, 'getTranslation')) {
            return false;
        }

        return $model::query()
            ->where("slug->{$locale}", $model->getTranslation('slug', $locale))
            ->when($model->getKey(), fn ($query) => $query->where($model->getKeyName(), '!=', $model->getKey()))
            ->exists();
    }

    private function slugExists(Model $model): bool
    {
        return $model::query()
            ->where('slug', $model->getAttribute('slug'))
            ->when($model->getKey(), fn ($query) => $query->where($model->getKeyName(), '!=', $model->getKey()))
            ->exists();
    }
}
