<?php

declare(strict_types=1);

use TypiCMS\Modules\Core\Models\File;
use TypiCMS\Modules\Core\Models\User;

beforeEach(function (): void {
    $this->image = File::query()->create([
        'type' => 'i',
        'name' => 'photo.jpg',
        'path' => 'files/photo.jpg',
        'extension' => 'jpg',
        'mimetype' => 'image/jpeg',
    ]);

    $this->actingAs(User::factory()->create(['superuser' => true]));
});

test('the focal point of an image can be set from the admin form', function (): void {
    $this->put(route('admin::update-file', $this->image), [
        'name' => 'photo.jpg',
        'focal_x' => 25,
        'focal_y' => 80,
    ])->assertSessionHasNoErrors();

    expect($this->image->refresh())
        ->focal_x->toBe(25)
        ->focal_y->toBe(80);
});

test('the focal point of an image can be cleared', function (): void {
    $this->image->update(['focal_x' => 25, 'focal_y' => 80]);

    $this->put(route('admin::update-file', $this->image), [
        'name' => 'photo.jpg',
        'focal_x' => '',
        'focal_y' => '',
    ])->assertSessionHasNoErrors();

    expect($this->image->refresh())
        ->focal_x->toBeNull()
        ->focal_y->toBeNull();
});

test('a focal point outside the image is rejected', function (): void {
    $this->put(route('admin::update-file', $this->image), [
        'name' => 'photo.jpg',
        'focal_x' => 101,
        'focal_y' => -1,
    ])->assertSessionHasErrors(['focal_x', 'focal_y']);
});

test('the focal point is exposed as a css position', function (): void {
    expect($this->image->focalPosition())->toBeNull()
        ->and($this->image->focalPosition('center'))->toBe('center');

    $this->image->update(['focal_x' => 25, 'focal_y' => 80]);

    expect($this->image->hasFocalPoint())->toBeTrue()
        ->and($this->image->focalPosition())->toBe('25% 80%');
});
