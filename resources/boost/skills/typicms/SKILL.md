---
name: TypiCMS Development
description: This skill should be used when the user asks to "create a module", "add a TypiCMS module", "create a new content type", "add fields to a module", "work with translations", "customize a module", mentions TypiCMS architecture, module structure, or wants to understand how TypiCMS modules work.
version: 2.0.0
---

# TypiCMS Development Patterns

This skill provides guidance for developing with TypiCMS, a modular multilingual CMS built on Laravel.

## Core Concepts

### Module Architecture

TypiCMS organizes functionality into self-contained modules in `Modules/`. Each module is a local Composer package with this structure:

```
Modules/ModuleName/
├── Composers/
│   └── SidebarViewComposer.php
├── config/
│   └── modulename.php
├── Exports/
│   └── Export.php
├── Http/
│   ├── Controllers/
│   │   ├── AdminController.php
│   │   ├── ApiController.php
│   │   └── PublicController.php
│   └── Requests/
│       └── FormRequest.php
├── Models/
│   └── ModuleName.php
├── Observers/
│   └── CustomObserver.php (optional)
├── Providers/
│   └── ModuleServiceProvider.php
└── routes/
    └── modulename.php
```

### Database Conventions

- All tables use the `typicms_` prefix (handled automatically)
- Translatable fields use JSON columns with locale keys: `{"en": "value", "fr": "valeur"}`
- Use `json()` column type with `new Expression('(JSON_OBJECT())')` default
- Image references use `foreignId('image_id')->nullable()->constrained('files')->nullOnDelete()`

### Multilingual Support

Supported locales are configured in `config/typicms.php`. Models use the `HasTranslations` trait with a `$translatable` array defining which fields support multiple languages.

## Creating a New Module

### Step 1: Create the Model

Models extend `Illuminate\Database\Eloquent\Model` directly and use trait-based composition:

```php
<?php

declare(strict_types=1);

namespace TypiCMS\Modules\ModuleName\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Uri;
use TypiCMS\Modules\Core\Models\File;
use TypiCMS\Modules\Core\Traits\HasAdminUrls;
use TypiCMS\Modules\Core\Traits\HasBodyPresenter;
use TypiCMS\Modules\Core\Traits\HasConfigurableOrder;
use TypiCMS\Modules\Core\Traits\HasContentPresenter;
use TypiCMS\Modules\Core\Traits\HasFiles;
use TypiCMS\Modules\Core\Traits\HasOgImage;
use TypiCMS\Modules\Core\Traits\HasSelectableFields;
use TypiCMS\Modules\Core\Traits\HasSlugScope;
use TypiCMS\Modules\Core\Traits\Historable;
use TypiCMS\Modules\Core\Traits\Navigable;
use TypiCMS\Modules\Core\Traits\Publishable;
use TypiCMS\Translatable\HasTranslations;

class ModuleName extends Model
{
    use HasAdminUrls;
    use HasBodyPresenter;
    use HasConfigurableOrder;
    use HasContentPresenter;
    use HasFiles;
    use HasOgImage;
    use HasSelectableFields;
    use HasSlugScope;
    use HasTranslations;
    use Historable;
    use Navigable;
    use Publishable;

    protected $guarded = [];

    protected $appends = ['thumb'];

    /** @var array<string> */
    public array $translatable = [
        'title',
        'slug',
        'status',
        'summary',
        'body',
    ];

    /** @var array<string> */
    public array $tipTapContent = [
        'body',
    ];

    public function url(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $route = "{$locale}::modulename";
        $slug = $this->translate('slug', $locale);

        if (Route::has($route) && $slug) {
            return route($route, $slug);
        }

        return null;
    }

    public function previewUrl(?string $locale = null): ?string
    {
        $url = $this->url($locale);

        if (!$url) {
            return null;
        }

        return (string) Uri::of($url)->withQuery(['preview' => 'true']);
    }

    /** @return Attribute<string, null> */
    protected function thumb(): Attribute
    {
        return Attribute::make(get: fn (): string => imageOrDefault($this->image, null, 54));
    }

    /** @return BelongsTo<File, $this> */
    public function image(): BelongsTo
    {
        return $this->belongsTo(File::class, 'image_id');
    }

    /** @return BelongsTo<File, $this> */
    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(File::class, 'og_image_id');
    }
}
```

### Step 2: Create the Migration

Use JSON columns for translatable fields:

```php
Schema::create('modulename', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('image_id')->nullable()->constrained('files')->nullOnDelete();
    $table->foreignId('og_image_id')->nullable()->constrained('files')->nullOnDelete();
    $table->json('status')->default(new Expression('(JSON_OBJECT())'));
    $table->json('title')->default(new Expression('(JSON_OBJECT())'));
    $table->json('slug')->default(new Expression('(JSON_OBJECT())'));
    $table->json('summary')->default(new Expression('(JSON_OBJECT())'));
    $table->json('body')->default(new Expression('(JSON_OBJECT())'));
    $table->timestamps();
});
```

### Step 3: Create the Service Provider

Register routes, config, views, and observers:

```php
<?php

declare(strict_types=1);

namespace TypiCMS\Modules\ModuleName\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use TypiCMS\Modules\Core\Observers\SlugObserver;
use TypiCMS\Modules\Core\Observers\TipTapHTMLObserver;
use TypiCMS\Modules\ModuleName\Composers\SidebarViewComposer;
use TypiCMS\Modules\ModuleName\Models\ModelName;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/modulename.php', 'typicms.modules.modulename');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/modulename.php');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views/', 'modulename');

        $this->publishes([
            __DIR__ . '/../../database/migrations/create_modulename_table.php.stub' => getMigrationFileName(
                'create_modulename_table',
            ),
        ], 'typicms-migrations');
        $this->publishes([__DIR__ . '/../../resources/views' => resource_path('views/vendor/modulename')], 'typicms-views');
        $this->publishes([__DIR__ . '/../../resources/scss' => resource_path('scss')], 'typicms-resources');

        // Observers
        ModuleName::observe(new SlugObserver());
        ModuleName::observe(new TipTapHTMLObserver());

        View::composer('admin::core._sidebar', SidebarViewComposer::class);

        View::composer('modulename::public.*', function ($view): void {
            $view->page = getPageLinkedToModule('modulename');
        });
    }
}
```

### Step 4: Create Controllers

**AdminController** - Extend `BaseAdminController`:

```php
final class AdminController extends BaseAdminController
{
    public function index(): View
    {
        return view('modulename::admin.index');
    }

    public function create(): View
    {
        return view('modulename::admin.create', ['model' => new ModelName()]);
    }

    public function edit(ModelName $modelname): View
    {
        return view('modulename::admin.edit', ['model' => $modelname]);
    }

    public function store(FormRequest $request): RedirectResponse
    {
        $model = ModelName::query()->create($request->validated());
        return $this->redirect($request, $model)->withMessage(__('Item successfully created.'));
    }

    public function update(ModelName $modelname, FormRequest $request): RedirectResponse
    {
        $modelname->update($request->validated());
        return $this->redirect($request, $modelname)->withMessage(__('Item successfully updated.'));
    }
}
```

**ApiController** - Extend `BaseApiController` for data tables and AJAX operations.

**PublicController** - Extend `BasePublicController` for frontend routes.

### Step 5: Create Form Request

Use array notation with `.*` suffix for translatable fields:

```php
class FormRequest extends AbstractFormRequest
{
    public function rules(): array
    {
        return [
            'image_id' => ['nullable', 'integer'],
            'og_image_id' => ['nullable', 'integer'],
            'title.*' => ['nullable', 'max:255'],
            'slug.*' => ['nullable', 'alpha_dash', 'max:255', 'required_if:status.*,1'],
            'status.*' => ['boolean'],
            'summary.*' => ['nullable', 'max:1000'],
            'body.*' => ['nullable', 'max:30000'],
        ];
    }
}
```

### Step 6: Create Config File

```php
return [
    'linkable_to_page' => true,
    'per_page' => 50,
    'order' => [
        'created_at' => 'desc',
    ],
    'sidebar' => [
        'icon' => '<i class="icon-file-text"></i>',
        'weight' => 30,
    ],
    'permissions' => [
        'read modulename' => 'Read',
        'create modulename' => 'Create',
        'update modulename' => 'Update',
        'delete modulename' => 'Delete',
    ],
];
```

### Step 7: Register the Module

Add the module's service provider to `bootstrap/providers.php` BEFORE `AppServiceProvider::class`:

```php
TypiCMS\Modules\ModuleName\Providers\ModuleServiceProvider::class,
```

## Model Traits Reference

| Trait                  | Purpose                                                  |
|------------------------|----------------------------------------------------------|
| `HasTranslations`      | Multilingual field support (from typicms/translatable)   |
| `HasAdminUrls`         | Admin URL generation (indexUrl, editUrl, etc.)           |
| `HasBodyPresenter`     | Rich text body formatting (formattedBody, dynamicLinks)  |
| `HasContentPresenter`  | Title presentation (presentTitle)                        |
| `HasConfigurableOrder` | Configurable ordering from module config (`order` scope) |
| `HasFiles`             | File attachments (images, documents, audios, videos)     |
| `HasOgImage`           | Open Graph image support (ogImage relationship)          |
| `HasSelectableFields`  | Field selection for queries (`selectFields` scope)       |
| `HasSlugScope`         | Slug query scopes (`whereSlugIs`)                        |
| `Historable`           | Activity/change tracking                                 |
| `Navigable`            | Navigation-related functionality                         |
| `Publishable`          | Published/draft status (`published` scope)               |
| `SortableTrait`        | Drag-and-drop ordering (from spatie/eloquent-sortable)   |

## Helper Functions

| Function                                      | Description                                         |
|-----------------------------------------------|-----------------------------------------------------|
| `locales()`                                   | Get all configured locales                          |
| `mainLocale()`                                | Get the primary locale                              |
| `enabledLocales()`                            | Get only enabled locales                            |
| `homeUrl()`                                   | Get home URL for current locale                     |
| `column($name)`                               | Get JSON column path for locale (e.g., `title->en`) |
| `imageOrDefault(?FileModel, ?width, ?height)` | Render image or default placeholder                 |
| `getPageLinkedToModule($module)`              | Get CMS page linked to a module                     |
| `getPagesLinkedToModule($module)`             | Get all CMS pages linked to a module                |
| `modules()`                                   | Get all registered modules                          |
| `websiteTitle(?locale)`                       | Get website title for locale                        |

## Observers

| Observer                  | Purpose                                                                      | Use When                                               |
|---------------------------|------------------------------------------------------------------------------|--------------------------------------------------------|
| `SlugObserver`            | Auto-generates unique slugs from title for multilingual models               | Model uses `HasTranslations` trait                     |
| `SlugMonolingualObserver` | Auto-generates unique slugs from title for non-translatable models           | Model has simple `slug` and `title` string fields      |
| `TipTapHTMLObserver`      | Patches TipTap HTML output (e.g., removes `<p>` tags inside `<li>` elements) | Model has `$tipTapContent` array with rich text fields |

## Changing a Module's Sidebar Icon

When changing the sidebar icon for a module, four steps are required:

1. **Module config** — Update the icon class in the module's config file (e.g., `Modules/ModuleName/config/modulename.php`):
   ```php
   'sidebar' => [
       'icon' => '<i class="icon-new-icon-name"></i>',
   ],
   ```

2. **package.json** — Add the new icon name to the `icons` script's `--glyphs` list so it gets included in the Lucide font subset.

3. **Lucide SCSS files** — Add the icon with its unicode value to the `$lucide-icons` map in the appropriate file:
   - `resources/scss/admin/_lucide.scss`
   - `resources/scss/public/_lucide.scss`

   Find the unicode value by inspecting `node_modules/lucide-static/font/lucide.css` for the icon name. Add the entry alphabetically:
   ```scss
   $lucide-icons: (
       // ...
       'new-icon-name': '\eXXX',
       // ...
   );
   ```

4. **Build assets** — Run `bun run icons` then `bun run build` to regenerate the font subset and compiled assets.

Icons come from [Lucide](https://lucide.dev/icons/). Use the Lucide icon name in kebab-case prefixed with `icon-` (e.g., Lucide's `handshake` becomes `icon-handshake`).

## Additional Resources

### Reference Files

For detailed patterns and code examples:

- **`references/module-patterns.md`** - Complete module patterns and variations
- **`examples/`** - Working code examples from existing modules
