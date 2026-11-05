<?php

use TypiCMS\Modules\Core\Models\User;

describe('non-superuser with “delete users” permission', function (): void {
    test('cannot delete a superuser via the api', function (): void {
        $actor = User::factory()->create(['superuser' => false]);
        $actor->givePermissionTo('delete users');

        $superuser = User::factory()->create(['superuser' => true]);

        $this->actingAs($actor, 'api')
            ->deleteJson("/api/users/{$superuser->id}")
            ->assertForbidden();

        $this->assertModelExists($superuser);
    });

    test('can still delete a regular user via the api', function (): void {
        $actor = User::factory()->create(['superuser' => false]);
        $actor->givePermissionTo('delete users');

        $target = User::factory()->create(['superuser' => false]);

        $this->actingAs($actor, 'api')
            ->deleteJson("/api/users/{$target->id}")
            ->assertNoContent();

        $this->assertModelMissing($target);
    });
});

test('a superuser can delete another superuser via the api', function (): void {
    $actor = User::factory()->create(['superuser' => true]);

    $superuser = User::factory()->create(['superuser' => true]);

    $this->actingAs($actor, 'api')
        ->deleteJson("/api/users/{$superuser->id}")
        ->assertNoContent();

    $this->assertModelMissing($superuser);
});
