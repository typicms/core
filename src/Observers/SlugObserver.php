<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Observers;

use Illuminate\Support\Str;

class SlugObserver
{
    public function saving(mixed $model): void
    {
        if (method_exists($model, 'getTranslations') && method_exists($model, 'setTranslation')) {
            $this->saveTranslated($model);

            return;
        }

        $this->saveMonolingual($model);
    }

    private function saveTranslated(mixed $model): void
    {
        $slugs = $model->getTranslations('slug');

        foreach ($model->getTranslations('title') as $locale => $title) {
            $baseSlug = $slugs[$locale] ?? Str::slug($title);

            if ($baseSlug === '') {
                continue;
            }

            $count = 1;
            while ($this->translatedSlugExists($model, $locale)) {
                $model->setTranslation('slug', $locale, sprintf('%s-%d', $baseSlug, $count));
                $count++;
            }
        }
    }

    private function saveMonolingual(mixed $model): void
    {
        if ($model->slug === null) {
            $generatedSlug = Str::slug($model->title);
            $model->slug = $generatedSlug !== '' ? $generatedSlug : null;
        }

        if ($model->slug === null) {
            return;
        }

        $baseSlug = $model->slug;
        $counter = 0;

        while ($this->slugExists($model)) {
            $counter++;
            $model->slug = sprintf('%s-%d', $baseSlug, $counter);
        }
    }

    private function translatedSlugExists(mixed $model, string $locale): bool
    {
        return $model::query()
            ->where('slug->'.$locale, $model->getTranslation('slug', $locale))
            ->when($model->id, fn ($query) => $query->where('id', '!=', $model->id))
            ->exists();
    }

    private function slugExists(mixed $model): bool
    {
        return $model::query()
            ->where('slug', $model->slug)
            ->when($model->id, fn ($query) => $query->where('id', '!=', $model->id))
            ->exists();
    }
}
