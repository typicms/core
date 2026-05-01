<?php

use Illuminate\Support\Str;
use Spatie\LaravelPasskeys\Models\Passkey;
use Symfony\Component\Uid\Uuid;
use TypiCMS\Modules\Core\Models\User;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\EmptyTrustPath;

function createTestUser(array $attributes = []): User
{
    return User::query()->create(array_merge([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => Str::random(10).'@example.com',
        'activated' => true,
    ], $attributes));
}

function createPasskeyForUser(User $user): Passkey
{
    return Passkey::query()->create([
        'name' => 'Test passkey',
        'authenticatable_id' => $user->id,
        'data' => new PublicKeyCredentialSource(
            base64_decode(
                'eHouz/Zi7+BmByHjJ/tx9h4a1WZsK4IzUmgGjkhyOodPGAyUqUp/B9yUkflXY3yHWsNtsrgCXQ3HjAIFUeZB+w==',
                true,
            ),
            PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
            [],
            'none',
            EmptyTrustPath::create(),
            Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            base64_decode(
                'pQECAyYgASFYIJV56vRrFusoDf9hm3iDmllcxxXzzKyO9WruKw4kWx7zIlgg/nq63l8IMJcIdKDJcXRh9hoz0L+nVwP1Oxil3/oNQYs=',
                true,
            ),
            'foo',
            100,
        ),
    ]);
}

test('create-passkey page is accessible for authenticated users without passkeys', function (): void {
    $user = User::query()->firstOrFail();
    $user->passkeys()->delete();

    $this->actingAs($user)->get('/en/create-passkey')->assertOk();
});

test('create-passkey page redirects to dashboard for users with passkeys', function (): void {
    $user = User::query()->firstOrFail();
    $user->passkeys()->delete();

    createPasskeyForUser($user);

    $this->actingAs($user)->get('/en/create-passkey')->assertRedirect(route('admin::dashboard'));
});

test('create-passkey page redirects guests to login', function (): void {
    $this->get('/en/create-passkey')->assertRedirect();
});

test('generate-options endpoint returns json for user with edit profile permission', function (): void {
    $user = User::query()->firstOrFail();
    $user->givePermissionTo('edit profile');

    $this->actingAs($user, 'api')->getJson('/api/passkeys/generate-options')->assertSuccessful();
});

test('generate-options endpoint rejects unauthenticated requests', function (): void {
    $this->getJson('/api/passkeys/generate-options')->assertUnauthorized();
});

test('generate-options endpoint rejects user without permission', function (): void {
    $user = createTestUser();

    $this->actingAs($user, 'api')->getJson('/api/passkeys/generate-options')->assertForbidden();
});

test('passkeys store endpoint rejects unauthenticated requests', function (): void {
    $this->postJson('/api/passkeys')->assertUnauthorized();
});

test('user with edit profile permission can delete their own passkey via api', function (): void {
    $user = User::query()->firstOrFail();
    $user->givePermissionTo('edit profile');

    $passkey = createPasskeyForUser($user);

    $this
        ->actingAs($user, 'api')
        ->deleteJson("/api/passkeys/{$passkey->id}")
        ->assertSuccessful();

    $this->assertDatabaseMissing(Passkey::class, ['id' => $passkey->id]);
});

test('user with edit profile permission cannot delete another users passkey', function (): void {
    $user = createTestUser();
    $user->givePermissionTo('edit profile');

    $otherUser = createTestUser();
    $passkey = createPasskeyForUser($otherUser);

    $this
        ->actingAs($user, 'api')
        ->deleteJson("/api/passkeys/{$passkey->id}")
        ->assertForbidden();

    $this->assertDatabaseHas(Passkey::class, ['id' => $passkey->id]);
});

test('user with edit profile permission can list their own passkeys via api', function (): void {
    $user = User::query()->firstOrFail();
    $user->givePermissionTo('edit profile');
    $user->passkeys()->delete();

    createPasskeyForUser($user);
    createPasskeyForUser($user);

    $response = $this->actingAs($user, 'api')->getJson("/api/users/{$user->id}/passkeys");

    $response->assertSuccessful();
    $response->assertJsonCount(2);
});

test('user with edit profile permission cannot list another users passkeys', function (): void {
    $user = createTestUser();
    $user->givePermissionTo('edit profile');

    $otherUser = createTestUser();
    createPasskeyForUser($otherUser);

    $this
        ->actingAs($user, 'api')
        ->getJson("/api/users/{$otherUser->id}/passkeys")
        ->assertForbidden();
});

test('user without permission cannot delete passkeys', function (): void {
    $user = createTestUser();

    $passkey = createPasskeyForUser($user);

    $this
        ->actingAs($user, 'api')
        ->deleteJson("/api/passkeys/{$passkey->id}")
        ->assertForbidden();

    $this->assertDatabaseHas(Passkey::class, ['id' => $passkey->id]);
});

test('user without permission cannot list passkeys', function (): void {
    $user = createTestUser();

    $this
        ->actingAs($user, 'api')
        ->getJson("/api/users/{$user->id}/passkeys")
        ->assertForbidden();
});
