<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Traits;

use Illuminate\Support\Facades\Vite;

trait HasOgImage
{
    public function ogImageUrl(): string
    {
        if ($this->ogImage) {
            return $this->ogImage->render(1200, 630);
        }

        if ($this->image) {
            return $this->image->render(1200, 630);
        }

        return Vite::asset(config()->string('typicms.og_image'));
    }
}
