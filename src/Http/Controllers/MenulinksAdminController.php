<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use TypiCMS\Modules\Core\Http\Requests\MenulinkFormRequest;
use TypiCMS\Modules\Core\Models\Menu;
use TypiCMS\Modules\Core\Models\Menulink;

final class MenulinksAdminController extends BaseAdminController
{
    public function create(Menu $menu): View
    {
        $model = new Menulink;

        return view('admin::menus.create-menulink', ['model' => $model, 'menu' => $menu]);
    }

    public function edit(Menu $menu, Menulink $menulink): View
    {
        return view('admin::menus.edit-menulink', [
            'menu' => $menu,
            'model' => $menulink,
        ]);
    }

    public function store(Menu $menu, MenulinkFormRequest $request): RedirectResponse
    {
        $menulink = Menulink::query()->create($request->validated());

        return $this->redirect($request, $menulink)->withMessage(__('Item successfully created.'));
    }

    public function update(Menu $menu, Menulink $menulink, MenulinkFormRequest $request): RedirectResponse
    {
        $menulink->update($request->validated());

        return $this->redirect($request, $menulink)->withMessage(__('Item successfully updated.'));
    }
}
