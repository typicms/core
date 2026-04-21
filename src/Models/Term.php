<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use TypiCMS\Modules\Core\Observers\SlugObserver;
use TypiCMS\Modules\Core\Traits\HasConfigurableOrder;
use TypiCMS\Modules\Core\Traits\HasContentPresenter;
use TypiCMS\Modules\Core\Traits\HasSelectableFields;
use TypiCMS\Modules\Core\Traits\HasSlugScope;
use TypiCMS\Modules\Core\Traits\Historable;
use TypiCMS\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $position
 * @property int $taxonomy_id
 * @property string $title
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, History> $history
 * @property-read int|null $history_count
 * @property-write mixed $status
 * @property-read Taxonomy $taxonomy
 * @property-read mixed $translations
 */
#[ObservedBy(SlugObserver::class)]
#[Unguarded]
class Term extends Model implements Sortable
{
    use HasConfigurableOrder;
    use HasContentPresenter;
    use HasSelectableFields;
    use HasSlugScope;
    use HasTranslations;
    use Historable;
    use SortableTrait;

    /** @var array<string> */
    public array $translatable = [
        'title',
        'slug',
    ];

    /** @var array<string> */
    public array $sortable = [
        'order_column_name' => 'position',
    ];

    /** @return Builder<static> */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('taxonomy_id', $this->taxonomy_id);
    }

    public function editUrl(): string
    {
        $route = 'admin::edit-term';
        if (Route::has($route)) {
            return route($route, [$this->taxonomy_id, $this->id]);
        }

        return route('admin::dashboard');
    }

    public function indexUrl(): string
    {
        $route = 'admin::index-terms';
        if (Route::has($route)) {
            return route($route, $this->taxonomy_id);
        }

        return route('admin::dashboard');
    }

    /** @return BelongsTo<Taxonomy, $this> */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }

    // public function publishedProjects(): MorphToMany
    // {
    //     return $this->morphedByMany(Project::class, 'model', 'model_has_terms')
    //         ->published()
    //         ->withTimestamps();
    // }
}
