<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Composers;

use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use TypiCMS\Modules\Sidebar\SidebarGroup;
use TypiCMS\Modules\Sidebar\SidebarItem;
use TypiCMS\Modules\Sidebar\SidebarManager;

class SidebarViewComposer
{
    public function compose(View $view): void
    {
        $sidebar = $view->offsetGet('sidebar');

        if (! $sidebar instanceof SidebarManager) {
            return;
        }

        if (Gate::allows('see dashboard')) {
            $sidebar->group('dashboard', function (SidebarGroup $group): void {
                $group->id = 'dashboard';
                $group->weight = 10;
                $group->hideHeading();
                $group->addItem(
                    __(config()->string('typicms.modules.dashboard.sidebar.label', 'Dashboard')),
                    function (SidebarItem $item): void {
                        $item->id = 'dashboard';
                        $item->icon = config()->string('typicms.modules.dashboard.sidebar.icon');
                        $item->weight = config()->integer('typicms.modules.dashboard.sidebar.weight');
                        $item->route('admin::dashboard');
                    },
                );
            });
        }

        if (Gate::allows('read pages')) {
            $sidebar->group(
                __(config()->string('typicms.modules.pages.sidebar.group', 'Content')),
                function (SidebarGroup $group): void {
                    $group->id = 'content';
                    $group->weight = 30;
                    $group->addItem(
                        __(config()->string('typicms.modules.pages.sidebar.label', 'Pages')),
                        function (SidebarItem $item): void {
                            $item->id = 'pages';
                            $item->icon = config()->string('typicms.modules.pages.sidebar.icon');
                            $item->weight = config()->integer('typicms.modules.pages.sidebar.weight');
                            $item->route('admin::index-pages');
                        },
                    );
                },
            );
        }

        if (Gate::allows('read menus')) {
            $sidebar->group(
                __(config()->string('typicms.modules.menus.sidebar.group', 'Content')),
                function (SidebarGroup $group): void {
                    $group->id = 'content';
                    $group->weight = 30;
                    $group->addItem(
                        __(config()->string('typicms.modules.menus.sidebar.label', 'Menus')),
                        function (SidebarItem $item): void {
                            $item->id = 'menus';
                            $item->icon = config()->string('typicms.modules.menus.sidebar.icon');
                            $item->weight = config()->integer('typicms.modules.menus.sidebar.weight');
                            $item->route('admin::index-menus');
                        },
                    );
                },
            );
        }

        if (Gate::allows('read blocks')) {
            $sidebar->group(
                __(config()->string('typicms.modules.blocks.sidebar.group', 'Content')),
                function (SidebarGroup $group): void {
                    $group->id = 'content';
                    $group->weight = 30;
                    $group->addItem(
                        __(config()->string('typicms.modules.blocks.sidebar.label', 'Content blocks')),
                        function (SidebarItem $item): void {
                            $item->id = 'blocks';
                            $item->icon = config()->string('typicms.modules.blocks.sidebar.icon');
                            $item->weight = config()->integer('typicms.modules.blocks.sidebar.weight');
                            $item->route('admin::index-blocks');
                        },
                    );
                },
            );
        }

        if (Gate::allows('read tags')) {
            $sidebar->group(
                __(config()->string('typicms.modules.tags.sidebar.group', 'Content')),
                function (SidebarGroup $group): void {
                    $group->id = 'content';
                    $group->weight = 30;
                    $group->addItem(
                        __(config()->string('typicms.modules.tags.sidebar.label', 'Tags')),
                        function (SidebarItem $item): void {
                            $item->id = 'tags';
                            $item->icon = config()->string('typicms.modules.tags.sidebar.icon');
                            $item->weight = config()->integer('typicms.modules.tags.sidebar.weight');
                            $item->route('admin::index-tags');
                        },
                    );
                },
            );
        }

        if (Gate::allows('read taxonomies')) {
            $sidebar->group(
                __(config()->string('typicms.modules.taxonomies.sidebar.group', 'Content')),
                function (SidebarGroup $group): void {
                    $group->id = 'content';
                    $group->weight = 30;
                    $group->addItem(
                        __(config()->string('typicms.modules.taxonomies.sidebar.label', 'Taxonomies')),
                        function (SidebarItem $item): void {
                            $item->id = 'taxonomies';
                            $item->icon = config()->string('typicms.modules.taxonomies.sidebar.icon');
                            $item->weight = config()->integer('typicms.modules.taxonomies.sidebar.weight');
                            $item->route('admin::index-taxonomies');
                        },
                    );
                },
            );
        }

        if (Gate::allows('read translations')) {
            $sidebar->group(
                __(config()->string('typicms.modules.translations.sidebar.group', 'Content')),
                function (SidebarGroup $group): void {
                    $group->id = 'content';
                    $group->weight = 30;
                    $group->addItem(
                        __(config()->string('typicms.modules.translations.sidebar.label', 'Translations')),
                        function (SidebarItem $item): void {
                            $item->id = 'translations';
                            $item->icon = config()->string('typicms.modules.translations.sidebar.icon');
                            $item->weight = config()->integer('typicms.modules.translations.sidebar.weight');
                            $item->route('admin::index-translations');
                        },
                    );
                },
            );
        }

        if (Gate::allows('read users')) {
            $sidebar->group(
                __(config()->string('typicms.modules.users.sidebar.group', 'Users and roles')),
                function (SidebarGroup $group): void {
                    $group->id = 'users';
                    $group->weight = 50;
                    $group->addItem(
                        __(config()->string('typicms.modules.users.sidebar.label', 'Users')),
                        function (SidebarItem $item): void {
                            $item->id = 'users';
                            $item->icon = config()->string('typicms.modules.users.sidebar.icon');
                            $item->weight = config()->integer('typicms.modules.users.sidebar.weight');
                            $item->route('admin::index-users');
                        },
                    );
                },
            );
        }

        if (Gate::allows('read roles')) {
            $sidebar->group(
                __(config()->string('typicms.modules.roles.sidebar.group', 'Users and roles')),
                function (SidebarGroup $group): void {
                    $group->id = 'users';
                    $group->weight = 50;
                    $group->addItem(
                        __(config()->string('typicms.modules.roles.sidebar.label', 'Roles')),
                        function (SidebarItem $item): void {
                            $item->id = 'roles';
                            $item->icon = config()->string('typicms.modules.roles.sidebar.icon');
                            $item->weight = config()->integer('typicms.modules.roles.sidebar.weight');
                            $item->route('admin::index-roles');
                        },
                    );
                },
            );
        }

        if (Gate::allows('read files')) {
            $sidebar->group(
                __(config()->string('typicms.modules.files.sidebar.group', 'Media')),
                function (SidebarGroup $group): void {
                    $group->id = 'media';
                    $group->weight = 40;
                    $group->addItem(
                        __(config()->string('typicms.modules.files.sidebar.label', 'Files')),
                        function (SidebarItem $item): void {
                            $item->id = 'files';
                            $item->icon = config()->string('typicms.modules.files.sidebar.icon');
                            $item->weight = config()->integer('typicms.modules.files.sidebar.weight');
                            $item->route('admin::index-files');
                        },
                    );
                },
            );
        }
    }
}
