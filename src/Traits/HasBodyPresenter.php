<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Traits;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

trait HasBodyPresenter
{
    public function formattedBody(): string
    {
        return $this->dynamicLinks();
    }

    public function dynamicLinks(string $property = 'body'): string
    {
        $text = $this->$property ?? '';
        preg_match_all(
            '/(?:{|%7B)!!(?:\s|%20)([a-z]+):(\d+)(?:\s|%20)!!(?:}|%7D)/',
            $text,
            $matches,
            PREG_SET_ORDER,
        );
        $patterns = [];
        $replacements = [];
        $lang = app()->getLocale();
        foreach ($matches as $match) {
            $patterns[] = $match[0];
            $module = $match[1];
            if (in_array($module, ['page', 'tag', 'user', 'term', 'taxonomy'], true)) {
                $classname = 'TypiCMS\Modules\Core\Models\\'.ucfirst($module);
            } else {
                $classname = 'TypiCMS\Modules\\'.ucfirst(Str::plural($module)).'\Models\\'.ucfirst($module);
            }

            $model = null;
            if (class_exists($classname)) {
                $model = $classname::query()->published()->find($match[2]);
            }

            if ($model === null) {
                continue;
            }

            if ($module === 'page') {
                $replacements[] = $model->url($lang) ?? '';
            } elseif (Route::has($lang.'::'.$module)) {
                $replacements[] = route($lang.'::'.$module, $model->slug);
            } else {
                $replacements[] = '';
            }
        }

        return str_replace($patterns, $replacements, $text);
    }
}
