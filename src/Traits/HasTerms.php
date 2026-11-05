<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use TypiCMS\Modules\Core\Models\Taxonomy;
use TypiCMS\Modules\Core\Models\Term;

trait HasTerms
{
    public static function bootHasTerms(): void
    {
        static::saved(function (self $model): void {
            if (request()->has('terms')) {
                $data = array_filter(Arr::flatten(request()->array('terms')));
                $model->terms()->sync($data);
            }
        });
    }

    /** @return MorphToMany<Term, $this> */
    public function terms(): MorphToMany
    {
        return $this->morphToMany(Term::class, 'model', 'model_has_terms')->orderBy('position')->withTimestamps();
    }

    /** @return Collection<int, Taxonomy> */
    public function getTaxonomies(): Collection
    {
        return Taxonomy::query()
            ->whereJsonContains('modules', $this->getTable())
            ->order()
            ->get();
    }

    /** @return array<string, string|null> */
    public function getTaxonomyValidationRules(): array
    {
        $taxonomies = $this->getTaxonomies();
        $rules = [];
        foreach ($taxonomies as $taxonomy) {
            $rules['terms.'.$taxonomy->name] = $taxonomy->validation_rule;
        }

        return $rules;
    }
}
