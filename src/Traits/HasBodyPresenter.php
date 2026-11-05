<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
        $text = $this->getAttribute($property);
        if (! is_string($text)) {
            return '';
        }

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
            $replacements[] = $this->dynamicLinkUrl($match[1], $match[2], $lang);
        }

        return str_replace($patterns, $replacements, $text);
    }

    private function dynamicLinkUrl(string $module, string $id, string $lang): string
    {
        if (in_array($module, ['page', 'tag', 'user', 'term', 'taxonomy'], true)) {
            $classname = 'TypiCMS\Modules\Core\Models\\'.ucfirst($module);
        } else {
            $classname = 'TypiCMS\Modules\\'.ucfirst(Str::plural($module)).'\Models\\'.ucfirst($module);
        }

        if (! is_subclass_of($classname, Model::class) || ! method_exists($classname, 'published')) {
            return '';
        }

        $model = $classname::query()
            ->tap(fn (Builder $query) => $query->scopes(['published']))
            ->find($id);

        if (! $model instanceof Model) {
            return '';
        }

        if ($module === 'page') {
            $url = method_exists($model, 'url') ? $model->url($lang) : null;

            return is_string($url) ? $url : '';
        }

        if (! Route::has("{$lang}::{$module}")) {
            return '';
        }

        $slug = $model->getAttribute('slug');

        return is_string($slug) ? route("{$lang}::{$module}", $slug) : '';
    }
}
