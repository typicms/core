<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use TypiCMS\Modules\Core\Traits\HasAdminUrls;
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
 * @property int|null $image_id
 * @property string $name
 * @property string|null $class
 * @property array<array-key, mixed> $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, History> $history
 * @property-read int|null $history_count
 * @property-read File|null $image
 * @property NestableCollection<int, Menulink> $menulinks
 * @property-read int|null $menulinks_count
 * @property-read mixed $thumb
 * @property-read mixed $translations
 */
#[Unguarded]
#[Appends(['thumb'])]
class Menu extends Model
{
    use HasAdminUrls;
    use HasConfigurableOrder;
    use HasContentPresenter;
    use HasSelectableFields;
    use HasSlugScope;
    use HasTranslations;
    use Historable;
    use Publishable;

    public function presentTitle(): string
    {
        return $this->name;
    }

    /** @var array<string> */
    public array $translatable = [
        'status',
    ];

    public function getMenu(string $name): ?self
    {
        $menu = static::query()
            ->published()
            ->with([
                'menulinks' => function ($query): void {
                    $query->with([
                        'page',
                        'section',
                        'image',
                        'parent',
                    ])->published();
                },
            ])
            ->where('name', $name)
            ->first();

        if (! $menu) {
            Log::info("No menu named “{$name}” found.");

            return null;
        }

        $menu->menulinks = $menu->menulinks->each(function (Menulink $menulink): void {
            $menulink->items = new Collection;
            $menulink->href = $this->setHref($menulink);
            $menulink->class = $this->setClass($menulink);
        })->nest();

        return $menu;
    }

    public function setHref(Menulink $menulink): string
    {
        if ($menulink->website) {
            return $menulink->website;
        }

        if (! $menulink->page) {
            return '/';
        }

        $url = $menulink->page->path();

        if ($menulink->section) {
            return "{$url}#{$menulink->section->slug}-{$menulink->section->id}";
        }

        return $url;
    }

    public function setClass(Menulink $menulink): string
    {
        $classes = collect(explode(' ', (string) $menulink->class))->filter();

        $pattern = mb_strlen((string) $menulink->href) > 3 ? "{$menulink->href}*" : $menulink->href;

        if (request()->is($pattern)) {
            $classes->push('active');
        }

        return $classes->implode(' ');
    }

    /** @return Attribute<string, null> */
    protected function thumb(): Attribute
    {
        return Attribute::make(get: fn (): string => imageOrDefault($this->image, null, 54));
    }

    /** @return BelongsTo<File, $this> */
    public function image(): BelongsTo
    {
        return $this->belongsTo(File::class, 'image_id');
    }

    /** @return HasMany<Menulink, $this> */
    public function menulinks(): HasMany
    {
        return $this->hasMany(Menulink::class)->orderBy('position', 'asc');
    }
}
