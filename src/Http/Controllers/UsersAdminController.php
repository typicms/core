<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TypiCMS\Modules\Core\Exports\UsersExport;
use TypiCMS\Modules\Core\Http\Requests\UsersFormRequest;
use TypiCMS\Modules\Core\Models\Role;
use TypiCMS\Modules\Core\Models\User;

final class UsersAdminController extends BaseAdminController
{
    public function index(): View
    {
        return view('admin::users.index');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filename = date('Y-m-d').' '.config('app.name').' users.xlsx';

        return Excel::download(new UsersExport, $filename);
    }

    public function create(): View
    {
        $model = new User;
        $checkedRoles = [];
        $roles = Role::query()->get();
        $passkeys = collect();

        return view('admin::users.create', [
            'model' => $model,
            'roles' => $roles,
            'checkedRoles' => $checkedRoles,
            'passkeys' => $passkeys,
        ]);
    }

    public function edit(User $user): View
    {
        $checkedRoles = $user->roles()->pluck('id')->all();
        $roles = Role::query()->get();
        $passkeys = $user->passkeys()->get(['id', 'name', 'last_used_at']);

        return view('admin::users.edit', [
            'model' => $user,
            'roles' => $roles,
            'checkedRoles' => $checkedRoles,
            'passkeys' => $passkeys,
        ]);
    }

    public function store(UsersFormRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = User::query()->create($data);
        $user->roles()->sync($request->array('checked_roles'));

        return $this->redirect($request, $user);
    }

    public function update(User $user, UsersFormRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user->update($data);
        $user->roles()->sync($request->array('checked_roles'));

        return $this->redirect($request, $user);
    }
}
