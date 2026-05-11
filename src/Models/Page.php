<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Uri;
use Override;
use TypiCMS\Modules\Core\Observers\AddToMenuObserver;
use TypiCMS\Modules\Core\Observers\HomePageObserver;
use TypiCMS\Modules\Core\Observers\TipTapHTMLObserver;
use TypiCMS\Modules\Core\Observers\UriObserver;
use TypiCMS\Modules\Core\Traits\HasAdminUrls;
use TypiCMS\Modules\Core\Traits\HasBodyPresenter;
use TypiCMS\Modules\Core\Traits\HasConfigurableOrder;
use TypiCMS\Modules\Core\Traits\HasContentPresenter;
use TypiCMS\Modules\Core\Traits\HasFiles;
use TypiCMS\Modules\Core\Traits\HasOgImage;
use TypiCMS\Modules\Core\Traits\HasSelectableFields;
use TypiCMS\Modules\Core\Traits\HasSlugScope;
use TypiCMS\Modules\Core\Traits\Historable;
use TypiCMS\Modules\Core\Traits\Navigable;
use TypiCMS\Modules\Core\Traits\Publishable;
use TypiCMS\NestableCollection;
use TypiCMS\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int|null $og_image_id
 * @property int|null $image_id
 * @property int|null $parent_id
 * @property int $position
 * @property bool $private
 * @property array<string, mixed> $data
 * @property bool $isLeaf
 * @property bool $isExpanded
 * @property bool $is_home
 * @property bool $redirect
 * @property string $title
 * @property string $slug
 * @property string $uri
 * @property string $body
 * @property string $status
 * @property string $meta_title
 * @property string $meta_description
 * @property string $meta_keywords
 * @property string|null $module
 * @property string|null $template
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, File> $audios
 * @property-read Collection<int, File> $documents
 * @property-read Collection<int, File> $files
 * @property-read Collection<int, History> $history
 * @property-read File|null $image
 * @property-read Collection<int, File> $images
 * @property-read NestableCollection<int, Menulink> $menulinks
 * @property-read File|null $ogImage
 * @property-read Page|null $parent
 * @property-read Collection<int, PageSection> $publishedSections
 * @property-read NestableCollection<int, Page> $publishedSubpages
 * @property-read Collection<int, PageSection> $sections
 * @property-read NestableCollection<int, Page> $subpages
 * @property-read mixed $translations
 * @property-read Collection<int, File> $videos
 */
#[ObservedBy([AddToMenuObserver::class, HomePageObserver::class, UriObserver::class, TipTapHTMLObserver::class])]
#[CollectedBy(NestableCollection::class)]
#[Unguarded]
class Page extends Model
{
    use HasAdminUrls;
    use HasBodyPresenter;
    use HasConfigurableOrder;
    use HasContentPresenter;
    use HasFiles;
    use HasOgImage;
    use HasSelectableFields;
    use HasSlugScope;
    use HasTranslations;
    use Historable;
    use Navigable;
    use Publishable;

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'is_home' => 'boolean',
            'private' => 'boolean',
            'redirect' => 'boolean',
        ];
    }

    /** @var array<string> */
    public array $translatable = [
        'title',
        'slug',
        'uri',
        'status',
        'body',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /** @var array<string> */
    public array $tipTapContent = [
        'body',
    ];

    public function parentUri(string $locale): string
    {
        $parentUri = $this->translate('uri', $locale) ?: '/';
        $parentUri = explode('/', (string) $parentUri);
        array_pop($parentUri);

        return implode('/', $parentUri).'/';
    }

    public function metaTitle(): string
    {
        return $this->meta_title ?? $this->title ?? '';
    }

    public function path(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $uri = $this->translate('uri', $locale);
        if (mainLocale() !== $locale || config('typicms.main_locale_in_url')) {
            $uri = $uri ? $locale.'/'.$uri : $locale;
        }

        return $uri ?: '/';
    }

    public function url(?string $locale = null): string
    {
        return url($this->path($locale));
    }

    public function previewUrl(?string $locale = null): string
    {
        return (string) Uri::of($this->url($locale))->withQuery(['preview' => 'true']);
    }

    public function isHome(): bool
    {
        return (bool) $this->is_home;
    }

    public function isPrivate(): bool
    {
        return (bool) $this->private;
    }

    /** @param Builder<Model> $query */
    #[Scope]
    protected function whereUriIs(Builder $query, string $uri): void
    {
        $field = 'uri';
        if (in_array($field, $this->translatable ?? [], true)) {
            $field .= '->'.app()->getLocale();
        }

        $query->where($field, $uri);
    }

    /** @param Builder<Model> $query */
    #[Scope]
    protected function whereUriIsNot(Builder $query, string $uri): void
    {
        $field = 'uri';
        if (in_array($field, $this->translatable ?? [], true)) {
            $field .= '->'.app()->getLocale();
        }

        $query->where($field, '!=', $uri);
    }

    /** @param Builder<Model> $query */
    #[Scope]
    protected function whereUriIsLike(Builder $query, string $uri): void
    {
        $field = 'uri';
        if (in_array($field, $this->translatable ?? [], true)) {
            $field .= '->'.app()->getLocale();
        }

        $query->where($field, 'LIKE', $uri);
    }

    /** @return array<int|string, string> */
    public function allForSelect(bool $withPagesLinkedToAModule = true): array
    {
        $locale = in_array(app()->getLocale(), locales(), true) ? app()->getLocale() : mainLocale();

        $pages = static::query()
            ->order()
            ->when(! $withPagesLinkedToAModule, function (Builder $query): void {
                $query->whereNull('module');
            })
            ->get()
            ->each(fn (self $page) => $page->setLocale($locale))
            ->nest()
            ->listsFlattened();

        return ['' => ''] + array_map(strip_tags(...), $pages);
    }

    /** @return NestableCollection<int, Page> */
    public function getSubPages(): NestableCollection
    {
        $rootUriArray = explode('/', $this->uri);
        $uri = $rootUriArray[0];
        if (in_array($uri, locales(), true) && isset($rootUriArray[1])) {
            $uri .= '/'.$rootUriArray[1];
            // add next part of uri in locale
        }

        return self::query()
            ->whereUriIsNot($uri)
            ->published()
            ->orderBy('position')
            ->whereUriIsLike($uri.'%')
            ->get()
            ->noCleaning()
            ->nest();
    }

    /** @return HasMany<PageSection, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->order();
    }

    /** @return HasMany<PageSection, $this> */
    public function publishedSections(): HasMany
    {
        return $this->sections()->published();
    }

    /** @return HasMany<Menulink, $this> */
    public function menulinks(): HasMany
    {
        return $this->hasMany(Menulink::class);
    }

    /** @return HasMany<Page, $this> */
    public function subpages(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->order();
    }

    /** @return HasMany<Page, $this> */
    public function publishedSubpages(): HasMany
    {
        return $this->subpages()->published();
    }

    /** @return BelongsTo<Page, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return BelongsTo<File, $this> */
    public function image(): BelongsTo
    {
        return $this->belongsTo(File::class, 'image_id');
    }

    /** @return BelongsTo<File, $this> */
    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(File::class, 'og_image_id');
    }
}
