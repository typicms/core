<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Spatie\ResponseCache\Facades\ResponseCache;
use stdClass;
use TypiCMS\Modules\Core\Models\Setting;

final class SettingsAdminController extends BaseAdminController
{
    public function index(): View
    {
        $data = new stdClass;
        foreach (Setting::query()->get() as $model) {
            $value = is_numeric($model->value) ? (int) $model->value : $model->value;
            $group_name = $model->group_name;
            $key_name = $model->key_name;
            if ($group_name !== 'config') {
                if (! isset($data->{$group_name})) {
                    $data->{$group_name} = new stdClass;
                }

                $data->{$group_name}->{$key_name} = $value;
            } else {
                $data->{$key_name} = $value;
            }
        }

        return view('admin::settings.index', ['data' => $data]);
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->except('_token');

        foreach ($data as $group_name => $array) {
            if (! is_array($array)) {
                $array = [$group_name => $array];
                $group_name = 'config';
            }

            foreach ($array as $key_name => $value) {
                $model = Setting::query()->firstOrCreate(['key_name' => $key_name, 'group_name' => $group_name]);
                $model->value = is_scalar($value) ? (string) $value : null;
                $model->save();
            }
        }

        return to_route('admin::index-settings');
    }

    public function clearCache(): RedirectResponse
    {
        Artisan::call('cache:clear');
        if (config('responsecache.enabled')) {
            ResponseCache::clear();
        }

        $message = __('Cache cleared.');

        return to_route('admin::index-settings')->with(['message' => $message]);
    }
}
