---
name: typicms-v18-upgrade
description: Upgrade a TypiCMS 17 project to TypiCMS 18. Use when the user asks to "upgrade to TypiCMS 18", "migrate to v18", bumps typicms/core to ^18.0, or hits v18 breaking changes such as "No hint path defined for [admin]" or "[public]", "View [admin::…] not found", "Route [en::index-news] not defined", a missing api-token meta tag, 401 responses from the admin API, or DirectoryNotFoundException when running php artisan optimize.
---

# TypiCMS 17 → 18 Upgrade

TypiCMS 18 makes five breaking changes. Apply them in this order, and run the checks at the end:

1. Composer and frontend dependencies (Bootstrap 6)
2. Admin API: session instead of API tokens
3. Views: no more `admin::`, `public::` and `mails::` namespaces
4. Module front office routes: rewritten URIs, no more per-locale route names
5. Published and custom modules (`/Modules`)

Read `CHANGELOG.md` (18.0.0) for the full list. When in doubt, compare with the canonical files in `vendor/typicms/core/stubs/` and with an upstream module in `vendor/typicms/*`.

Route names in the admin (`admin::index-news`, `admin::dashboard`, `admin::update-page`…) do **not** change. Only view names lose their `::`.

## 1. Dependencies

In `composer.json`:

```json
"typicms/core": "^18.0",
"typicms/laravel-translatable-bootforms": "^11.0"
```

Every TypiCMS module the project requires (`typicms/news`, `typicms/events`…) also moves to its 18.x release.

In `package.json`, require `"bootstrap": "^6.0.0-alpha.2"` and remove `@popperjs/core`. Add this to `vite.config.js`, otherwise the HTML source editor loses syntax highlighting:

```js
resolve: {
    dedupe: ['@codemirror/autocomplete', '@codemirror/language', '@codemirror/state', '@codemirror/view', '@lezer/common', '@lezer/highlight'],
},
```

Then run `bun remove alertify.js`, `composer update`, `bun install` and `bun run build`.

**alertify.js is replaced by Bootstrap toasts.** In published or custom admin code:

- Replace `alertify.success(` with `toast.success(` and `alertify.error(` with `toast.error(`. The messages are inserted as text, not HTML. `window.toast` is global. Inside the admin JS, import it with `import toast from '…/admin/toast.ts'`.
- Remove `alertify.logPosition(…)`.
- In published layouts, replace the `<script type="module">` that flashes `session('message')`, `session('success')` and `session('error')` with `<x-core::toasts />`, after `@stack('js')`.
- Delete a published `resources/scss/_alertify.scss` and its `@use 'alertify';` in `admin.scss`, and add `@use 'admin/toasts';`.
- Find leftovers with: `grep -rn "alertify" app resources Modules`

**Published SCSS and views (Bootstrap 6).** The admin and public Sass variables (`resources/scss/admin/_variables.scss` and the `$` variables in `public.scss`) are replaced by Bootstrap CSS variables. Buttons use Bootstrap classes in the markup instead of `@extend`. If the project has its own `resources/scss` or published Blade views, migrate them with the `bootstrap-v5-v6-migration` skill. Then compare them with `vendor/typicms/core/resources/scss` to pick up the CSS variables.

## 2. Admin API authentication

The admin API now uses the session and the CSRF token. API routes use `['web', 'api', 'auth']` instead of `['api', 'auth:api']`.

- `config/auth.php`: remove the `api` guard (`'driver' => 'token'`).
- Publish and run the migration that drops `users.api_token`:
  ```bash
  php artisan vendor:publish --tag=typicms-migrations
  php artisan migrate
  ```
  Already published migrations keep their file names, so only the new one is added.
- Remove `<meta name="api-token" …>` from any published layout. Replace `Authorization: Bearer …api-token…` headers in published JS with `'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content`.
- Find leftovers with: `grep -rn "api_token\|api-token\|auth:api" app config resources Modules routes`

## 3. View names

Views are no longer registered under namespaces. Packages add their `resources/views` folder with `View::addLocation()`. The project's `resources/views` is searched first, so published views keep overriding package views at the same path.

| v17                       | v18                       |
|---------------------------|---------------------------|
| `admin::news.index`       | `admin.news.index`        |
| `public::news.show`       | `public.news.show`        |
| `mails::events.new-…`     | `mail.events.new-…`       |
| `'public::'.$module.'._x'`| `'public.'.$module.'._x'` |

Rename **only view names**. A view name always contains a dot after the namespace. A route name never does (`admin::index-news`, `admin::dashboard`). This Perl one-liner renames the views and leaves the routes alone:

```bash
grep -rl --null -E "(admin|public|mails)::(([A-Za-z0-9_-]|\{\{ ?[A-Za-z]+ ?\}\})+\.|'\.)" app config bootstrap resources Modules routes 2>/dev/null \
  | xargs -0 perl -pi -e "s/\b(admin|public)::(?=(?:[A-Za-z0-9_\-]|\{\{ ?[A-Za-z]+ ?\}\})+\.|'\.)/\$1./g; s/\bmails::(?=[A-Za-z0-9_\-]+\.)/mail./g"
```

It covers `view()`, `@extends`, `@include*`, `View::composer()`, `response()->view()`, notifications (`->view('mails::…')`), `Paginator::defaultView()` and:

- `config/javascript.php`: `'bind_js_vars_to_this_view' => 'admin.core._javascript'`
- `bootstrap/app.php`: the exception renderer uses `view()->exists('admin.errors.'.$statusCode)`

Check what is left with `grep -rn "admin::\|public::\|mails::" app config bootstrap resources Modules routes`. Every remaining match must be a route name.

Also check these:

- `config/typicms.php`: `template_dir` is now a folder of `resources/views` (`'public'`), used as `public.pages.{template}`. Code that builds `$templateDir.'::pages.'` must use `'.pages.'`.
- `errors::` is no longer registered by TypiCMS. Laravel resolves `resources/views/errors` itself.
- Custom service providers that call `loadViewsFrom([...], 'admin')` or `'public'` must drop the call. See step 5.

## 4. Module front office routes and URLs

Modules linked to a page are no longer registered under the page's URI. A module registers its routes once under a fixed internal prefix. The `RewriteModuleUri` middleware rewrites `/en/agenda/a-talk` onto `/en/_module/events/a-talk` before routing. So the per-locale named routes are gone: `en::index-news`, `en::news`, `en::news-feed`, `en::projects-category`, `en::search`, `en::index-tags`…

Find their uses:

```bash
grep -rn "::index-\|getLocale() *\. *'::\|\$lang *\. *'::\|\$locale}::\|Route::has(" app resources Modules routes
```

Ignore the auth routes (`en::login`, `en::login-code`, `en::create-passkey`…), which still exist.

Replace them with `TypiCMS\Modules\Core\Support\ModuleUrl`. Each method returns `null` when the module is not linked to a page published in that locale:

| v17                                                     | v18                                                      |
|---------------------------------------------------------|----------------------------------------------------------|
| `route(app()->getLocale().'::index-news')`              | `ModuleUrl::index('news')`                               |
| `route($locale.'::news', $slug)`                        | `ModuleUrl::item('news', $slug, $locale)`                |
| `route($lang.'::news-feed', 'news')`                    | `ModuleUrl::item('news', 'news-feed.xml', $lang)`        |
| `route($locale.'::project', [$category, $slug])`        | `ModuleUrl::to('projects', [$category, $slug], $locale)` |
| `Route::has(app()->getLocale().'::search')`             | `ModuleUrl::index('search') !== null`                    |

In Blade, import the class with `@use('TypiCMS\Modules\Core\Support\ModuleUrl')`.

`feeds()` in `app/helpers.php` must build its URLs with `ModuleUrl` too:

```php
$url = ModuleUrl::item($module, "{$module}-feed.xml", $locale);
if (($properties['has_feed'] ?? false) === true && $url !== null) {
    $feeds[$module] = [
        'url' => $url,
        'title' => __(ucfirst($module).' feed').' – '.websiteTitle(),
    ];
}
```

## 5. Modules in /Modules

This covers modules created with `typicms:create` and vendor modules moved with `typicms:publish`. Upgrade each one against `vendor/typicms/core/stubs/module/**`.

**Routes** (`routes/{module}.php`): replace the `getPageLinkedToModule()` + `foreach (locales())` block with `ModuleRoutes::group()`. Keep the URIs and drop the route names. Privacy and the `public` middleware are handled by the `ModulePage` middleware.

```php
use TypiCMS\Modules\Core\Support\ModuleRoutes;

ModuleRoutes::group('things', function (Router $router): void {
    $router->get('/', [PublicController::class, 'index']);
    $router->get('{slug}', [PublicController::class, 'show']);
});
```

In the same file, change the API group to `Route::middleware(['web', 'api', 'auth'])->prefix('api')`.

**Model** `url()`:

```php
public function url(?string $locale = null): ?string
{
    $locale ??= app()->getLocale();

    return ModuleUrl::item('things', $this->translate('slug', $locale), $locale);
}
```

**Service provider**:

- Remove every `loadViewsFrom([...], 'admin' | 'public' | 'mails')` call, and do not replace them with `View::addLocation()`. The module's views are in the project's `resources/views`, and the `__DIR__.'/../../resources/views/…'` paths point to `Modules/resources/views`, which does not exist. `view:cache` (and so `php artisan optimize`) throws `DirectoryNotFoundException` on such a path.
- Remove the `View::composer('public.things.*', …getPageLinkedToModule(…))` block. `ModulePage` already shares `$page` with the module's views.
- Keep `View::composer('admin.core._sidebar', SidebarViewComposer::class)`.

**Controllers, notifications, views**: step 3 renamed their view names. Step 4 handles their `route($locale.'::…')` calls.

## Checks

```bash
php artisan optimize          # must succeed: view:cache scans every view location
php artisan optimize:clear    # TypiCMS module routes must not stay cached
php artisan test --compact
grep -rn "admin::\|public::\|mails::" app config bootstrap resources Modules routes   # only route names left
```

Then open the public site and the admin:

- a page linked to each module, one of its items and its feed
- the search form
- a private module page while logged out (it must redirect to login)
- the admin lists (they load through the API, so a 401 means step 2 is incomplete)
- file uploads
