<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Override;
use TypiCMS\Modules\Core\Traits\HasConfigurableOrder;
use TypiCMS\Modules\Core\Traits\HasSelectableFields;
use TypiCMS\Modules\Core\Traits\HasSlugScope;

/**
 * @property int $id
 * @property int $historable_id
 * @property string|null $historable_type
 * @property int|null $user_id
 * @property string|null $title
 * @property string|null $locale
 * @property string $historable_table
 * @property string $action
 * @property object $old
 * @property object $new
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ?Model $historable
 * @property-read mixed $href
 * @property-write mixed $status
 * @property-read User|null $user
 */
#[Unguarded]
#[Appends(['href'])]
#[Table(name: 'history')]
class History extends Model
{
    use HasConfigurableOrder;
    use HasSelectableFields;
    use HasSlugScope;

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'old' => 'object',
            'new' => 'object',
        ];
    }

    public string $order = 'id';

    public string $direction = 'desc';

    /** @return MorphTo<Model, $this> */
    public function historable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Avoid "Class not found" when historable_type points at a missing class.
     */
    protected static function booted(): void
    {
        static::retrieved(function (self $history): void {
            $type = $history->getAttributes()['historable_type'] ?? null;

            if ($type === null) {
                return;
            }

            $class = Relation::getMorphedModel($type) ?? $type;

            if (! class_exists($class)) {
                $history->setRelation('historable', null);
            }
        });
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return Attribute<string, null> */
    protected function href(): Attribute
    {
        return Attribute::make(get: function () {
            if ($this->historable === null) {
                return null;
            }

            return method_exists($this->historable, 'editUrl') ? $this->historable->editUrl() : '';
        });
    }
}
