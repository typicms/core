<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use TypiCMS\Modules\Core\Http\Requests\ProfileFormRequest;
use TypiCMS\Modules\Core\Models\User;

final class ProfileController extends BaseAdminController
{
    public function edit(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $passkeys = $user->passkeys()->get(['id', 'name', 'last_used_at']);

        return view('admin::users.profile', [
            'model' => $user,
            'passkeys' => $passkeys,
        ]);
    }

    public function update(ProfileFormRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->update($request->validated());

        return to_route('admin::profile')->with('success', __('Profile updated.'));
    }
}
