<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use TypiCMS\Modules\Core\Http\Requests\RolesFormRequest;
use TypiCMS\Modules\Core\Models\Role;

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
        $checkedPermissions = $request->array('checked_permissions');
        $data = $request->except(['exit', 'checked_permissions']);

        $this->storeNewPermissions($checkedPermissions);

        $role = Role::query()->create($data);
        $role->syncPermissions($checkedPermissions);

        return $this->redirect($request, $role);
    }

    public function update(Role $role, RolesFormRequest $request): RedirectResponse
    {
        $checkedPermissions = $request->array('checked_permissions');
        $data = $request->except(['exit', 'checked_permissions']);
        $role->update($data);

        $this->storeNewPermissions($checkedPermissions);
        $role->syncPermissions($checkedPermissions);
        $role->forgetCachedPermissions();

        return $this->redirect($request, $role);
    }

    /**
     * @param  array<string>  $permissions
     */
    private function storeNewPermissions(array $permissions): void
    {
        foreach ($permissions as $name) {
            Permission::query()->firstOrCreate(['name' => $name]);
        }
    }
}
