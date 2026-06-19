<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use TypiCMS\Modules\Core\Traits\HasContentPresenter;
use TypiCMS\Translatable\HasTranslations;

function makePresentable(array $title): Model
{
    $model = new class extends Model
    {
        use HasContentPresenter;
        use HasTranslations;

        public array $translatable = ['title'];
    };

    $model->exists = true;
    $model->setRawAttributes(['title' => json_encode($title)]);

    return $model;
}

beforeEach(function (): void {
    app()->setLocale('en');
    config(['app.fallback_locale' => null]);
});

it('returns the title in the current locale', function (): void {
    $model = makePresentable(['en' => 'Hello', 'fr' => 'Bonjour']);

    expect($model->presentTitle())->toBe('Hello');
});

it('falls back to any available translation when the current locale is empty and fallback is disabled', function (): void {
    $model = makePresentable(['fr' => 'Bonjour', 'nl' => 'Hallo']);

    expect($model->presentTitle())->toBe('Bonjour');
});

it('returns an empty string when no translation exists', function (): void {
    $model = makePresentable([]);

    expect($model->presentTitle())->toBe('');
});

it('returns an empty string for a non-persisted model', function (): void {
    $model = makePresentable(['fr' => 'Bonjour']);
    $model->exists = false;

    expect($model->presentTitle())->toBe('');
});
