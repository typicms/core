<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Sleep;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\title;

#[AsCommand(name: 'typicms:install', description: 'Installation of TypiCMS: Laravel setup, installation of composer and npm packages')]
class Install extends Command
{
    public function __construct(
        protected Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        title('Installing TypiCMS');
        $this->components->info('Welcome to TypiCMS');

        $this->call('vendor:publish', [
            '--tag' => [
                'permission-migrations',
                'typicms-config',
                'typicms-resources',
                'typicms-public',
                'typicms-lang',
                'typicms-views',
                'typicms-migrations',
                'typicms-seeders',
                'typicms-factories',
                'typicms-tests',
            ],
        ]);

        app()->environment('local');

        // Set database credentials in .env and migrate
        $this->call('typicms:database');

        // Create a superuser
        $this->call('typicms:user');

        $domain = $this->getDirectoryName().'.test';

        if ($this->canShellExec()) {
            $this->setAppUrl($domain);
            $this->secureSite($domain);
            $this->setDirectoryPermissions();
            $this->installAndBuildAssets();
        } else {
            $this->components->info('Set APP_URL in your .env file and secure your site with “herd secure” or “valet secure”.');
            $this->components->info(
                'Make the /storage and /bootstrap/cache directories writable, then run “npm install && npm run build”.',
            );
        }

        $this->components->info('Installation complete!');
        $this->components->info("Access the admin panel at <href=https://{$domain}/en/otp-login>https://{$domain}/en/otp-login</>");
        $this->components->info('Enjoy TypiCMS!');
        title('');
    }

    private function canShellExec(): bool
    {
        return
            function_exists('shell_exec')
            && ! in_array('shell_exec', explode(',', (string) ini_get('disable_functions')), true);
    }

    private function setDirectoryPermissions(): void
    {
        $this->components->task('Setting permissions', function (): void {
            shell_exec('chmod 755 $(find storage -type d) 2> /dev/null');
            shell_exec('chmod 755 $(find bootstrap/cache -type d) 2> /dev/null');
        });
    }

    private function installAndBuildAssets(): void
    {
        $packageManager = shell_exec('which bun 2> /dev/null')
            ? 'bun'
            : (shell_exec('which npm 2> /dev/null') ? 'npm' : null);

        if (! $packageManager) {
            $this->components->error('No package manager found. Please install bun or npm, run “npm install” and finally “npm run dev”.');

            return;
        }

        $installCommand = $packageManager === 'bun' ? 'bun i' : 'npm install';

        $this->components->task(
            "Installing packages with {$packageManager}",
            fn (): bool => $this->runWithSpinner($installCommand),
        );

        $this->components->task(
            'Compiling assets',
            fn (): bool => $this->runWithSpinner("{$packageManager} run build"),
        );
    }

    private function setAppUrl(string $domain): void
    {
        $appUrl = "https://{$domain}";

        $this->components->task("Setting app URL to {$appUrl}", function () use ($appUrl): void {
            $contents = $this->files->get('.env');
            $contents = (string) preg_replace('/('.preg_quote('APP_URL=', '/').')(.*)/', '${1}'.$appUrl, $contents);

            $this->files->put('.env', $contents);
        });
    }

    private function secureSite(string $domain): void
    {
        if (shell_exec('which herd 2> /dev/null')) {
            $this->components->task(
                'Securing site with Herd',
                fn (): bool => $this->runWithSpinner("herd secure {$domain}"),
            );

            return;
        }

        if (shell_exec('which valet 2> /dev/null')) {
            $this->components->task(
                'Securing site with Valet',
                fn (): bool => $this->runWithSpinner("valet secure {$domain}"),
            );
        }
    }

    private function runWithSpinner(string $command): bool
    {
        $frames = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
        $i = 0;

        $process = Process::fromShellCommandline("{$command} 2> /dev/null");
        $process->setTimeout(300);
        $process->start();

        while ($process->isRunning()) {
            $this->output->write($frames[$i % count($frames)]);
            Sleep::usleep(100_000);
            $this->output->write("\x08 \x08");
            $i++;
        }

        return $process->isSuccessful();
    }

    private function getDirectoryName(): string
    {
        return basename(dirname(app_path()));
    }
}
