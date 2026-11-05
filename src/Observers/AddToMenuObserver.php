<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Observers;

use TypiCMS\Modules\Core\Models\Menulink;
use TypiCMS\Modules\Core\Models\Page;

class AddToMenuObserver
{
    /**
     * If a new homepage is defined, cancel previous homepage.
     */
    public function created(Page $model): void
    {
        if ($menu_id = request()->integer('add_to_menu')) {
            $position = $this->getPositionFormMenu($menu_id);
            $data = [
                'menu_id' => $menu_id,
                'page_id' => $model->id,
                'position' => $position,
            ];
            foreach (locales() as $locale) {
                $data['title'][$locale] = $model->translate('title', $locale);
                $data['status'][$locale] = 0;
                $data['description'][$locale] = null;
                $data['website'][$locale] = '';
            }

            Menulink::query()->create($data);
        }
    }

    private function getPositionFormMenu(int $id): int
    {
        $position = Menulink::query()->where('menu_id', $id)->max('position');

        return (is_numeric($position) ? (int) $position : 0) + 1;
    }
}
