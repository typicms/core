<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use TypiCMS\Modules\Core\Models\User;

beforeEach(function (): void {
    Storage::fake('public');

    $this->contributor = User::factory()->create(['superuser' => false]);
    $this->contributor->givePermissionTo('create files');
});

function payloadNamed(string $clientName, string $content = '<img src=x onerror="alert(document.cookie)">'): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'upload');
    file_put_contents($path, $content);

    return new UploadedFile($path, $clientName, 'text/plain', null, true);
}

describe('non-superuser with only “create files” permission', function (): void {
    test('cannot store a payload under an executable .html extension', function (): void {
        $this->actingAs($this->contributor)
            ->postJson('api/files', ['name' => payloadNamed('poc.html')])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        Storage::disk('public')->assertMissing('files/poc.html');
    });

    test('cannot bypass svg sanitization by renaming svg content', function (): void {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(document.cookie)</script></svg>';

        $this->actingAs($this->contributor)
            ->postJson('api/files', ['name' => payloadNamed('evil.html', $svg)])
            ->assertStatus(422);

        Storage::disk('public')->assertMissing('files/evil.html');
    });

    test('can still upload a legitimate image', function (): void {
        $this->actingAs($this->contributor)
            ->postJson('api/files', ['name' => UploadedFile::fake()->image('photo.jpg', 20, 20)])
            ->assertOk()
            ->assertJsonPath('model.extension', 'jpg');
    });
});
