<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;
use Spatie\LaravelPasskeys\Actions\StorePasskeyAction;
use Spatie\LaravelPasskeys\Models\Passkey;
use Spatie\LaravelPasskeys\Support\Config;
use Throwable;
use TypiCMS\Modules\Core\Models\User;
use Webauthn\PublicKeyCredentialCreationOptions;

final class PasskeysApiController extends BaseApiController
{
    public function getPasskeys(User $user, #[CurrentUser] User $currentUser): JsonResponse
    {
        $this->authorizePasskeyAccess($currentUser, $user);

        $passkeys = $user->passkeys()->get(['id', 'name', 'last_used_at']);

        return response()->json($passkeys);
    }

    public function store(Request $request, #[CurrentUser] User $user): void
    {
        $this->authorizePasskeyAccess($user);

        $storePasskeyAction = Config::getAction('store_passkey', StorePasskeyAction::class);

        try {
            $storePasskeyAction->execute(
                $user,
                $request->passkey,
                $request->options ?? $this->previouslyGeneratedPasskeyOptions(),
                request()->getHost(),
                ['name' => (string) $request->string('name')],
            );
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'name' => __('passkeys::passkeys.error_something_went_wrong_generating_the_passkey'),
            ])->errorBag('passkeyForm');
        }
    }

    public function destroy(Passkey $passkey, #[CurrentUser] User $currentUser): void
    {
        /** @var User $user */
        $user = User::query()->findOrFail($passkey->getAttribute('authenticatable_id'));
        $this->authorizePasskeyAccess($currentUser, $user);

        $passkey->delete();
    }

    public function generatePasskeyOptions(#[CurrentUser] User $user): string|PublicKeyCredentialCreationOptions
    {
        $this->authorizePasskeyAccess($user);

        $generatePassKeyOptionsAction = Config::getAction(
            'generate_passkey_register_options',
            GeneratePasskeyRegisterOptionsAction::class,
        );

        $options = $generatePassKeyOptionsAction->execute($user);

        session()->put('passkey-registration-options', $options);

        return $options;
    }

    protected function previouslyGeneratedPasskeyOptions(): ?string
    {
        return session()->pull('passkey-registration-options');
    }

    /**
     * @throws AuthorizationException
     */
    private function authorizePasskeyAccess(User $currentUser, ?User $user = null): void
    {
        $isOwnPasskey = ! $user instanceof User || $user->id === $currentUser->id;

        if ($currentUser->can('edit profile') && $isOwnPasskey) {
            return;
        }

        if ($user instanceof User && $user->isSuperUser() && ! $currentUser->isSuperUser()) {
            throw new AuthorizationException;
        }

        if ($currentUser->can('update users')) {
            return;
        }

        throw new AuthorizationException;
    }
}
