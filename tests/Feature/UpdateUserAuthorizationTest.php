<?php

use TypiCMS\Modules\Core\Models\User;

describe('non-superuser with “update users” permission', function (): void {
    test('cannot rewrite a superuser’s email', function (): void {
        $actor = User::factory()->create(['superuser' => false]);
        $actor->givePermissionTo('update users');

        $superuser = User::factory()->create([
            'superuser' => true,
            'email' => 'admin@gmail.com',
        ]);

        $this->actingAs($actor)
            ->put(route('admin::update-user', $superuser), [
                'email' => 'attacker@gmail.com',
                'first_name' => 'Root',
                'last_name' => 'Super',
                'locale' => 'en',
            ])
            ->assertForbidden();

        expect($superuser->refresh()->email)->toBe('admin@gmail.com');
    });

    test('can still update a regular user', function (): void {
        $actor = User::factory()->create(['superuser' => false]);
        $actor->givePermissionTo('update users');

        $target = User::factory()->create([
            'superuser' => false,
            'email' => 'member@gmail.com',
        ]);

        $this->actingAs($actor)
            ->put(route('admin::update-user', $target), [
                'email' => 'updated-member@gmail.com',
                'first_name' => 'Updated',
                'last_name' => 'Member',
                'locale' => 'en',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        expect($target->refresh()->email)->toBe('updated-member@gmail.com');
    });
});

describe('superuser', function (): void {
    test('can update another superuser', function (): void {
        $actor = User::factory()->create(['superuser' => true]);

        $superuser = User::factory()->create([
            'superuser' => true,
            'email' => 'admin@gmail.com',
        ]);

        $this->actingAs($actor)
            ->put(route('admin::update-user', $superuser), [
                'email' => 'admin-updated@gmail.com',
                'first_name' => 'Root',
                'last_name' => 'Super',
                'locale' => 'en',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        expect($superuser->refresh()->email)->toBe('admin-updated@gmail.com');
    });
});
