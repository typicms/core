<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Support;

use Spatie\Permission\Models\Permission;

/**
 * Canonical list of every permission that can be granted.
 *
 * grouped() is for display and depends on the locale, names() is the allow list
 * and does not.
 */
class Permissions
{
    /**
     * Granted to every role through hidden inputs, never rendered.
     *
     * @var array<string, string>
     */
    private const array ALWAYS_GRANTED = [
        'change locale' => 'Change locale',
        'update preferences' => 'Update preferences',
        'clear cache' => 'Clear cache',
    ];

    /**
     * Permissions belonging to no module.
     *
     * @var array<string, string>
     */
    private const array GLOBALS = [
        'see navbar' => 'See navbar',
        'see dashboard' => 'Access dashboard',
        'read settings' => 'See settings',
        'update settings' => 'Change settings',
        'see history' => 'See history',
        'clear history' => 'Empty history',
        'see unpublished items' => 'Preview unpublished items',
        'impersonate users' => 'Impersonate users',
    ];

    /** @return array<string, string> */
    public static function alwaysGranted(): array
    {
        return self::ALWAYS_GRANTED;
    }

    /** @return array<string, string> */
    public static function globals(): array
    {
        return self::GLOBALS;
    }

    /**
     * Every grantable permission name.
     *
     * @return list<string>
     */
    public static function names(): array
    {
        $names = [...array_keys(self::ALWAYS_GRANTED), ...array_keys(self::GLOBALS)];

        foreach (self::modules() as $permissions) {
            $names = [...$names, ...array_keys($permissions)];
        }

        return array_values(array_unique($names));
    }

    public static function has(string $name): bool
    {
        return in_array($name, self::names(), true);
    }

    /**
     * Store the permissions of the list that are missing in the database.
     *
     * It takes no argument on purpose: only the list is stored, so a name coming
     * from a request can never create a permission. Do not give it one.
     */
    public static function sync(): void
    {
        $missing = array_diff(self::names(), array_filter(Permission::query()->pluck('name')->all(), is_string(...)));

        if ($missing === []) {
            return;
        }

        foreach ($missing as $name) {
            Permission::query()->create(['name' => $name]);
        }
    }

    /**
     * Module permissions keyed by translated module name, for display only.
     *
     * @return array<string, array<string, string>>
     */
    public static function grouped(): array
    {
        $grouped = [];

        foreach (self::modules() as $module => $permissions) {
            $label = (string) __(ucfirst($module));
            $grouped[$label] = array_merge($grouped[$label] ?? [], $permissions);
        }

        ksort($grouped, SORT_LOCALE_STRING);

        return $grouped;
    }

    /**
     * Read from config on every call, never memoized: a module registered after
     * this class is first touched must still reach the list.
     *
     * @return array<string, array<string, string>>
     */
    private static function modules(): array
    {
        $config = config('typicms.modules', []);

        if (! is_array($config)) {
            return [];
        }

        $modules = [];

        foreach ($config as $module => $data) {
            if (! is_array($data)) {
                continue;
            }

            if (! isset($data['permissions'])) {
                continue;
            }

            $permissions = self::labelled($data['permissions']);

            if ($permissions === []) {
                continue;
            }

            $modules[(string) $module] = $permissions;
        }

        return $modules;
    }

    /**
     * Drop anything a malformed config could otherwise push into the allow list.
     *
     * @return array<string, string>
     */
    private static function labelled(mixed $permissions): array
    {
        if (! is_array($permissions)) {
            return [];
        }

        $labelled = [];

        foreach ($permissions as $name => $label) {
            if (! is_string($name)) {
                continue;
            }

            if (! is_string($label)) {
                continue;
            }

            $labelled[$name] = $label;
        }

        return $labelled;
    }
}
