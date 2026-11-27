<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use TypiCMS\Modules\Core\Http\Requests\UserRegistrationFormRequest;
use TypiCMS\Modules\Core\Models\Role;
use TypiCMS\Modules\Core\Models\User;

final class RegisterController extends Controller
{
    public function showRegistrationForm(): View
    {
        return view('public.users.register');
    }

    public function register(UserRegistrationFormRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $existingUser = User::query()->where('email', $data['email'])->first();

        if ($existingUser) {
            return $this->redirectToLoginCode($existingUser);
        }

        if (config('typicms.registration.activated')) {
            $data['activated'] = true;
        }

        $user = User::query()->create($data);

        $roleName = config('typicms.registration.role');
        if (is_string($roleName) && $roleName !== '' && Role::query()->where('name', $roleName)->exists()) {
            $user->assignRole($roleName);
        }

        event(new Registered($user));

        return $this->redirectToLoginCode($user);
    }

    /**
     * Existing and new accounts get the same response, so registration
     * cannot be used to find out which addresses have an account.
     */
    private function redirectToLoginCode(User $user): RedirectResponse
    {
        session(['email' => $user->email]);

        if ($user->wasRecentlyCreated || $user->activated) {
            $user->sendOneTimePassword();
        }

        return to_route(app()->getLocale().'::login-code')
            ->with('status', __('A one-time password has been sent to your email address.'));
    }
}
