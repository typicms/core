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
        return view('public::users.register');
    }

    public function register(UserRegistrationFormRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (User::query()->where('email', $data['email'])->exists()) {
            return to_route(app()->getLocale().'::login')
                ->with('status', __('An account already exists for this email address.'));
        }

        if (config('typicms.registration.activated')) {
            $data['activated'] = true;
        }

        $user = User::query()->create($data);

        $roleName = config('typicms.registration.role');
        if ($roleName && Role::query()->where('name', $roleName)->exists()) {
            $user->assignRole($roleName);
        }

        session(['email' => $user->email]);

        $user->sendOneTimePassword();

        event(new Registered($user));

        return to_route(app()->getLocale().'::login-code')
            ->with('status', __('Your account has been created, check your email for the one time login code.'));
    }
}
