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
            $title = (string) collect($this->getTranslations('title'))->first(fn ($value): bool => filled($value));
        }

        return strip_tags($title);
    }
}
