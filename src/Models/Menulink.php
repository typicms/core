<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use TypiCMS\Modules\Core\Traits\HasConfigurableOrder;
use TypiCMS\Modules\Core\Traits\HasContentPresenter;
use TypiCMS\Modules\Core\Traits\HasSelectableFields;
use TypiCMS\Modules\Core\Traits\HasSlugScope;
use TypiCMS\Modules\Core\Traits\Historable;
use TypiCMS\Modules\Core\Traits\Publishable;
use TypiCMS\NestableCollection;
use TypiCMS\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $menu_id
 * @property int|null $page_id
 * @property int|null $section_id
 * @property int|null $parent_id
 * @property int|null $image_id
 * @property array<mixed> $data
 * @property Collection<int, Model> $items
 * @property string|null $href
 * @property bool $isLeaf
 * @property bool $isExpanded
 * @property int $position
 * @property string|null $target
 * @property string|null $class
 * @property string $status
 * @property string $title
 * @property string $website
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, History> $history
 * @property-read int|null $history_count
 * @property-read File|null $image
 * @property-read Menu $menu
 * @property-read Page|null $page
 * @property-read Menulink|null $parent
 * @property-read PageSection|null $section
 * @property-read NestableCollection<int, Menulink> $submenulinks
 * @property-read int|null $submenulinks_count
 * @property-read mixed $translations
 */
#[CollectedBy(NestableCollection::class)]
#[Unguarded]
class Menulink extends Model
{
    use HasConfigurableOrder;
    use HasContentPresenter;
    use HasSelectableFields;
    use HasSlugScope;
    use HasTranslations;
    use Historable;
    use Publishable;

    /** @var array<string> */
    public array $translatable = [
        'title',
        'website',
        'description',
        'status',
    ];

    /** @return BelongsTo<Menu, $this> */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /** @return BelongsTo<File, $this> */
    public function image(): BelongsTo
    {
        return $this->belongsTo(File::class, 'image_id');
    }

    /** @return BelongsTo<Page, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** @return BelongsTo<PageSection, $this> */
    public function section(): BelongsTo
    {
        return $this->belongsTo(PageSection::class);
    }

    /** @return HasMany<Menulink, $this> */
    public function submenulinks(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->order();
    }

    /** @return BelongsTo<Menulink, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function editUrl(): string
    {
        $route = 'admin::edit-menulink';
        if (Route::has($route)) {
            return route($route, [$this->menu_id, $this->id]);
        }

        return route('admin::dashboard');
    }

    public function indexUrl(): string
    {
        $route = 'admin::edit-menu';
        if (Route::has($route)) {
            return route($route, $this->menu_id);
        }

        return route('admin::dashboard');
    }
}
