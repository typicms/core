<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;
use TypiCMS\Modules\Core\Models\User;

use function Laravel\Prompts\text;

#[AsCommand(name: 'typicms:user', description: 'Creation of a superuser.')]
class CreateUser extends Command
{
    public function handle(): void
    {
        $firstname = text(label: 'Enter your first name', required: 'The first name is required.');
        $lastname = text(label: 'Enter your last name', required: 'The last name is required.');
        $email = text(
            label: 'Enter your email address',
            required: 'The email address is required.',
            validate: fn (string $value): ?string => match (true) {
                ! filter_var($value, FILTER_VALIDATE_EMAIL) => 'The email address is not valid.',
                default => null,
            },
        );

        if (User::query()->where('email', $email)->exists()) {
            $this->components->info("A user with email “{$email}” already exists, skipping superuser creation.");

            return;
        }

        try {
            $this->components->task('Creating a superuser', function () use ($firstname, $lastname, $email): void {
                User::query()->create([
                    'first_name' => $firstname,
                    'last_name' => $lastname,
                    'email' => $email,
                    'superuser' => 1,
                    'activated' => 1,
                ]);
            });
        } catch (Throwable) {
            $this->components->warn('An error occurred while creating the superuser. Please check your database configuration and create one manually with “php artisan typicms:user”.');
        }
    }
}
