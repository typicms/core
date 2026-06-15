<?php

declare(strict_types=1);

use TypiCMS\Modules\Core\Models\User;

it('sanitizes a malicious welcome message before rendering it on the dashboard', function (): void {
    $this->withoutVite();

    $viewer = User::factory()->create();
    $viewer->givePermissionTo('see dashboard');

    $payload = '<img src=x onerror="document.body.setAttribute(\'data-settings-xss\',\'1\')">';
    config(['typicms.welcome_message' => $payload, 'typicms.welcome_message_url' => '']);

    $this->actingAs($viewer)
        ->get(route('admin::dashboard'))
        ->assertOk()
        ->assertDontSee('onerror', false)
        ->assertDontSee('data-settings-xss', false);
});

it('keeps safe formatting in the welcome message', function (): void {
    $this->withoutVite();

    $viewer = User::factory()->create();
    $viewer->givePermissionTo('see dashboard');

    config([
        'typicms.welcome_message' => '<p>Welcome <strong>back</strong></p>',
        'typicms.welcome_message_url' => '',
    ]);

    $this->actingAs($viewer)
        ->get(route('admin::dashboard'))
        ->assertOk()
        ->assertSee('<strong>back</strong>', false);
});
