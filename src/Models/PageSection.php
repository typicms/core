<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Uri;
use Override;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use TypiCMS\Modules\Core\Observers\TipTapHTMLObserver;
use TypiCMS\Modules\Core\Traits\HasBodyPresenter;
use TypiCMS\Modules\Core\Traits\HasConfigurableOrder;
use TypiCMS\Modules\Core\Traits\HasContentPresenter;
use TypiCMS\Modules\Core\Traits\HasFiles;
use TypiCMS\Modules\Core\Traits\HasSelectableFields;
use TypiCMS\Modules\Core\Traits\HasSlugScope;
use TypiCMS\Modules\Core\Traits\Historable;
use TypiCMS\Modules\Core\Traits\Publishable;
use TypiCMS\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $page_id
 * @property int|null $image_id
 * @property int $position
 * @property bool $hide_title
 * @property string|null $template
 * @property string $status
 * @property string $title
 * @property string $slug
 * @property string $body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[ObservedBy([TipTapHTMLObserver::class])]
#[Unguarded]
#[Appends(['thumb'])]
class PageSection extends Model implements Sortable
{
    use HasBodyPresenter;
    use HasConfigurableOrder;
    use HasContentPresenter;
    use HasFiles;
    use HasSelectableFields;
    use HasSlugScope;
    use HasTranslations;
    use Historable;
    use Publishable;
    use SortableTrait;

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'hide_title' => 'boolean',
        ];
    }

    /** @var array<string> */
    public array $translatable = [
        'title',
        'slug',
        'status',
        'body',
    ];

    /** @var array<string> */
    public array $tipTapContent = [
        'body',
    ];

    /** @var array<string> */
    public array $sortable = [
        'order_column_name' => 'position',
    ];

    /** @return Builder<static> */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('page_id', $this->page_id);
    }

    /** @return Attribute<string, null> */
    protected function thumb(): Attribute
    {
        return Attribute::make(get: fn (): string => imageOrDefault($this->image, null, 54));
    }

    public function url(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $pageUrl = $this->page?->url($locale);

        if (! $pageUrl) {
            return null;
        }

        return "{$pageUrl}#{$this->position}-{$this->translate('slug', $locale)}";
    }

    public function previewUrl(?string $locale = null): ?string
    {
        $url = $this->url($locale);

        if (! $url) {
            return null;
        }

        return (string) Uri::of($url)->withQuery(['preview' => 'true']);
    }

    public function editUrl(): string
    {
        $route = 'admin::edit-page_section';
        if (Route::has($route)) {
            return route($route, [$this->page_id, $this->id]);
        }

        return route('admin::dashboard');
    }

    public function indexUrl(): string
    {
        $route = 'admin::edit-page';
        if (Route::has($route)) {
            return route($route, $this->page_id);
        }

        return route('admin::dashboard');
    }

    /** @return BelongsTo<Page, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** @return BelongsTo<File, $this> */
    public function image(): BelongsTo
    {
        return $this->belongsTo(File::class, 'image_id');
    }
}
