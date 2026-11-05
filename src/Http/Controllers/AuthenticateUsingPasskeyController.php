<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Override;
use Spatie\LaravelPasskeys\Http\Controllers\AuthenticateUsingPasskeyController as BaseController;
use Spatie\LaravelPasskeys\Http\Requests\AuthenticateUsingPasskeysRequest;
use UnexpectedValueException;

final class AuthenticateUsingPasskeyController extends BaseController
{
    #[Override]
    public function __invoke(AuthenticateUsingPasskeysRequest $request): RedirectResponse
    {
        $result = parent::__invoke($request);

        $user = auth()->user();

        if ($user && ! $user->activated) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            session()->flash('authenticatePasskey::message', __('Your account is not activated.'));

            return back();
        }

        if (! $result instanceof RedirectResponse) {
            throw new UnexpectedValueException('The passkey authentication must return a redirect.');
        }

        return $result;
    }
}
