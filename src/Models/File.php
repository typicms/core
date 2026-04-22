<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Models;

use Bkwld\Croppa\Facades\Croppa;
use Exception;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use TypiCMS\Modules\Core\Traits\HasAdminUrls;
use TypiCMS\Modules\Core\Traits\HasConfigurableOrder;
use TypiCMS\Modules\Core\Traits\HasContentPresenter;
use TypiCMS\Modules\Core\Traits\HasSelectableFields;
use TypiCMS\Modules\Core\Traits\HasSlugScope;
use TypiCMS\Modules\Core\Traits\Historable;
use TypiCMS\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int|null $folder_id
 * @property string|null $type
 * @property string|null $name
 * @property string|null $path
 * @property string|null $extension
 * @property string|null $mimetype
 * @property int|null $width
 * @property int|null $height
 * @property int|null $filesize
 * @property string $title
 * @property string $description
 * @property string $alt_attribute
 * @property int|null $focal_x
 * @property int|null $focal_y
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, File> $children
 * @property-read int|null $children_count
 * @property-read File|null $folder
 * @property-read Collection<int, History> $history
 * @property-read int|null $history_count
 * @property-write mixed $status
 * @property-read mixed $thumb_sm
 * @property-read mixed $translations
 * @property-read mixed $storage_url
 */
#[Unguarded]
#[Appends(['thumb_sm', 'storage_url'])]
class File extends Model
{
    use HasAdminUrls;
    use HasConfigurableOrder;
    use HasContentPresenter;
    use HasSelectableFields;
    use HasSlugScope;
    use HasTranslations;
    use Historable;

    /** @var array<string> */
    public array $translatable = [
        'title',
        'description',
        'alt_attribute',
    ];

    protected string $imageNotFound = 'img-not-found.png';

    public function presentTitle(): string
    {
        return $this->title ?: ($this->name ?? '');
    }

    public function formattedFilesize(int $precision = 0): string
    {
        $base = log((float) ($this->filesize ?? 0), 1024);
        $suffixes = ['', __('KB'), __('MB'), __('GB'), __('TB')];

        return round(1024 ** ($base - floor($base)), $precision).' '.$suffixes[(int) floor($base)];
    }

    protected function getImagePathOrDefault(): string
    {
        if (! $this->path || ! Storage::exists($this->path)) {
            return $this->imgNotFound();
        }

        return $this->path;
    }

    /**
     * Return URL of a resized or cropped image.
     *
     * @param  array<string|int, string|array<string>>  $options
     */
    public function render(
        ?int $width = null,
        ?int $height = null,
        array $options = [],
    ): string {
        $path = $this->getImagePathOrDefault();

        if (in_array(pathinfo($path, PATHINFO_EXTENSION), ['svg', 'gif'], true)) {
            return Storage::url($path);
        }

        return url(Croppa::url('storage/'.$path, $width, $height, $options));
    }

    public function imgNotFound(): string
    {
        if (! Storage::exists($this->imageNotFound)) {
            Storage::put($this->imageNotFound, \Illuminate\Support\Facades\File::get(resource_path('images/'.$this->imageNotFound)));
        }

        return $this->imageNotFound;
    }

    /** @return Attribute<string, null> */
    protected function thumbSm(): Attribute
    {
        return Attribute::make(get: fn (): string => $this->render(240, 240, ['resize']));
    }

    /** @return Attribute<string, null> */
    protected function storageUrl(): Attribute
    {
        $url = '';

        try {
            $url = Storage::url($this->path ?? '');
        } catch (Exception) {
        }

        return Attribute::make(get: fn () => $url);
    }

    /** @return BelongsTo<File, $this> */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(self::class, 'folder_id', 'id');
    }

    /** @return HasMany<File, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'folder_id', 'id');
    }
}
