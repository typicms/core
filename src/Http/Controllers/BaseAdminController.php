<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use InvalidArgumentException;

abstract class BaseAdminController extends Controller
{
    protected function redirect(Request $request, mixed $model): RedirectResponse
    {
        if (is_array($model)) {
            $model = end($model);
        }

        if (! is_object($model) || ! method_exists($model, 'indexUrl') || ! method_exists($model, 'editUrl')) {
            throw new InvalidArgumentException('The model must have admin URLs.');
        }

        $redirectUrl = $request->input('exit') ? $model->indexUrl() : $model->editUrl();

        if (! is_string($redirectUrl)) {
            throw new InvalidArgumentException('The admin URL must be a string.');
        }

        return redirect()->to($redirectUrl);
    }
}
