<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPasskeys\Http\Controllers\GeneratePasskeyAuthenticationOptionsController;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;
use TypiCMS\Modules\Core\Http\Controllers\AuthController;
use TypiCMS\Modules\Core\Http\Controllers\AuthenticateUsingPasskeyController;
use TypiCMS\Modules\Core\Http\Controllers\ImpersonateController;
use TypiCMS\Modules\Core\Http\Controllers\PasskeysApiController;
use TypiCMS\Modules\Core\Http\Controllers\ProfileController;
use TypiCMS\Modules\Core\Http\Controllers\RegisterController;
use TypiCMS\Modules\Core\Http\Controllers\UsersAdminController;
use TypiCMS\Modules\Core\Http\Controllers\UsersApiController;
use TypiCMS\Modules\Core\Http\Middleware\JavaScriptData;

/*
 * Front office routes
 */
foreach (locales() as $lang) {
    Route::middleware(['public', JavaScriptData::class, DoNotCacheResponse::class])
        ->prefix($lang)
        ->name($lang.'::')
        ->group(function (Router $router): void {
            if (config('typicms.registration.allowed')) {
                // Registration
                $router
                    ->middleware(['guest'])
                    ->get('register', [RegisterController::class, 'showRegistrationForm'])
                    ->name('register');
                $router
                    ->middleware(['guest'])
                    ->post('register', [RegisterController::class, 'register'])
                    ->name('register-action')
                    ->middleware('throttle:5,1');
            }

            $router
                ->middleware(['guest'])
                ->group(function (Router $router): void {
                    // Login with a passkey
                    $router->get('login', [AuthController::class, 'showPasskeyLoginForm'])->name('login');
                    // Login with a one-time password
                    $router->get('otp-login', [AuthController::class, 'showOneTimePasswordLoginForm'])->name(
                        'otp-login',
                    );
                    $router
                        ->post('otp-login', [AuthController::class, 'submitOneTimePasswordLoginForm'])
                        ->name('send-one-time-password')
                        ->middleware('throttle:5,1');
                    $router->get('otp-login-code', [AuthController::class, 'showOneTimePasswordForm'])->name(
                        'login-code',
                    );
                    $router
                        ->post('otp-login-code', [AuthController::class, 'submitOneTimePassword'])
                        ->name('submit-one-time-password')
                        ->middleware('throttle:5,1');
                });
            $router
                ->middleware(['auth'])
                ->group(function (Router $router): void {
                    // Require passkey creation
                    $router->get('create-passkey', [AuthController::class, 'showPasskeyCreationForm'])->name(
                        'create-passkey',
                    );
                });
            // Logout
            $router->post('logout', [AuthController::class, 'logout'])->name('logout');
            // Impersonate
            $router->get('stop-impersonation', [ImpersonateController::class, 'stopImpersonation'])->name(
                'stop-impersonation',
            );
        });
}

Route::middleware(['web', DoNotCacheResponse::class])->group(function (Router $router): void {
    Route::prefix('passkeys')->group(function (): void {
        Route::get('authentication-options', GeneratePasskeyAuthenticationOptionsController::class)->name(
            'passkeys.authentication_options',
        );
        Route::post('authenticate', AuthenticateUsingPasskeyController::class)->name('passkeys.login');
    });
});

/*
 * Admin routes
 */
Route::middleware('admin')
    ->prefix('admin')
    ->name('admin::')
    ->group(function (Router $router): void {
        $router
            ->get('users', [UsersAdminController::class, 'index'])
            ->name('index-users')
            ->middleware('can:read users');
        $router
            ->get('users/export', [UsersAdminController::class, 'export'])
            ->name('export-users')
            ->middleware('can:read users');
        $router
            ->get('users/create', [UsersAdminController::class, 'create'])
            ->name('create-user')
            ->middleware('can:create users');
        $router
            ->get('users/{user}/edit', [UsersAdminController::class, 'edit'])
            ->name('edit-user')
            ->middleware('can:read users');
        $router
            ->post('users', [UsersAdminController::class, 'store'])
            ->name('store-user')
            ->middleware('can:create users');
        $router
            ->put('users/{user}', [UsersAdminController::class, 'update'])
            ->name('update-user')
            ->middleware('can:update users');
        $router
            ->get('users/{id}/impersonate', [ImpersonateController::class, 'start'])
            ->name('impersonate-user')
            ->middleware('can:impersonate users');
        $router
            ->get('profile', [ProfileController::class, 'edit'])
            ->name('profile')
            ->middleware('can:edit profile');
        $router
            ->put('profile', [ProfileController::class, 'update'])
            ->name('update-profile')
            ->middleware('can:edit profile');
    });

/*
 * API routes
 */
Route::middleware(['api', 'auth:api'])->prefix('api')->group(function (Router $router): void {
    $router->get('users', [UsersApiController::class, 'index'])->middleware('can:read users');
    $router
        ->post('users/current/update-preferences', [UsersApiController::class, 'updatePreferences'])
        ->middleware('can:update users');
    $router->delete('users/{user}', [UsersApiController::class, 'destroy'])->middleware('can:delete users');
    // Passkeys API routes
    $router->delete('passkeys/{passkey}', [PasskeysApiController::class, 'destroy']);
    $router->post('passkeys', [PasskeysApiController::class, 'store']);
    $router->get('passkeys/generate-options', [PasskeysApiController::class, 'generatePasskeyOptions']);
    $router->get('users/{user}/passkeys', [PasskeysApiController::class, 'getPasskeys']);
});
