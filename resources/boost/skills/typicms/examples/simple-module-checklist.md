# Simple Module Creation Checklist

Use this checklist when creating a new TypiCMS module.

## Files to Create

### 1. Model (`Modules/ModuleName/Models/ModelName.php`)

- [ ] Extend `Illuminate\Database\Eloquent\Model`
- [ ] Add `HasTranslations` trait
- [ ] Add core traits: `HasAdminUrls`, `HasBodyPresenter`, `HasContentPresenter`, `HasConfigurableOrder`, `HasFiles`, `HasOgImage`, `HasSelectableFields`, `HasSlugScope`, `Historable`, `Navigable`, `Publishable`
- [ ] Add `#[ObservedBy([SlugObserver::class, TipTapHTMLObserver::class])]` (drop `TipTapHTMLObserver` if no rich text fields)
- [ ] Add `#[Unguarded]` instead of a `$guarded` property
- [ ] Add `#[Appends(['thumb'])]` instead of an `$appends` property
- [ ] Define `$translatable` array
- [ ] Define `$tipTapContent` array if using rich text
- [ ] Define a `casts()` method (with `#[Override]`) instead of a `$casts` property when casts are needed
- [ ] Add `url()` and `previewUrl()` methods for public URLs
- [ ] Add `thumb()` attribute
- [ ] Add relationships (image, ogImage, etc.)

### 2. Migration (`Modules/ModuleName/database/migrations/create_modulename_table.php.stub`)

- [ ] Use JSON columns for translatable fields
- [ ] Add `new Expression('(JSON_OBJECT())')` default for JSON fields
- [ ] Add foreign keys for images (`files` table)
- [ ] Only create `up()` method (no `down()`)

### 3. Service Provider (`Modules/ModuleName/Providers/ModuleServiceProvider.php`)

- [ ] Merge config
- [ ] Load routes
- [ ] Load views into the shared `admin` namespace from `[resource_path('views/admin'), __DIR__.'/../../resources/views/admin']`
- [ ] Load views into the shared `public` namespace from `[resource_path('views/public'), __DIR__.'/../../resources/views/public']`
- [ ] Publish migrations, admin views, public views, and SCSS
- [ ] Register sidebar view composer on `admin::core._sidebar`
- [ ] Register page view composer on `public::modulename.*`
- [ ] Do **not** call `Model::observe()` — observers are attached via `#[ObservedBy]` on the model

### 4. Config (`Modules/ModuleName/config/modulename.php`)

- [ ] Set `model` to the FQCN of the module's model
- [ ] Set `linkable_to_page`
- [ ] Set `per_page`
- [ ] Define `order` array
- [ ] Configure `sidebar` (icon, weight)
- [ ] Define `permissions`
- [ ] Optionally set `has_feed` (RSS) and/or `llms_txt` (include in `/llms.txt`)

### 5. Controllers

- [ ] `AdminController` - index, create, edit, store, update
- [ ] `ApiController` - index, updatePartial, destroy
- [ ] `PublicController` - index, show (if public-facing)

### 6. Form Request (`Modules/ModuleName/Http/Requests/FormRequest.php`)

- [ ] Extend `AbstractFormRequest`
- [ ] Use `.*` suffix for translatable fields
- [ ] Add validation rules

### 7. Routes (`Modules/ModuleName/routes/modulename.php`)

- [ ] Public routes (if linkable to page)
- [ ] Admin routes with permissions
- [ ] API routes

### 8. Sidebar Composer (`Modules/ModuleName/Composers/SidebarViewComposer.php`)

- [ ] Check gate permission
- [ ] Add to the appropriate sidebar group

### 9. Views (under the module's `resources/views/`)

Resolved as `admin::modulename.*` and `public::modulename.*`:

- [ ] `admin/modulename/index.blade.php`
- [ ] `admin/modulename/create.blade.php`
- [ ] `admin/modulename/edit.blade.php`
- [ ] `admin/modulename/_form.blade.php`
- [ ] `public/modulename/index.blade.php` (if public)
- [ ] `public/modulename/show.blade.php` (if public)

Users can override any of these by publishing the views into `resources/views/admin/modulename/` or `resources/views/public/modulename/` at the project root.

## Post-Creation Steps

1. [ ] Register service provider `TypiCMS\Modules\ModuleName\Providers\ModuleServiceProvider::class` in `bootstrap/providers.php` (before `AppServiceProvider`)
2. [ ] Run migration: `php artisan migrate`
3. [ ] Clear cache: `php artisan cache:clear`
4. [ ] Add permissions to roles in admin panel
5. [ ] Link module to a page (if `linkable_to_page` is true)

## Naming Conventions

| Type                                 | Convention                                  | Example                                          |
|--------------------------------------|---------------------------------------------|--------------------------------------------------|
| Module folder & namespace            | PascalCase plural (`ModuleName`)            | `Modules/Events`, `TypiCMS\Modules\Events`       |
| Model class                          | PascalCase singular (`ModelName`)           | `Event`, `Partner`                               |
| Route-bound variable                 | lowercase singular (`$modelname`)           | `$event`, `$partner`                             |
| Table                                | snake_case plural                           | `events`, `partners`                             |
| Index / export route name            | `<action>-<plural>`                         | `index-events`, `export-partners`                |
| Create / store / edit / update / destroy route name | `<action>-<singular>`        | `create-event`, `update-partner`                 |
| View                                 | `admin::modulename.*`, `public::modulename.*` | `admin::events.index`, `public::partners.show` |
| Config key                           | snake_case plural                           | `typicms.modules.events`                         |
| Permission                           | `<verb> <plural>`                           | `read events`, `create partners`                 |
