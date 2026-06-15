<?php

use TypiCMS\Modules\Core\Models\User;

describe('non-superuser with “update users” permission', function (): void {
    test('cannot self-escalate via a query-string superuser flag', function (): void {
        $actor = User::factory()->create(['superuser' => false]);
        $actor->givePermissionTo('update users');

        $this->actingAs($actor)
            ->put(route('admin::update-user', $actor).'?superuser=1', [
                'email' => $actor->email,
                'first_name' => 'X',
                'last_name' => 'Y',
                'locale' => 'en',
                'superuser' => '0',
            ])
            ->assertRedirect();

        expect($actor->refresh()->isSuperUser())->toBeFalse();
    });

    test('cannot self-escalate via a body superuser flag', function (): void {
        $actor = User::factory()->create(['superuser' => false]);
        $actor->givePermissionTo('update users');

        $this->actingAs($actor)
            ->put(route('admin::update-user', $actor), [
                'email' => $actor->email,
                'first_name' => 'X',
                'last_name' => 'Y',
                'locale' => 'en',
                'superuser' => '1',
            ])
            ->assertRedirect();

        expect($actor->refresh()->isSuperUser())->toBeFalse();
    });
});

describe('superuser with “update users” permission', function (): void {
    test('can still grant the superuser flag', function (): void {
        $actor = User::factory()->create(['superuser' => true]);

        $target = User::factory()->create(['superuser' => false]);

        $this->actingAs($actor)
            ->put(route('admin::update-user', $target), [
                'email' => $target->email,
                'first_name' => 'X',
                'last_name' => 'Y',
                'locale' => 'en',
                'superuser' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        expect($target->refresh()->isSuperUser())->toBeTrue();
    });
});
