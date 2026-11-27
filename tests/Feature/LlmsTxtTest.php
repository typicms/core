<?php

use Illuminate\Support\Facades\Cache;
use TypiCMS\Modules\Core\Models\Menu;
use TypiCMS\Modules\Core\Models\Menulink;
use TypiCMS\Modules\Core\Models\Page;

test('llms.txt does not list private pages linked in a menu', function (): void {
    $menu = Menu::query()->firstOrCreate(['name' => 'primary'], ['status' => ['en' => '1']]);
    $menu->update(['status' => array_fill_keys(locales(), '1')]);

    $publicPage = Page::query()->create([
        'title' => ['en' => 'Visible public page'],
        'slug' => ['en' => 'visible-public-page'],
        'status' => ['en' => '1'],
        'private' => false,
    ]);

    $privatePage = Page::query()->create([
        'title' => ['en' => 'Secret private page'],
        'slug' => ['en' => 'secret-private-page'],
        'status' => ['en' => '1'],
        'private' => true,
    ]);

    foreach ([$publicPage, $privatePage] as $page) {
        Menulink::query()->create([
            'menu_id' => $menu->id,
            'page_id' => $page->id,
            'title' => ['en' => $page->title],
            'status' => ['en' => '1'],
        ]);
    }

    Cache::forget('llms-txt');

    $this->get('/llms.txt')
        ->assertOk()
        ->assertSee('Visible public page')
        ->assertDontSee('Secret private page')
        ->assertDontSee('secret-private-page');
});
