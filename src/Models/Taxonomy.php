<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Override;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use TypiCMS\Modules\Core\Observers\SlugObserver;
use TypiCMS\Modules\Core\Traits\HasAdminUrls;
use TypiCMS\Modules\Core\Traits\HasConfigurableOrder;
use TypiCMS\Modules\Core\Traits\HasSelectableFields;
use TypiCMS\Modules\Core\Traits\HasSlugScope;
use TypiCMS\Modules\Core\Traits\Historable;
use TypiCMS\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $position
 * @property string $name
 * @property string $title
 * @property string $slug
 * @property string|null $validation_rule
 * @property string $result_string
 * @property string $modules
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, History> $history
 * @property-read int|null $history_count
 * @property-write mixed $status
 * @property-read Collection<int, Term> $terms
 * @property-read int|null $terms_count
 * @property-read mixed $translations
 */
#[ObservedBy(SlugObserver::class)]
#[Unguarded]
class Taxonomy extends Model implements Sortable
{
    use HasAdminUrls;
    use HasConfigurableOrder;
    use HasSelectableFields;
    use HasSlugScope;
    use HasTranslations;
    use Historable;
    use SortableTrait;

    public function presentTitle(): string
    {
        return $this->name ?? '';
    }

    /** @var array<string> */
    public array $translatable = [
        'title',
        'slug',
        'result_string',
    ];

    /** @var array<string> */
    public array $sortable = [
        'order_column_name' => 'position',
    ];

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'modules' => 'array',
        ];
    }

    /** @return array<string, string> */
    public function allForSelect(): array
    {
        $locale = in_array(app()->getLocale(), locales(), true) ? app()->getLocale() : mainLocale();

        $items = self::query()
            ->order()
            ->get()
            ->each(fn (self $item): Taxonomy => $item->setLocale($locale))
            ->pluck('title', 'id')
            ->all();

        return ['' => ''] + $items;
    }

    /** @return HasMany<Term, $this> */
    public function terms(): HasMany
    {
        return $this->hasMany(Term::class)->order();
    }
}
