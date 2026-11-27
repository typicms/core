<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use TypiCMS\Modules\Core\Models\User;

/** @extends Factory<User> */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'email' => Str::random(10).'@gmail.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'locale' => 'en',
            'activated' => true,
            'superuser' => false,
            'privacy_policy_accepted' => false,
        ];
    }
}
