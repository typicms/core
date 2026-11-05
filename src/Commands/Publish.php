<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Commands;

use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\MountManager;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\spin;

#[AsCommand(name: 'typicms:publish', description: 'Move a module from the vendor directory to the /Modules directory.')]
#[Signature('typicms:publish {module : The module that you want to publish}
            {--force : Overwrite any existing files.}')]
class Publish extends Command
{
    protected string $module;

    public function __construct(
        protected Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->module = mb_strtolower($this->argument('module'));
        if (! is_dir(base_path('vendor/typicms/'.$this->module))) {
            $this->components->error('Module “'.$this->module.'” not found in vendor directory.');

            return;
        }

        $provider = 'TypiCMS\Modules\\'.ucfirst($this->module).'\Providers\ModuleServiceProvider';
        if (class_exists($provider)) {
            $this->call('vendor:publish', ['--provider' => $provider]);
            $this->publishModule();
            $this->changePathForLoadViews();
            $this->uninstallFromComposer();
            $this->addProviderToBootstrap($provider);
            $this->components->success(ucfirst($this->module).' module successfully published.');
        } else {
            $this->components->error($provider.' not found.');
        }
    }

    /**
     * Publishes the module.
     */
    private function publishModule(): void
    {
        $from = base_path('vendor/typicms/'.$this->module.'/src');
        $to = base_path('Modules/'.ucfirst($this->module));

        if ($this->files->isDirectory($from)) {
            $this->publishDirectory($from, $to);
        } else {
            $this->components->error(sprintf('Can’t locate path: <%s>', $from));
        }
    }

    /**
     * Publish the directory to the given directory.
     */
    protected function publishDirectory(string $from, string $to): void
    {
        $visibility = PortableVisibilityConverter::fromArray([], Visibility::PUBLIC);

        $manager = new MountManager([
            'from' => new Flysystem(new LocalFilesystemAdapter($from)),
            'to' => new Flysystem(new LocalFilesystemAdapter($to, $visibility)),
        ]);

        foreach ($manager->listContents('from://', true) as $file) {
            if (mb_substr($file->path(), 0, 15) === 'resources/views') {
                continue;
            }

            if (mb_substr($file->path(), 0, 16) === 'resources/assets') {
                continue;
            }

            $path = Str::after($file->path(), 'from://');
            if ($file->isFile() && (! $manager->fileExists('to://'.$path) || $this->option('force'))) {
                $manager->write('to://'.$path, $manager->read($file->path()));
            }
        }
    }

    /**
     * Change the path of loadViewsFrom.
     */
    private function changePathForLoadViews(): void
    {
        $file = 'Modules/'.ucfirst($this->module).'/Providers/ModuleServiceProvider.php';
        $contents = $this->files->get($file);
        $contents = preg_replace(
            '#loadViewsFrom(.*)/\', \'(.*)\'\)#',
            'loadViewsFrom(resource_path(\'views\'), \'$2\')',
            $contents,
        );

        $this->files->put($file, $contents ?? '');
    }

    /**
     * Add the module's service provider to bootstrap/providers.php.
     */
    private function addProviderToBootstrap(string $provider): void
    {
        $file = base_path('bootstrap/providers.php');
        $contents = $this->files->get($file);

        $providerLine = "    {$provider}::class,";

        if (str_contains($contents, $provider)) {
            return;
        }

        $contents = str_replace(
            '    AppServiceProvider::class,',
            "{$providerLine}\n\n    AppServiceProvider::class,",
            $contents,
        );

        $this->files->put($file, $contents);
    }

    /**
     * Remove the module from Composer.
     */
    private function uninstallFromComposer(): void
    {
        $uninstallCommand = 'composer remove typicms/'.$this->module.' 2> /dev/null';

        if (
            function_exists('shell_exec')
            && ! in_array('shell_exec', explode(',', (string) ini_get('disable_functions')), true)
        ) {
            spin(
                fn (): string|false|null => shell_exec($uninstallCommand),
                'Uninstall '.$this->module.' from composer…',
            );
        } else {
            $this->components->info('You can now run '.$uninstallCommand.'.');
        }
    }
}
