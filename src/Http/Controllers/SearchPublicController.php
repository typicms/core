<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

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
        $words = array_filter(explode(' ', $data['query']));

        foreach ($config as $key => $data) {
            if (! is_array($data)) {
                continue;
            }

            $model = resolve($data['model']);
            $columns = $data['columns'];
            $query = $model
                ->query()
                ->where(function ($query) use ($words, $columns, $model, $key): void {
                    foreach ($columns as $column) {
                        $query->orWhere(function ($query) use ($words, $column, $model): void {
                            foreach ($words as $word) {
                                if (in_array($column, (array) $model->translatable, true)) {
                                    $query
                                        ->published()
                                        ->whereRaw(
                                            'JSON_UNQUOTE(JSON_EXTRACT(`'
                                            .$column
                                            .'`, ?)) LIKE ? COLLATE utf8mb4_unicode_ci',
                                            ['$.'.app()->getLocale(), '%'.$word.'%'],
                                        );
                                } else {
                                    $query
                                        ->published()
                                        ->whereRaw(
                                            '`'.$column.'` LIKE ? COLLATE utf8mb4_unicode_ci',
                                            ['%'.$word.'%'],
                                        );
                                }
                            }
                        });
                        if ($key === 'pages') { // search in page sections
                            $query->orWhere(function ($query) use ($words, $column, $model): void {
                                $query->published();
                                $query->whereHas('sections', function ($query) use ($words, $column, $model): void {
                                    foreach ($words as $word) {
                                        if (in_array($column, (array) $model->translatable, true)) {
                                            $query
                                                ->published()
                                                ->whereRaw(
                                                    'JSON_UNQUOTE(JSON_EXTRACT(`'
                                                    .$column
                                                    .'`, ?)) LIKE ? COLLATE utf8mb4_unicode_ci',
                                                    ['$.'.app()->getLocale(), '%'.$word.'%'],
                                                );
                                        } else {
                                            $query
                                                ->published()
                                                ->whereRaw(
                                                    '`'.$column.'` LIKE ? COLLATE utf8mb4_unicode_ci',
                                                    ['%'.$word.'%'],
                                                );
                                        }
                                    }
                                });
                            });
                        }
                    }
                });
            $items = $query->order()->get();
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
}
