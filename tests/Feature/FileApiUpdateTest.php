<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use TypiCMS\Modules\Core\Models\File;
use TypiCMS\Modules\Core\Models\User;

beforeEach(function (): void {
    Storage::fake('public');

    $this->image = File::query()->create([
        'type' => 'i',
        'name' => 'photo.jpg',
        'path' => 'files/photo.jpg',
        'extension' => 'jpg',
        'mimetype' => 'image/jpeg',
    ]);
});

test('the file api returns every translation of a file', function (): void {
    $this->image->update(['title' => ['en' => 'Sunset', 'fr' => 'Coucher de soleil']]);

    $this->actingAs(User::factory()->create(['superuser' => true]))
        ->getJson("/api/files/{$this->image->id}")
        ->assertOk()
        ->assertJsonPath('translations.title.en', 'Sunset')
        ->assertJsonPath('translations.title.fr', 'Coucher de soleil');
});

test('a file can be updated through the api', function (): void {
    $this->actingAs(User::factory()->create(['superuser' => true]))
        ->putJson("/api/files/{$this->image->id}", [
            'name' => 'sunset.jpg',
            'title' => ['en' => 'Sunset'],
            'alt_attribute' => ['en' => 'A sunset'],
            'focal_x' => 25,
            'focal_y' => 80,
        ])
        ->assertOk()
        ->assertJsonPath('translations.title.en', 'Sunset');

    expect($this->image->refresh())
        ->name->toBe('sunset.jpg')
        ->focal_x->toBe(25)
        ->focal_y->toBe(80)
        ->and($this->image->getTranslation('alt_attribute', 'en'))->toBe('A sunset');
});

test('the file of a file can be replaced through the api', function (): void {
    $this->actingAs(User::factory()->create(['superuser' => true]))
        ->post("/api/files/{$this->image->id}", [
            '_method' => 'PUT',
            'name' => 'photo.jpg',
            'file' => UploadedFile::fake()->image('landscape.png', 40, 30),
        ], ['Accept' => 'application/json'])
        ->assertOk();

    expect($this->image->refresh())
        ->name->toBe('landscape.png')
        ->extension->toBe('png')
        ->width->toBe(40);

    Storage::disk('public')->assertExists($this->image->path);
});

test('updating a file through the api requires the update permission', function (): void {
    $user = User::factory()->create(['superuser' => false]);
    $user->givePermissionTo('read files');

    $this->actingAs($user)
        ->putJson("/api/files/{$this->image->id}", ['name' => 'sunset.jpg'])
        ->assertForbidden();

    expect($this->image->refresh()->name)->toBe('photo.jpg');
});

test('the api rejects a file update without a name', function (): void {
    $this->actingAs(User::factory()->create(['superuser' => true]))
        ->putJson("/api/files/{$this->image->id}", ['name' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});
