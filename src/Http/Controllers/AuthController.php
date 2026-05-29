<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Spatie\OneTimePasswords\Rules\OneTimePasswordRule;
use TypiCMS\Modules\Core\Http\Requests\OneTimePasswordLoginRequest;
use TypiCMS\Modules\Core\Models\User;

final class AuthController extends Controller
{
    private ?string $email = null;

    public function showPasskeyLoginForm(): View
    {
        return view('public::users.login');
    }

    public function showOneTimePasswordLoginForm(): View
    {
        return view('public::users.login-otp');
    }

    public function showOneTimePasswordForm(): View
    {
        return view('public::users.login-code');
    }

    public function showPasskeyCreationForm(): View|RedirectResponse
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user && $user->passkeys->isNotEmpty()) {
            return to_route('admin::dashboard');
        }

        return view('public::users.create-passkey');
    }

    protected function submitOneTimePasswordLoginForm(OneTimePasswordLoginRequest $request): RedirectResponse
    {
        $this->email = (string) $request->string('email');
        $user = $this->findUserOrFail();

        if (! $user->activated) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('Your account is not activated.')]);
        }

        session(['email' => $this->email]);

        $user->sendOneTimePassword();

        $status = config('mail.default') === 'log'
            ? __('An e-mail with the one-time password has been sent to the Laravel log file.')
            : __('A one-time password has been sent to your email address.');

        return to_route(app()->getLocale().'::login-code')
            ->withInput($request->only('email'))
            ->with('status', $status);
    }

    public function submitOneTimePassword(Request $request): RedirectResponse
    {
        if (session()->missing('email')) {
            return to_route(app()->getLocale().'::otp-login')
                ->with('status', __('Please enter your email address first.'));
        }

        $this->email = session('email');
        session()->forget('email');
        $user = $this->findUserOrFail();

        if (! $user->activated) {
            return to_route(app()->getLocale().'::otp-login')->withErrors(['email' => __(
                'Your account is not activated.',
            )]);
        }

        Validator::make($request->only('one_time_password'), [
            'one_time_password' => ['required', new OneTimePasswordRule($user)],
        ])->validate();

        auth()->login($user);

        if ($user->passkeys->isEmpty()) {
            return to_route(app()->getLocale().'::create-passkey');
        }

        return to_route('admin::dashboard');
    }

    protected function findUserOrFail(): User
    {
        $authenticatableModel = config('auth.providers.users.model');

        return $authenticatableModel::query()->where('email', $this->email)->firstOrFail();
    }

    public function logout(Request $request): RedirectResponse|JsonResponse
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $request->wantsJson() ? new JsonResponse([], 204) : redirect('/');
    }

    protected function guard(): StatefulGuard|Guard
    {
        return Auth::guard();
    }
}
