<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

final class SearchPublicController extends BasePublicController
{
    public function search(Request $request): View
    {
        $results = collect();
        $tabs = [];
        $count = 0;
        $data = [];
        $data['query'] = e((string) $request->string('query'));
        $validator = Validator::make($data, [
            'query' => ['required', 'string', 'min:3'],
        ]);
        if ($validator->fails()) {
            return view('public::search.index', ['results' => $results, 'count' => $count])->withErrors($validator);
        }

        $config = config('typicms.search');
        if (! is_array($config)) {
            $config = [];
        }
        $words = array_filter(explode(' ', $data['query']));

        foreach ($config as $key => $data) {
            if (! is_array($data)) {
                continue;
            }

            $modelClass = $data['model'] ?? null;
            $columns = $data['columns'] ?? null;
            if (! is_string($modelClass) || ! is_array($columns)) {
                continue;
            }

            if (! is_subclass_of($modelClass, Model::class)) {
                continue;
            }

            $columns = array_filter($columns, is_string(...));
            $model = new $modelClass;
            $query = $model->newQuery()->where(function (Builder $query) use ($words, $columns, $model, $key): void {
                foreach ($columns as $column) {
                    $query->orWhere(function (Builder $query) use ($words, $column, $model): void {
                        foreach ($words as $word) {
                            $this->whereWordMatches($query, $model, $column, $word);
                        }
                    });
                    if ($key === 'pages') { // search in page sections
                        $query->orWhere(function (Builder $query) use ($words, $column, $model): void {
                            $query->scopes(['published']);
                            $query->whereHas('sections', function (Builder $query) use ($words, $column, $model): void {
                                foreach ($words as $word) {
                                    $this->whereWordMatches($query, $model, $column, $word);
                                }
                            });
                        });
                    }
                }
            });
            $items = $query->tap(fn (Builder $query) => $query->scopes(['order']))->get();
            $numberOfItems = $items->count();
            if ($numberOfItems) {
                $tabs[] = ['module' => $key, 'count' => $numberOfItems];
                $results[] = ['module' => $key, 'models' => $items];
                $count += $numberOfItems;
            }
        }

        return view('public::search.index', ['results' => $results, 'count' => $count, 'tabs' => $tabs])->withErrors(
            $validator,
        );
    }

    /** @param Builder<Model> $query */
    private function whereWordMatches(Builder $query, Model $model, string $column, string $word): void
    {
        $query->scopes(['published']);

        $isTranslatable = method_exists($model, 'isTranslatableAttribute') && $model->isTranslatableAttribute($column) === true;

        if ($isTranslatable) {
            $query->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(`{$column}`, ?)) LIKE ? COLLATE utf8mb4_unicode_ci",
                ['$.'.app()->getLocale(), "%{$word}%"],
            );

            return;
        }

        $query->whereRaw("`{$column}` LIKE ? COLLATE utf8mb4_unicode_ci", ["%{$word}%"]);
    }
}
