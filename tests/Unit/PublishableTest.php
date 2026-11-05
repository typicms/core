<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use TypiCMS\Modules\Core\Traits\Publishable;
use TypiCMS\Translatable\HasTranslations;

function makeMonolingualPublishable(): Model
{
    return new class extends Model
    {
        use Publishable;

        protected $table = 'items';
    };
}

function makeTranslatablePublishable(): Model
{
    return new class extends Model
    {
        use HasTranslations;
        use Publishable;

        protected $table = 'items';

        public array $translatable = ['status'];
    };
}

beforeEach(function (): void {
    app()->setLocale('en');
});

it('reads the status of a model without translations', function (): void {
    $model = makeMonolingualPublishable();
    $model->setRawAttributes(['status' => 1]);

    expect($model->isPublished())->toBeTrue();
});

it('filters a model without translations on its status column', function (): void {
    $query = makeMonolingualPublishable()->newQuery()->published();

    expect($query->getQuery()->wheres[0]['column'])->toBe('status');
});

it('reads the status of a translatable model in the requested locale', function (): void {
    $model = makeTranslatablePublishable();
    $model->setRawAttributes(['status' => json_encode(['en' => 1, 'fr' => 0])]);

    expect($model->isPublished('en'))->toBeTrue()
        ->and($model->isPublished('fr'))->toBeFalse();
});

it('filters a translatable model on its status in the current locale', function (): void {
    $query = makeTranslatablePublishable()->newQuery()->published();

    expect($query->getQuery()->wheres[0]['column'])->toBe('status->en');
});
