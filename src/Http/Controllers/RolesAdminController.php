<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use TypiCMS\Modules\Core\Http\Requests\RolesFormRequest;
use TypiCMS\Modules\Core\Models\Role;
use TypiCMS\Modules\Core\Support\Permissions;

final class RolesAdminController extends BaseAdminController
{
    public function index(): View
    {
        return view('admin::roles.index');
    }

    public function create(): View
    {
        $model = new Role;
        $checkedPermissions = [];

        return view('admin::roles.create', ['model' => $model, 'checkedPermissions' => $checkedPermissions]);
    }

    public function edit(Role $role): View
    {
        $checkedPermissions = $role->permissions()->pluck('name')->all();

        return view('admin::roles.edit', ['model' => $role, 'checkedPermissions' => $checkedPermissions]);
    }

    public function store(RolesFormRequest $request): RedirectResponse
    {
        Permissions::sync();

        $role = Role::query()->create(['name' => $request->validated('name')]);
        $role->syncPermissions($request->checkedPermissions());

        return $this->redirect($request, $role);
    }

    public function update(Role $role, RolesFormRequest $request): RedirectResponse
    {
        Permissions::sync();

        $role->update(['name' => $request->validated('name')]);
        $role->syncPermissions($request->checkedPermissions());
        $role->forgetCachedPermissions();

        return $this->redirect($request, $role);
    }
}
