<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Traits;

trait HasContentPresenter
{
    public function presentTitle(): string
    {
        if (! $this->exists) {
            return '';
        }

        $title = (string) $this->title;

        if ($title === '' && method_exists($this, 'isTranslatableAttribute') && $this->isTranslatableAttribute('title')) {
            $firstTitle = collect($this->getTranslations('title'))->first(fn (mixed $value): bool => filled($value));
            $title = is_string($firstTitle) ? $firstTitle : '';
        }

        return strip_tags($title);
    }
}
