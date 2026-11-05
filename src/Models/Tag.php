<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Models;

use Exception;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use TypiCMS\Modules\Core\Traits\HasAdminUrls;
use TypiCMS\Modules\Core\Traits\HasConfigurableOrder;
use TypiCMS\Modules\Core\Traits\HasSelectableFields;
use TypiCMS\Modules\Core\Traits\HasSlugScope;
use TypiCMS\Modules\Core\Traits\Historable;

/**
 * @property int $id
 * @property string $tag
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, History> $history
 * @property-read int|null $history_count
 * @property-write mixed $status
 */
#[Unguarded]
class Tag extends Model
{
    use HasAdminUrls;
    use HasConfigurableOrder;
    use HasSelectableFields;
    use HasSlugScope;
    use Historable;

    public function presentTitle(): string
    {
        return $this->tag;
    }

    /** @param Builder<Tag> $query */
    #[Scope]
    protected function published(Builder $query): void {}

    /** @return array<string, Collection<int, Model>> */
    public function getTaggedItemsGrouped(): array
    {
        $taggables = DB::table('taggables')->where('tag_id', $this->id)->get();

        $grouped = [];

        foreach ($taggables as $taggable) {
            $modelClass = $taggable->taggable_type;

            if (! is_string($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
                continue;
            }

            try {
                $model = $modelClass::query()->find($taggable->taggable_id);
                if ($model instanceof Model) {
                    $type = $model->getTable();
                    if (! isset($grouped[$type])) {
                        $grouped[$type] = new Collection;
                    }

                    $grouped[$type]->push($model);
                }
            } catch (Exception) {
                continue;
            }
        }

        return $grouped;
    }
}
