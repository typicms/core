<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'typicms:create', description: 'Create a module in the /Modules directory.')]
class Create extends Command
{
    protected string $module;

    /** @var array<string, string> */
    protected array $tokens;

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

        if (preg_match('/^[a-z]+$/i', $moduleName) !== 1) {
            $this->components->error('Only alphabetic characters are allowed.');

            return;
        }

        $this->module = Str::plural(mb_ucfirst(mb_strtolower($moduleName)));

        $this->tokens = [
            '{{ modelPlural }}' => $this->module,
            '{{ model }}' => Str::singular($this->module),
            '{{ slugPlural }}' => mb_strtolower($this->module),
            '{{ slug }}' => mb_strtolower(Str::singular($this->module)),
        ];

        if ($this->moduleExists()) {
            $this->components->error('A module named '.$this->module.' already exists.');

            return;
        }

        $this->renderStubs();
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
     * Render every stub from the core package into the new module directory.
     */
    private function renderStubs(): void
    {
        $stubsRoot = dirname(__DIR__, 2).'/stubs/module';
        $moduleRoot = base_path('Modules/'.$this->module);

        if (! $this->files->isDirectory($stubsRoot)) {
            $this->components->error(sprintf('Cannot locate stubs at <%s>.', $stubsRoot));

            return;
        }

        foreach ($this->files->allFiles($stubsRoot, true) as $file) {
            $relative = $this->renderTokens($file->getRelativePathname());

            if (Str::endsWith($relative, '.stub')) {
                $relative = Str::replaceLast('.stub', '', $relative);
            }

            $target = $moduleRoot.'/'.$relative;

            if ($this->files->exists($target) && ! $this->option('force')) {
                continue;
            }

            $this->createParentDirectory(dirname($target));

            $contents = $this->files->get($file->getPathname());
            $this->files->put($target, $this->renderTokens($contents));
        }
    }

    /**
     * Replace placeholder tokens with the current module's values.
     */
    private function renderTokens(string $value): string
    {
        return str_replace(array_keys($this->tokens), array_values($this->tokens), $value);
    }

    /**
     * Publish views to the admin:: and public:: namespaces.
     */
    public function publishViews(): void
    {
        $moduleSlug = mb_strtolower($this->module);
        $moduleDir = base_path('Modules/'.$this->module);

        $this->moveDirectory(
            $moduleDir.'/resources/views/admin/'.$moduleSlug,
            resource_path('views/admin/'.$moduleSlug),
        );
        $this->moveDirectory(
            $moduleDir.'/resources/views/public/'.$moduleSlug,
            resource_path('views/public/'.$moduleSlug),
        );
    }

    /**
     * Publish scss files.
     */
    public function publishScssFiles(): void
    {
        $this->moveDirectory(
            base_path('Modules/'.$this->module.'/resources/scss/public'),
            resource_path('scss/public'),
        );
    }

    /**
     * Rename and move migration file.
     */
    public function moveMigrationFile(): void
    {
        $from = base_path('Modules/'.$this->module.'/database/migrations/create_'.mb_strtolower($this->module).'_table.php');
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
     * Move every file from one directory to another, preserving the relative paths.
     */
    protected function moveDirectory(string $from, string $to): void
    {
        if (! $this->files->isDirectory($from)) {
            return;
        }

        foreach ($this->files->allFiles($from, true) as $file) {
            $target = $to.'/'.$file->getRelativePathname();

            if ($this->files->exists($target) && ! $this->option('force')) {
                continue;
            }

            $this->createParentDirectory(dirname($target));
            $this->files->move($file->getPathname(), $target);
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
        return $this->files->isDirectory(base_path('Modules/'.$this->module));
    }
}
