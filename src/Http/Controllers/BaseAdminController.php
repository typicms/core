<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

abstract class BaseAdminController extends Controller
{
    protected function redirect(Request $request, mixed $model): RedirectResponse
    {
        if (is_array($model)) {
            $model = end($model);
        }

        $redirectUrl = $request->input('exit') ? $model->indexUrl() : $model->editUrl();

        return redirect()->to($redirectUrl);
    }
}
