<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Providers;

use Exception;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Override;
use Spatie\ResponseCache\Facades\ResponseCache;
use TypiCMS\Modules\Core\Commands\Create;
use TypiCMS\Modules\Core\Commands\CreateUser;
use TypiCMS\Modules\Core\Commands\Database;
use TypiCMS\Modules\Core\Commands\Install;
use TypiCMS\Modules\Core\Commands\Publish;
use TypiCMS\Modules\Core\Composers\SidebarViewComposer;
use TypiCMS\Modules\Core\Composers\SidebarViewCreator;
use TypiCMS\Modules\Core\Models\Page;
use TypiCMS\Modules\Core\Models\Setting;

class ModuleServiceProvider extends ServiceProvider
{
    private int $migrationCount = 0;

    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        if (config('responsecache.enabled')) {
            Event::listen('eloquent.saved:*', fn () => ResponseCache::clear());
            Event::listen('eloquent.deleted:*', fn () => ResponseCache::clear());
        }

        /*
         * Get configuration from DB and store it in the container.
         */
        config([
            'typicms' => array_merge(new Setting()->allToArray(), config('typicms', [])),
        ]);

        Gate::before(function ($user) {
            if ($user->isSuperUser()) {
                return true;
            }
        });

        Paginator::defaultView('public::core.pagination.bootstrap-5');
        Paginator::defaultSimpleView('public::core.pagination.simple-bootstrap-5');

        /*
         * Load routes.
         */
        $this->loadRoutesFrom(__DIR__.'/../routes/blocks.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/core.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/dashboard.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/files.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/history.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/llms-txt.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/menus.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/roles.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/search.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/settings.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/sitemap.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/tags.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/taxonomies.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/translations.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/users.php');

        /*
         * Load views.
         */
        $this->loadViewsFrom([
            resource_path('views/admin'),
            __DIR__.'/../../resources/views/admin',
        ], 'admin');

        $this->loadViewsFrom([
            resource_path('views/public'),
            __DIR__.'/../../resources/views/public',
        ], 'public');

        $this->loadViewsFrom([
            resource_path('views/mail'),
            __DIR__.'/../../resources/views/mail',
        ], 'mail');

        $this->loadViewsFrom([
            resource_path('views/errors'),
            __DIR__.'/../../resources/views/errors',
        ], 'errors');

        /*
         |--------------------------------------------------------------------------
         | Publish config.
         |--------------------------------------------------------------------------
         */
        $this->publishes([__DIR__.'/../config/typicms.php' => config_path('typicms.php')], 'typicms-config');

        /*
         |--------------------------------------------------------------------------
         | Publish bootstrap files.
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../stubs/bootstrap/app.php' => base_path('bootstrap/app.php'),
            __DIR__.'/../../stubs/bootstrap/providers.php' => base_path('bootstrap/providers.php'),
        ], 'typicms-bootstrap');

        /*
         |--------------------------------------------------------------------------
         | Publish helpers.
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../stubs/app/helpers.php' => app_path('helpers.php'),
        ], 'typicms-helpers');

        /*
         |--------------------------------------------------------------------------
         | Publish fonts.
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../resources/fonts' => resource_path('fonts'),
        ], ['typicms-resources', 'typicms-admin-resources', 'typicms-public-resources', 'typicms-fonts']);

        /*
         |--------------------------------------------------------------------------
         | Publish images (shared between admin and public).
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../resources/images' => resource_path('images'),
        ], ['typicms-resources', 'typicms-admin-resources', 'typicms-public-resources', 'typicms-images']);

        /*
         |--------------------------------------------------------------------------
         | Publish admin scss files.
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../resources/scss/_alertify.scss' => resource_path('scss/_alertify.scss'),
            __DIR__.'/../../resources/scss/_auth.scss' => resource_path('scss/_auth.scss'),
            __DIR__.'/../../resources/scss/_lucide-base.scss' => resource_path('scss/_lucide-base.scss'),
            __DIR__.'/../../resources/scss/admin' => resource_path('scss/admin'),
            __DIR__.'/../../resources/scss/admin.scss' => resource_path('scss/admin.scss'),
        ], ['typicms-resources', 'typicms-admin-resources', 'typicms-admin-scss']);

        /*
         |--------------------------------------------------------------------------
         | Publish public scss files.
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../resources/scss/_lucide-base.scss' => resource_path('scss/_lucide-base.scss'),
            __DIR__.'/../../resources/scss/public' => resource_path('scss/public'),
            __DIR__.'/../../resources/scss/public.scss' => resource_path('scss/public.scss'),
        ], ['typicms-resources', 'typicms-public-resources', 'typicms-public-scss']);

        /*
         |--------------------------------------------------------------------------
         | Publish admin js/ts/vue files.
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../resources/js/admin' => resource_path('js/admin'),
            __DIR__.'/../../resources/js/admin.js' => resource_path('js/admin.js'),
            __DIR__.'/../../resources/js/components' => resource_path('js/components'),
            __DIR__.'/../../resources/js/composables' => resource_path('js/composables'),
        ], ['typicms-resources', 'typicms-admin-resources', 'typicms-admin-js']);

        /*
         |--------------------------------------------------------------------------
         | Publish public js/ts/vue files.
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../resources/js/public' => resource_path('js/public'),
            __DIR__.'/../../resources/js/public.js' => resource_path('js/public.js'),
        ], ['typicms-resources', 'typicms-public-resources', 'typicms-public-js']);

        /*
         |--------------------------------------------------------------------------
         | Publish lang files.
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../lang' => lang_path(),
        ], 'typicms-lang');

        /*
         |--------------------------------------------------------------------------
         | Publish public folder.
         |--------------------------------------------------------------------------
         */
        $this->publishes([__DIR__.'/../../public' => public_path()], 'typicms-public');

        /*
         |--------------------------------------------------------------------------
         | Publish seeders.
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../database/seeders/' => database_path('seeders'),
        ], 'typicms-seeders');

        /*
         |--------------------------------------------------------------------------
         | Publish factories.
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../database/factories/' => database_path('factories'),
        ], 'typicms-factories');

        /*
         |--------------------------------------------------------------------------
         | Publish tests.
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../tests/Feature/' => base_path('tests/Feature'),
            __DIR__.'/../../tests/Unit/' => base_path('tests/Unit'),
        ], 'typicms-tests');

        /*
         |--------------------------------------------------------------------------
         | Publish views.
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../resources/views/errors' => resource_path('views/errors'),
        ], ['typicms-views', 'typicms-errors-views']);
        $this->publishes([
            __DIR__.'/../../resources/views/admin' => resource_path('views/admin'),
        ], ['typicms-views', 'typicms-admin-views', 'typicms-admin-core-views']);
        $this->publishes([
            __DIR__.'/../../resources/views/public' => resource_path('views/public'),
        ], ['typicms-views', 'typicms-public-views', 'typicms-public-core-views']);

        /*
         |--------------------------------------------------------------------------
         | Publish migrations.
         |--------------------------------------------------------------------------
         */
        $this->publishes([
            __DIR__.'/../../database/migrations/create_files_table.php.stub' => $this->getMigrationFileName(
                'create_files_table',
            ),
            __DIR__.'/../../database/migrations/create_pages_tables.php.stub' => $this->getMigrationFileName(
                'create_pages_tables',
            ),
            __DIR__.'/../../database/migrations/create_blocks_table.php.stub' => $this->getMigrationFileName(
                'create_blocks_table',
            ),
            __DIR__.'/../../database/migrations/create_settings_table.php.stub' => $this->getMigrationFileName(
                'create_settings_table',
            ),
            __DIR__.'/../../database/migrations/create_history_table.php.stub' => $this->getMigrationFileName(
                'create_history_table',
            ),
            __DIR__.'/../../database/migrations/create_users_table.php.stub' => $this->getMigrationFileName(
                'create_users_table',
            ),
            __DIR__
                .'/../../database/migrations/create_one_time_passwords_table.php.stub' => $this->getMigrationFileName(
                    'create_one_time_passwords_table',
                ),
            __DIR__.'/../../database/migrations/create_passkeys_table.php.stub' => $this->getMigrationFileName(
                'create_passkeys_table',
            ),
            __DIR__.'/../../database/migrations/create_model_has_files_table.php.stub' => $this->getMigrationFileName(
                'create_model_has_files_table',
            ),
            __DIR__.'/../../database/migrations/create_menus_tables.php.stub' => $this->getMigrationFileName(
                'create_menus_tables',
            ),
            __DIR__.'/../../database/migrations/create_tags_table.php.stub' => $this->getMigrationFileName(
                'create_tags_table',
            ),
            __DIR__.'/../../database/migrations/create_taxonomies_tables.php.stub' => $this->getMigrationFileName(
                'create_taxonomies_tables',
            ),
            __DIR__.'/../../database/migrations/create_translations_table.php.stub' => $this->getMigrationFileName(
                'create_translations_table',
            ),
        ], 'typicms-migrations');

        /*
         |--------------------------------------------------------------------------
         | Sidebar
         |--------------------------------------------------------------------------
         */
        View::creator('admin::core._sidebar', SidebarViewCreator::class);

        /*
         |--------------------------------------------------------------------------
         | View composers.
         |--------------------------------------------------------------------------
         */
        View::composers([
            SidebarViewComposer::class => 'admin::core._sidebar',
        ]);
        View::composer('public::search.*', function ($view): void {
            $view->page = getPageLinkedToModule('search');
        });
        View::composer('public::tags.*', function ($view): void {
            $view->page = getPageLinkedToModule('tags');
        });

        Blade::componentNamespace('TypiCMS\\Modules\\Core\\Http\\Components', 'core');
        Blade::anonymousComponentPath(resource_path('views/admin/components'), 'core');
        Blade::anonymousComponentPath(resource_path('views/public/components'), 'core');
        Blade::anonymousComponentPath(__DIR__.'/../../resources/views/admin/components', 'core');
        Blade::anonymousComponentPath(__DIR__.'/../../resources/views/public/components', 'core');

        /*
         |--------------------------------------------------------------------------
         | Blade directives.
         |--------------------------------------------------------------------------
         */
        Blade::directive('block', fn (string $name): string => sprintf(
            '<?php echo (new TypiCMS\Modules\Core\Models\Block())->render(%s) ?>',
            $name,
        ));
        Blade::directive('menu', fn (string $name): string => sprintf(
            "<?php echo view('public::menus._menu', ['name' => %s]) ?>",
            $name,
        ));

        /*
         |--------------------------------------------------------------------------
         | Register TypiCMS commands.
         |--------------------------------------------------------------------------
         */
        if ($this->app->runningInConsole()) {
            $this->commands([
                Create::class,
                CreateUser::class,
                Database::class,
                Install::class,
                Publish::class,
            ]);
        }
    }

    /**
     * Register the service provider.
     */
    #[Override]
    public function register(): void
    {
        /*
         * Merge config from the different modules.
         */
        $this->mergeConfigFrom(__DIR__.'/../config/dashboard.php', 'typicms.modules.dashboard');
        $this->mergeConfigFrom(__DIR__.'/../config/pages.php', 'typicms.modules.pages');
        $this->mergeConfigFrom(__DIR__.'/../config/page_sections.php', 'typicms.modules.page_sections');
        $this->mergeConfigFrom(__DIR__.'/../config/blocks.php', 'typicms.modules.blocks');
        $this->mergeConfigFrom(__DIR__.'/../config/history.php', 'typicms.modules.history');
        $this->mergeConfigFrom(__DIR__.'/../config/menus.php', 'typicms.modules.menus');
        $this->mergeConfigFrom(__DIR__.'/../config/menulinks.php', 'typicms.modules.menulinks');
        $this->mergeConfigFrom(__DIR__.'/../config/files.php', 'typicms.modules.files');
        $this->mergeConfigFrom(__DIR__.'/../config/search.php', 'typicms.modules.search');
        $this->mergeConfigFrom(__DIR__.'/../config/tags.php', 'typicms.modules.tags');
        $this->mergeConfigFrom(__DIR__.'/../config/taxonomies.php', 'typicms.modules.taxonomies');
        $this->mergeConfigFrom(__DIR__.'/../config/terms.php', 'typicms.modules.terms');
        $this->mergeConfigFrom(__DIR__.'/../config/translations.php', 'typicms.modules.translations');
        $this->mergeConfigFrom(__DIR__.'/../config/users.php', 'typicms.modules.users');
        $this->mergeConfigFrom(__DIR__.'/../config/roles.php', 'typicms.modules.roles');

        /*
         * Register TypiCMS routes.
         */
        $this->app->singleton('typicms.routes', function (): array {
            try {
                return Page::query()
                    ->with('images', 'documents')
                    ->whereNotNull('module')
                    ->get()
                    ->all();
            } catch (Exception) {
                return [];
            }
        });
    }

    private function getMigrationFileName(string $name): string
    {
        return getMigrationFileName($name, ++$this->migrationCount);
    }
}
