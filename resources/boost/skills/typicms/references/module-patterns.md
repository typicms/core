# TypiCMS Module Patterns Reference

This document contains detailed patterns for TypiCMS module development.

## Route Patterns

### Complete Route File Structure

```php
<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use TypiCMS\Modules\Core\Models\Page;
use TypiCMS\Modules\ModuleName\Http\Controllers\AdminController;
use TypiCMS\Modules\ModuleName\Http\Controllers\ApiController;
use TypiCMS\Modules\ModuleName\Http\Controllers\PublicController;

/*
 * Front office routes
 */
if (($page = getPageLinkedToModule('modulename')) instanceof Page) {
    $middleware = $page->private ? ['public', 'auth'] : ['public'];
    foreach (locales() as $lang) {
        if ($page->isPublished($lang) && ($path = $page->path($lang))) {
            Route::middleware($middleware)
                ->prefix($path)
                ->name($lang.'::')
                ->group(function (Router $router): void {
                    $router->get('/', [PublicController::class, 'index'])->name('index-modulename');
                    $router->get('{slug}', [PublicController::class, 'show'])->name('modelname');
                });
        }
    }
}

/*
 * Admin routes — index/store/export use the plural slug, the others use the singular.
 */
Route::middleware('admin')
    ->prefix('admin')
    ->name('admin::')
    ->group(function (Router $router): void {
        $router->get('modulename', [AdminController::class, 'index'])
            ->name('index-modulename')
            ->middleware('can:read modulename');
        $router->get('modulename/create', [AdminController::class, 'create'])
            ->name('create-modelname')
            ->middleware('can:create modulename');
        $router->get('modulename/{modelname}/edit', [AdminController::class, 'edit'])
            ->name('edit-modelname')
            ->middleware('can:read modulename');
        $router->post('modulename', [AdminController::class, 'store'])
            ->name('store-modelname')
            ->middleware('can:create modulename');
        $router->put('modulename/{modelname}', [AdminController::class, 'update'])
            ->name('update-modelname')
            ->middleware('can:update modulename');
    });

/*
 * API routes
 */
Route::middleware(['api', 'auth:api'])->prefix('api')->group(function (Router $router): void {
    $router->get('modulename', [ApiController::class, 'index'])
        ->middleware('can:read modulename');
    $router->patch('modulename/{modelname}', [ApiController::class, 'updatePartial'])
        ->middleware('can:update modulename');
    $router->delete('modulename/{modelname}', [ApiController::class, 'destroy'])
        ->middleware('can:delete modulename');
});
```

## API Controller Patterns

### Index with QueryBuilder

```php
public function index(Request $request): LengthAwarePaginator
{
    $query = ModelName::query()
        ->selectFields();

    return QueryBuilder::for($query)
        ->allowedSorts(['status_translated', 'title_translated', 'created_at'])
        ->allowedFilters([
            AllowedFilter::custom('title', new FilterOr()),
        ])
        ->allowedIncludes(['image'])
        ->paginate($request->integer('per_page'));
}
```

### Partial Update for Translatable Fields

```php
protected function updatePartial(ModelName $model, Request $request): void
{
    foreach ($request->only('status') as $key => $content) {
        if ($model->isTranslatableAttribute($key)) {
            foreach ($content as $lang => $value) {
                $model->setTranslation($key, $lang, $value);
            }
        } else {
            $model->{$key} = $content;
        }
    }

    $model->save();
}
```

## Public Controller Patterns

Public views live under the shared `public::` namespace.

```php
final class PublicController extends BasePublicController
{
    public function index(): View
    {
        $models = ModelName::query()
            ->published()
            ->order()
            ->with('image')
            ->paginate(config('typicms.modules.modulename.per_page'));

        return view('public::modulename.index', ['models' => $models]);
    }

    public function show(string $slug): View
    {
        $model = ModelName::query()
            ->published()
            ->whereSlugIs($slug)
            ->with(['image', 'images', 'documents'])
            ->firstOrFail();

        return view('public::modulename.show', ['model' => $model]);
    }
}
```

## Sidebar View Composer

```php
<?php

declare(strict_types=1);

namespace TypiCMS\Modules\ModuleName\Composers;

use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use TypiCMS\Modules\Sidebar\SidebarGroup;
use TypiCMS\Modules\Sidebar\SidebarItem;

class SidebarViewComposer
{
    public function compose(View $view): void
    {
        if (Gate::denies('read modulename')) {
            return;
        }

        $view->offsetGet('sidebar')->group(__('Content'), function (SidebarGroup $group): void {
            $group->id = 'content';
            $group->weight = 30;
            $group->addItem(__('Module Name'), function (SidebarItem $item): void {
                $item->id = 'modulename';
                $item->icon = config('typicms.modules.modulename.sidebar.icon');
                $item->weight = config('typicms.modules.modulename.sidebar.weight');
                $item->route('admin::index-modulename');
            });
        });
    }
}
```

## Model Relationships

### BelongsTo (Image)

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TypiCMS\Modules\Core\Models\File;

/** @return BelongsTo<File, $this> */
public function image(): BelongsTo
{
    return $this->belongsTo(File::class, 'image_id');
}
```

### BelongsTo (OG Image)

```php
/** @return BelongsTo<File, $this> */
public function ogImage(): BelongsTo
{
    return $this->belongsTo(File::class, 'og_image_id');
}
```

### Thumb Attribute

Declare appended accessors with the `#[Appends]` attribute on the class instead of a `$appends` property:

```php
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[Appends(['thumb'])]
class ModelName extends Model
{
    /** @return Attribute<string, null> */
    protected function thumb(): Attribute
    {
        return Attribute::make(get: fn (): string => imageOrDefault($this->image, null, 54));
    }
}
```

## Sortable Module Pattern

For modules with drag-and-drop ordering:

```php
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class ModelName extends Model implements Sortable
{
    use SortableTrait;

    /** @var array<string, string> */
    public array $sortable = [
        'order_column_name' => 'position',
    ];
}
```

Add to migration:

```php
$table->unsignedInteger('position')->default(0);
```

## Admin View Patterns

### _form.blade.php

```blade
<x-core::header :$model :back-url="$model->indexUrl()" :back-label="__('Module Name')" :default-title="__('New item')" />

<div class="form-body">
    <x-core::form-errors />

    <div class="row">
        <div class="col-lg-8">
            <x-core::title-and-slug-fields :locales="locales()" />
            <x-core::toggle-status-button />

            {!! TranslatableBootForm::textarea(__('Summary'), 'summary')->rows(4) !!}
            <x-core::tiptap-editors :$model name="body" :label="__('Body')" />
        </div>
        <div class="col-lg-4">
            <div class="right-column">
                <file-manager></file-manager>
                <file-field type="image" field="image_id" :init-file="{{ $model->image ?? 'null' }}"></file-field>
                <files-field :init-files="{{ $model->files }}"></files-field>
                {!! TranslatableBootForm::textarea(__('Meta title'), 'meta_title')->rows(2) !!}
                {!! TranslatableBootForm::textarea(__('Meta description'), 'meta_description')->rows(4) !!}
            </div>
        </div>
    </div>
</div>
```

### create.blade.php

```blade
<x-layouts.admin :title="__('New :model', ['model' => __('modelname')])">
    {!! BootForm::open()->action(route('admin::store-modelname'))->multipart() !!}
    @include('admin::modulename._form')
    {!! BootForm::close() !!}
</x-layouts.admin>
```

### edit.blade.php

```blade
<x-layouts.admin :title="$model->title" :$model>
    {!! BootForm::open()->action(route('admin::update-modelname', $model))->method('put')->multipart()->bind($model) !!}
    @include('admin::modulename._form')
    {!! BootForm::close() !!}
</x-layouts.admin>
```

## Export Class Pattern

```php
<?php

declare(strict_types=1);

namespace TypiCMS\Modules\ModuleName\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use TypiCMS\Modules\ModuleName\Models\ModelName;

class Export implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return ModelName::query()->order();
    }

    public function headings(): array
    {
        return ['ID', 'Title', 'Status', 'Created At'];
    }

    public function map($model): array
    {
        return [
            $model->id,
            $model->title,
            $model->isPublished() ? 'Published' : 'Draft',
            $model->created_at->format('Y-m-d H:i'),
        ];
    }
}
```

## Feedable Interface

For RSS feed support:

```php
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

class ModelName extends Model implements Feedable
{
    public function toFeedItem(): FeedItem
    {
        return FeedItem::create()
            ->id($this->id)
            ->title($this->title)
            ->summary($this->summary ?? '')
            ->updated($this->updated_at ?? now())
            ->link($this->url() ?? '')
            ->authorName(config('app.name'));
    }
}
```

Add to config:

```php
'has_feed' => true,
```

## Observer Patterns

Attach observers via the `#[ObservedBy]` attribute on the model — not via `Model::observe()` in the service provider.

### SlugObserver (Multilingual)

Use for models with the `HasTranslations` trait. Auto-generates unique slugs per locale from the title field.

```php
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use TypiCMS\Modules\Core\Observers\SlugObserver;

#[ObservedBy([SlugObserver::class])]
class ModelName extends Model {}
```

**How it works:**
- Generates slug from title using `Str::slug()` for each locale
- Ensures uniqueness by appending `-1`, `-2`, etc. if slug exists
- Checks against `slug->{locale}` JSON path in database

### SlugMonolingualObserver (Non-translatable)

Use for models without translations (simple string `slug` and `title` fields).

```php
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use TypiCMS\Modules\Core\Observers\SlugMonolingualObserver;

#[ObservedBy([SlugMonolingualObserver::class])]
class ModelName extends Model {}
```

**How it works:**
- Generates slug from title only if slug is null
- Ensures uniqueness by appending `-1`, `-2`, etc.
- Checks against simple `slug` column

### TipTapHTMLObserver

Use for models with TipTap rich text fields. Patches the HTML output to fix formatting issues (e.g., removes nested `<p>` tags inside `<li>` elements). Combine with `SlugObserver` in a single attribute when both apply:

```php
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use TypiCMS\Modules\Core\Observers\SlugObserver;
use TypiCMS\Modules\Core\Observers\TipTapHTMLObserver;

#[ObservedBy([SlugObserver::class, TipTapHTMLObserver::class])]
class ModelName extends Model {}
```

**Requirements:**
- Model must have `$tipTapContent` property listing rich text fields:

```php
/** @var array<string> */
public array $tipTapContent = [
    'body',
    'description',
];
```

**How it works:**
- Runs on `saving` event
- Iterates through all fields in `$tipTapContent`
- For each locale, patches the HTML content
- Specifically removes `<p>` tags that TipTap incorrectly nests inside `<li>` elements

## Publishing Vendor Modules

To customize a vendor module:

```bash
php artisan typicms:publish modulename
```

This copies the module to `/Modules/` for customization.

## Creating a New Module (Artisan)

To scaffold a new module:

```bash
php artisan typicms:create modulename
```

This creates the basic module structure that can be customized.
<x-layouts.admin :title="\_\_('New item')">
</x-layouts.admin>
