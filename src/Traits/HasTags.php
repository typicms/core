<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use TypiCMS\Modules\Core\Models\Tag;

trait HasTags
{
    public static function bootHasTags(): void
    {
        static::saved(function (self $model): void {
            if (request()->has('tags')) {
                $tags = array_filter(array_map(trim(...), explode(',', (string) request()->string('tags'))));

                // Create or add tags
                $tagIds = [];

                if ($tags !== []) {
                    $foundTags = Tag::query()->whereIn('tag', $tags)->get();

                    $returnTags = [];

                    foreach ($foundTags as $tag) {
                        $pos = array_search($tag->tag, $tags, true);

                        // Add returned tags to array
                        if ($pos !== false) {
                            $returnTags[] = $tag;
                            unset($tags[$pos]);
                        }
                    }

                    // Add remaining tags as new
                    foreach ($tags as $tag) {
                        $returnTags[] = Tag::query()->create([
                            'tag' => $tag,
                            'slug' => Str::slug($tag),
                        ]);
                    }

                    foreach ($returnTags as $tag) {
                        $tagIds[] = $tag->id;
                    }
                }

                // Assign tags to model
                $model->tags()->sync($tagIds);
            }
        });
    }

    /** @return MorphToMany<Tag, $this> */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->orderBy('tag')->withTimestamps();
    }
}
