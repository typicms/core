<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[AsCommand(name: 'typicms:database', description: 'Set database credentials in .env file')]
class Database extends Command
{
    public function __construct(
        protected Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $contents = $this->getKeyFile();

        $dbName = (string) ($this->argument('database') ?? text(
            label: 'Choose a database name',
            placeholder: $this->guessDatabaseName(),
            default: $this->guessDatabaseName(),
            required: 'The database name is required.',
            hint: 'The database will be created if it doesn’t exist.',
        ));

        $dbAddress = text(
            label: 'What is your MySQL server address?',
            placeholder: '127.0.0.1',
            default: '127.0.0.1',
            required: 'The MySQL server address is required.',
        );
        $dbPort = text(
            label: 'What is your MySQL server port?',
            placeholder: '3306',
            default: '3306',
            required: 'The MySQL server port is required.',
        );
        $dbUserName = text(
            label: 'What is your MySQL server username?',
            default: 'root',
            required: 'The MySQL server username is required.',
        );
        $dbPassword = password(label: 'What is your MySQL server password?');

        $this->components->task('Updating .env file with database credentials', function () use (&$contents, $dbAddress, $dbPort, $dbName, $dbUserName, $dbPassword): void {
            $search = [
                '/('.preg_quote('DB_HOST=', '/').')(.*)/',
                '/('.preg_quote('DB_PORT=', '/').')(.*)/',
                '/('.preg_quote('DB_DATABASE=', '/').')(.*)/',
                '/('.preg_quote('DB_USERNAME=', '/').')(.*)/',
                '/('.preg_quote('DB_PASSWORD=', '/').')(.*)/',
            ];
            $replace = [
                '${1}'.$dbAddress,
                '${1}'.$dbPort,
                '${1}'.$dbName,
                '${1}'.$dbUserName,
                '${1}'.$dbPassword,
            ];
            $contents = (string) preg_replace($search, $replace, $contents);

            $this->files->put('.env', $contents);
        });

        $this->laravel->make(Repository::class)->set('database.connections.mysql.host', $dbAddress);
        $this->laravel->make(Repository::class)->set('database.connections.mysql.port', $dbPort);
        $this->laravel->make(Repository::class)->set('database.connections.mysql.username', $dbUserName);
        $this->laravel->make(Repository::class)->set('database.connections.mysql.password', $dbPassword);
        $this->laravel->make(Repository::class)->set('database.connections.mysql.database');

        DB::purge();

        $this->components->task('Creating database', function () use ($dbName): void {
            if (! collect(Schema::getSchemas())->contains('name', $dbName)) {
                Schema::createDatabase($dbName);
            }
        });

        $this->laravel->make(Repository::class)->set('database.connections.mysql.database', $dbName);

        DB::purge();

        if (count(DB::select('SHOW TABLES')) !== 0) {
            $this->components->error('The database '.$dbName.' is not empty, no migration and seed were done.');
        } else {
            $this->components->task('Migrating and seeding database', function (): void {
                $this->callSilently('migrate');
                $this->callSilently('db:seed');
            });
        }
    }

    /** @return array<int, array<int, int|string>> */
    #[Override]
    protected function getArguments(): array
    {
        return [
            ['database', InputArgument::OPTIONAL, 'The database name'],
        ];
    }

    protected function getKeyFile(): string
    {
        return $this->files->exists('.env') ? $this->files->get('.env') : $this->files->get('.env.example');
    }

    private function guessDatabaseName(): string
    {
        return Str::slug(Str::before(basename(dirname(app_path())), '.'));
    }
}
