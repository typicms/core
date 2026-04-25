<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\MountManager;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'typicms:create', description: 'Create a module in the /Modules directory.')]
class Create extends Command
{
    protected string $module;

    /** @var array<string> */
    protected array $search = [
        'things',
        'thing',
        'Things',
        'Thing',
    ];

    /** @var array<string> */
    protected array $replace;

    protected $signature = 'typicms:create {module : The module that you want to create}
            {--force : Overwrite any existing files.}';

    public function __construct(
        protected Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $moduleName = $this->argument('module');
        $isAlphabetic = preg_match('/^[a-z]+$/i', $moduleName) === 1;
        if (! $isAlphabetic) {
            $this->components->error('Only alphabetic characters are allowed.');

            return;
        }

        $this->module = Str::plural(mb_ucfirst(mb_strtolower($moduleName)));

        $this->replace = [
            mb_strtolower($this->module),
            mb_strtolower(Str::singular($this->module)),
            $this->module,
            Str::singular($this->module),
        ];

        if ($this->moduleExists()) {
            $this->components->error('A module named '.$this->module.' already exists.');

            return;
        }

        $this->publishModule();
        $this->moveAndRenameFiles();
        $this->searchAndReplaceInFiles();
        $this->publishViews();
        $this->publishScssFiles();
        $this->moveMigrationFile();
        $this->addTranslations();
        $this->deleteDirectories();
        $this->components->info(
            'The module <info>'
            .$this->module
            .'</info> was created in <info>/Modules</info>.',
        );
        $this->components->info(
            'Add <info>TypiCMS\Modules\\'
            .$this->module
            .'\Providers\ModuleServiceProvider::class</info> in <info>bootstrap/providers.php</info>.',
        );
        $this->components->info(
            'Run the database migration with the command <info>php artisan migrate</info>.',
        );
        $this->components->info('Run <info>npm run dev</info> to finish.');
    }

    /**
     * Generate the module in the Modules directory.
     */
    private function publishModule(): void
    {
        $from = base_path('vendor/typicms/things/src');
        $to = base_path('Modules/'.$this->module);

        if ($this->files->isDirectory($from)) {
            $this->publishDirectory($from, $to);
        } else {
            $this->components->error(sprintf('Can’t locate path: <%s>', $from));
        }
    }

    /**
     * Search and remplace all occurences of ‘Things’
     * in all files by the name of the new module.
     */
    public function searchAndReplaceInFiles(): void
    {
        $directory = base_path('Modules/'.$this->module);

        $manager = new MountManager([
            'directory' => new Flysystem(new LocalFilesystemAdapter($directory)),
        ]);

        foreach ($manager->listContents('directory://', true) as $file) {
            if ($file['type'] === 'file') {
                $content = str_replace($this->search, $this->replace, $manager->read($file['path']));
                $manager->write($file['path'], $content);
            }
        }
    }

    /**
     * Rename files.
     */
    public function moveAndRenameFiles(): void
    {
        $moduleDir = base_path('Modules/'.$this->module);
        $paths = [
            $moduleDir.'/config/things.php',
            $moduleDir.'/Models/Thing.php',
            $moduleDir.'/routes/things.php',
            $moduleDir.'/resources/scss/public/_thing.scss',
            $moduleDir.'/resources/scss/public/_thing-list.scss',
        ];
        foreach ($paths as $path) {
            $this->files->move($path, str_replace($this->search, $this->replace, $path));
        }
    }

    /**
     * Publish views to the admin:: and public:: namespaces.
     */
    public function publishViews(): void
    {
        $moduleSlug = mb_strtolower($this->module);
        $moduleDir = base_path('Modules/'.$this->module);

        $this->publishDirectory(
            $moduleDir.'/resources/views/admin/things',
            resource_path('views/admin/'.$moduleSlug),
        );
        $this->publishDirectory(
            $moduleDir.'/resources/views/public/things',
            resource_path('views/public/'.$moduleSlug),
        );
    }

    /**
     * Publish scss files.
     */
    public function publishScssFiles(): void
    {
        $from = base_path('Modules/'.$this->module.'/resources/scss/public');
        $to = resource_path('scss/public');
        $this->publishDirectory($from, $to);
    }

    /**
     * Rename and move migration file.
     */
    public function moveMigrationFile(): void
    {
        $from = base_path('Modules/'.$this->module.'/database/migrations/create_things_table.php.stub');
        $to = getMigrationFileName('create_'.mb_strtolower($this->module).'_table');
        $this->files->move($from, $to);
    }

    /**
     * Add translations.
     */
    public function addTranslations(): void
    {
        $this->callSilently('translations:add', ['path' => 'Modules/'.$this->module.'/lang']);
    }

    public function deleteDirectories(): void
    {
        $this->files->deleteDirectory(base_path('Modules/'.$this->module.'/resources'));
        $this->files->deleteDirectory(base_path('Modules/'.$this->module.'/lang'));
        $this->files->deleteDirectory(base_path('Modules/'.$this->module.'/database'));
    }

    /**
     * Publish the file to the given path.
     */
    protected function publishFile(string $from, string $to): void
    {
        if ($this->files->exists($to) && ! $this->option('force')) {
            return;
        }

        $this->createParentDirectory(dirname($to));

        $this->files->copy($from, $to);
    }

    /**
     * Publish the directory to the given directory.
     */
    protected function publishDirectory(string $from, string $to): void
    {
        $visibility = PortableVisibilityConverter::fromArray([], Visibility::PUBLIC);

        $this->moveManagedFiles(new MountManager([
            'from' => new Flysystem(new LocalFilesystemAdapter($from)),
            'to' => new Flysystem(new LocalFilesystemAdapter($to, $visibility)),
        ]));
    }

    /**
     * Move all the files in the given MountManager.
     */
    protected function moveManagedFiles(MountManager $manager): void
    {
        foreach ($manager->listContents('from://', true) as $file) {
            $path = Str::after($file['path'], 'from://');

            if ($file['type'] === 'file' && (! $manager->fileExists('to://'.$path) || $this->option('force'))) {
                $manager->write('to://'.$path, $manager->read($file['path']));
            }
        }
    }

    /**
     * Create the directory to house the published files if needed.
     */
    protected function createParentDirectory(string $directory): void
    {
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0o755, true);
        }
    }

    /**
     * Check if the module exists.
     */
    public function moduleExists(): bool
    {
        $location1 = $this->files->isDirectory(base_path('Modules/'.$this->module));
        $location2 = $this->files->isDirectory(base_path('vendor/typicms/'.mb_strtolower($this->module)));

        return $location1 || $location2;
    }
}
